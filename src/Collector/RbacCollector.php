<?php

declare(strict_types=1);

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

namespace Lmc\Rbac\Mvc\DevTools\Collector;

use InvalidArgumentException;
use Laminas\DeveloperTools\Collector\CollectorInterface;
use Laminas\Mvc\MvcEvent;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Role\RecursiveRoleIterator;
use Lmc\Rbac\Mvc\Service\RoleService;
use Laminas\Permissions\Rbac\RoleInterface;
use RecursiveIteratorIterator;
use ReflectionException;
use ReflectionProperty;
use Serializable;
use Traversable;

use function array_values;
use function array_walk;
use function is_array;
use function iterator_to_array;
use function method_exists;
use function serialize;
use function unserialize;

/**
 * RbacCollector
 */
class RbacCollector implements CollectorInterface, Serializable
{
    /**
     * Collector priority
     */
    const PRIORITY                        = -100;
    protected array $collectedGuards      = [];
    protected array $collectedRoles       = [];
    protected array $collectedPermissions = [];
    protected array $collectedOptions     = [];
/**
 * Collector Name.
 */
    public function getName(): string
    {
        return 'lmc_rbac';
    }

    /**
     * Collector Priority.
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @throws ReflectionException
     */
    public function collect(MvcEvent $mvcEvent): void
    {
        if (! $application = $mvcEvent->getApplication()) {
            return;
        }

        $serviceManager = $application->getServiceManager();
/** @var RoleService $roleService */
        $roleService = $serviceManager->get(RoleService::class);
/** @var ModuleOptions $options */
        $options = $serviceManager->get(ModuleOptions::class);
         $this->collectOptions($options);
        $this->collectGuards($options->getGuards());
        $this->collectIdentityRolesAndPermissions($roleService);
    }

    /**
     * Collect options
     */
    private function collectOptions(ModuleOptions $moduleOptions): void
    {
        $this->collectedOptions = [
            'guest_role'        => $moduleOptions->getGuestRole(),
            'protection_policy' => $moduleOptions->getProtectionPolicy(),
        ];
    }

    /**
     * Collect guards
     *
     * @param array $guards
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
     * @throws ReflectionException
     */
    private function collectIdentityRolesAndPermissions(RoleService $roleService): void
    {
        $identityRoles = $roleService->getIdentityRoles();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveRoleIterator($identityRoles),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $role) {
            $roleName = $role->getName();
            $this->collectedRoles[] = $roleName;
            $this->collectPermissions($role);
            /*
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
            */
        }
    }

    /**
     * Collect permissions for the given role
     *
     * @throws ReflectionException
     */
    private function collectPermissions(RoleInterface $role): void
    {
        if (method_exists($role, 'getPermissions')) {
            $permissions = $role->getPermissions();
        } else {
            $reflectionProperty = new ReflectionProperty($role, 'permissions');
            $permissions        = $reflectionProperty->getValue($role);
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
        // Start collect all the data we need!
        return [
            'guards'      => $this->collectedGuards,
            'roles'       => $this->collectedRoles,
            'permissions' => $this->collectedPermissions,
            'options'     => $this->collectedOptions,
        ];
    }

 // Gather the permissions for the given role. We have to use reflection as
 // the RoleInterface does not have "getPermissions" method

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
        if (! is_array($collection)) {
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
        $this->collectedGuards      = $data['guards'];
        $this->collectedRoles       = $data['roles'];
        $this->collectedPermissions = $data['permissions'];
        $this->collectedOptions     = $data['options'];
    }
}
