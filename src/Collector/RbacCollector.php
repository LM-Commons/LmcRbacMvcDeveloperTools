<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace LmcRbac\Mvc\DevTools\Collector;

use Laminas\Permissions\Rbac\RoleInterface;
use LmcRbacMvc\Role\RecursiveRoleIterator;
use RecursiveIteratorIterator;
use ReflectionProperty;
use ReflectionException;
use Serializable;
use Traversable;
use Laminas\Mvc\MvcEvent;
use Laminas\DeveloperTools\Collector\CollectorInterface;
use LmcRbacMvc\Options\ModuleOptions;
use LmcRbacMvc\Service\RoleService;
use InvalidArgumentException;

/**
 * RbacCollector
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @license MIT
 */
class RbacCollector implements CollectorInterface, Serializable
{
    /**
     * Collector priority
     */
    const PRIORITY = -100;

    protected array $collectedGuards = [];

    protected array $collectedRoles = [];

    protected array $collectedPermissions = [];

    protected array $collectedOptions = [];

    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'lmc_rbac';
    }

    /**
     * Collector Priority.
     *
     * @return integer
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent
     * @throws ReflectionException
     */
    public function collect(MvcEvent $mvcEvent): void
    {
        if (!$application = $mvcEvent->getApplication()) {
            return;
        }

        $serviceManager = $application->getServiceManager();

        /* @var RoleService $roleService */
        $roleService = $serviceManager->get('LmcRbacMvc\Service\RoleService');

        /* @var ModuleOptions $options */
        $options = $serviceManager->get('LmcRbacMvc\Options\ModuleOptions');

        // Start collect all the data we need!
        $this->collectOptions($options);
        $this->collectGuards($options->getGuards());
        $this->collectIdentityRolesAndPermissions($roleService);
    }

    /**
     * Collect options
     *
     * @param  ModuleOptions $moduleOptions
     * @return void
     */
    private function collectOptions(ModuleOptions $moduleOptions): void
    {
        $this->collectedOptions = [
            'guest_role'        => $moduleOptions->getGuestRole(),
            'protection_policy' => $moduleOptions->getProtectionPolicy()
        ];
    }

    /**
     * Collect guards
     *
     * @param array $guards
     * @return void
     */
    private function collectGuards(array $guards): void
    {
        $this->collectedGuards = [];

        foreach ($guards as $type => $rules) {
            $this->collectedGuards[$type] = $rules;
        }
    }

    /**
     * Collect roles and permissions
     *
     * @param RoleService $roleService
     * @return void
     * @throws ReflectionException
     */
    private function collectIdentityRolesAndPermissions(RoleService $roleService): void
    {
        $identityRoles = $roleService->getIdentityRoles();

        foreach ($identityRoles as $role) {
            $roleName = $role->getName();

            if (empty($role->getChildren())) {
                $this->collectedRoles[] = $roleName;
            } else {
                $iteratorIterator = new RecursiveIteratorIterator(
                    new RecursiveRoleIterator($role->getChildren()),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iteratorIterator as $childRole) {
                    $this->collectedRoles[$roleName][] = $childRole->getName();
                    $this->collectPermissions($childRole);
                }
            }

            $this->collectPermissions($role);
        }
    }

    /**
     * Collect permissions for the given role
     *
     * @param RoleInterface $role
     * @return void
     * @throws ReflectionException
     */
    private function collectPermissions(RoleInterface $role): void
    {
        if (method_exists($role, 'getPermissions')) {
            $permissions = $role->getPermissions();
        } else {
            // Gather the permissions for the given role. We have to use reflection as
            // the RoleInterface does not have "getPermissions" method
            $reflectionProperty = new ReflectionProperty($role, 'permissions');
            $reflectionProperty->setAccessible(true);

            $permissions = $reflectionProperty->getValue($role);
        }

        if ($permissions instanceof Traversable) {
            $permissions = iterator_to_array($permissions);
        }

        array_walk($permissions, function (&$permission) {
            $permission = (string) $permission;
        });

        $this->collectedPermissions[$role->getName()] = array_values($permissions);
    }

    /**
     * @return array|string[]
     */
    public function getCollection(): array
    {
        return [
            'guards'      => $this->collectedGuards,
            'roles'       => $this->collectedRoles,
            'permissions' => $this->collectedPermissions,
            'options'     => $this->collectedOptions
        ];
    }
    /**
     * {@inheritDoc}
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data): void
    {
        $collection = unserialize($data);
        if (!is_array($collection)) {
            throw new InvalidArgumentException(__METHOD__ . ": Unserialized data should be an array.");
        }

        $this->__unserialize($collection);
    }

    public function __serialize(): array
    {
        return $this->getCollection();
    }

    public function __unserialize(array $data): void
    {
        $this->collectedGuards = $data['guards'];
        $this->collectedRoles  = $data['roles'];
        $this->collectedPermissions =  $data['permissions'];
        $this->collectedOptions = $data['options'];
    }
}
