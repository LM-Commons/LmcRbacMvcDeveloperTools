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

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\DevToolsTest\Collector;

use Laminas\Mvc\Application;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use Lmc\Rbac\Identity\IdentityInterface;
use Lmc\Rbac\Mvc\Guard\ControllerGuard;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Guard\RouteGuard;
use Lmc\Rbac\Mvc\Identity\IdentityProviderInterface;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Role\RecursiveRoleIteratorStrategy;
use Lmc\Rbac\Mvc\Service\RoleService;
use Lmc\Rbac\Role\InMemoryRoleProvider;
use Laminas\Permissions\Rbac\RoleInterface;
use Lmc\Rbac\Role\RoleProviderInterface;
use Lmc\Rbac\Service\RoleService as BaseRoleService;
use Lmc\Rbac\Mvc\DevTools\Collector\RbacCollector;
use LmcTest\Rbac\Mvc\DevToolsTest\Asset\MockRoleWithPermissionMethod;
use LmcTest\Rbac\Mvc\DevToolsTest\Asset\MockRoleWithPermissionProperty;
use LmcTest\Rbac\Mvc\DevToolsTest\Asset\MockRoleWithPermissionTraversable;
use PHPUnit\Framework\TestCase;

use function serialize;
use function unserialize;

/**
 * @covers \Lmc\Rbac\Mvc\DevTools\Collector\RbacCollector
 */
class RbacCollectorTest extends TestCase
{
    public function testDefaultGetterReturnValues(): void
    {
        $collector = new RbacCollector();
        $this->assertSame(-100, $collector->getPriority());
        $this->assertSame('lmc_rbac', $collector->getName());
    }

    public function testSerialize(): void
    {
        $collector  = new RbacCollector();
        $serialized = $collector->serialize();
        $this->assertIsString($serialized);
        $unserialized = unserialize($serialized);
        $this->assertSame([], $unserialized['guards']);
        $this->assertSame([], $unserialized['roles']);
        $this->assertSame([], $unserialized['options']);
    }

    public function testUnserialize(): void
    {
        $collector    = new RbacCollector();
        $unserialized = [
            'guards'      => [
                'foo' => 'bar',
            ],
            'roles'       => [
                'foo' => 'bar',
            ],
            'permissions' => [
                'foo' => 'bar',
            ],
            'options'     => [
                'foo' => 'bar',
            ],
        ];
        $serialized   = serialize($unserialized);
        $collector->unserialize($serialized);
        $collection = $collector->getCollection();
        $this->assertIsArray($collection);
        $this->assertSame(['foo' => 'bar'], $collection['guards']);
        $this->assertSame(['foo' => 'bar'], $collection['roles']);
        $this->assertSame(['foo' => 'bar'], $collection['options']);
        $this->assertSame(['foo' => 'bar'], $collection['permissions']);
    }

    public function testUnserializeThrowsInvalidArgumentException(): void
    {
        $this->expectException('InvalidArgumentException');
        $collector    = new RbacCollector();
        $unserialized = 'not_an_array';
        $serialized   = serialize($unserialized);
        $collector->unserialize($serialized);
    }

    public function testCollectNothingIfNoApplicationIsSet(): void
    {
        $mvcEvent  = new MvcEvent();
        $collector = new RbacCollector();
        $collector->collect($mvcEvent);
        $expectedCollection = [
            'guards' => [],
            'roles' => [],
            'permissions' => [],
            'options' => [],
        ];
        $test = $collector->getCollection();
        $this->assertEquals($expectedCollection, $collector->getCollection());
    }

    public function testCanCollect(): void
    {
        $dataToCollect = [
            'module_options' => [
                'guest_role'        => 'guest',
                'protection_policy' => GuardInterface::POLICY_ALLOW,
                'guards'            => [
                    RouteGuard::class      => [
                        'admin*' => ['*'],
                    ],
                    ControllerGuard::class => [
                        [
                            'controller' => 'Foo',
                            'roles'      => ['*'],
                        ],
                    ],
                ],
            ],
            'role_config'    => [
                'member' => [
                    'children'    => ['guest'],
                    'permissions' => ['write', 'delete'],
                ],
                'guest'  => [
                    'permissions' => ['read'],
                ],
            ],
            'identity_role'  => ['member'],
        ];

        $serviceManager = new ServiceManager();
        $application    = $this->createMock('Laminas\Mvc\ApplicationInterface');
        $application->expects($this->once())->method('getServiceManager')->willReturn($serviceManager);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setApplication($application);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->once())->method('getRoles')->willReturn($dataToCollect['identity_role']);

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->once())->method('getIdentity')->willReturn($identity);

        $baseRoleService = new BaseRoleService(new InMemoryRoleProvider($dataToCollect['role_config']), 'guest');
        $roleService     = new RoleService($identityProvider, $baseRoleService, new RecursiveRoleIteratorStrategy());

        $serviceManager->setService(RoleService::class, $roleService);
        $serviceManager->setService(ModuleOptions::class, new ModuleOptions($dataToCollect['module_options']));
        $collector = new RbacCollector();
        $collector->collect($mvcEvent);

        $collector->unserialize($collector->serialize());
        $collection = $collector->getCollection();

        $expectedCollection = [
            'guards'      => [
                RouteGuard::class      => [
                    'admin*' => ['*'],
                ],
                ControllerGuard::class => [
                    [
                        'controller' => 'Foo',
                        'roles'      => ['*'],
                    ],
                ],
            ],
            'roles'       => [
                'member', 'guest',
//                'member' => ['guest'],
            ],
            'permissions' => [
                'member' => ['write', 'delete', 'read'],
                'guest'  => ['read'],
            ],
            'options'     => [
                'guest_role'        => 'guest',
                'protection_policy' => 'allow',
            ],
        ];

        $this->assertEquals($expectedCollection, $collection);
    }

    /**
     * Tests the collectPermissions method when the role has a $permissions Property
     */
    public function testCollectPermissionsProperty(): void
    {
        $expectedCollection = [
            'guards'      => [],
            'roles'       => ['role-with-permission-property'],
            'permissions' => [
                'role-with-permission-property' => ['permission-property-a', 'permission-property-b'],
            ],
            'options'     => [
                'guest_role'        => 'guest',
                'protection_policy' => GuardInterface::POLICY_ALLOW,
            ],
        ];

        $collection = $this->collectPermissionsPropertyTestBase(new MockRoleWithPermissionProperty());
        $this->assertEquals($expectedCollection, $collection);
    }

    /**
     * Tests the collectPermissions method when the role has a getPermissions() method
     */
    public function testCollectPermissionsMethod(): void
    {
        $expectedCollection = [
            'guards'      => [],
            'roles'       => ['role-with-permission-method'],
            'permissions' => [
                'role-with-permission-method' => ['permission-method-a', 'permission-method-b'],
            ],
            'options'     => [
                'guest_role'        => 'guest',
                'protection_policy' => GuardInterface::POLICY_ALLOW,
            ],
        ];

        $collection = $this->collectPermissionsPropertyTestBase(new MockRoleWithPermissionMethod());
        $this->assertEquals($expectedCollection, $collection);
    }

    /**
     * Tests the collectPermissions method when the role implements Traversable
     */
    public function testCollectPermissionsTraversable(): void
    {
        $expectedCollection = [
            'guards'      => [],
            'roles'       => ['role-with-permission-traversable'],
            'permissions' => [
                'role-with-permission-traversable' => ['permission-method-a', 'permission-method-b'],
            ],
            'options'     => [
                'guest_role'        => 'guest',
                'protection_policy' => GuardInterface::POLICY_ALLOW,
            ],
        ];

        $collection = $this->collectPermissionsPropertyTestBase(new MockRoleWithPermissionTraversable());
        $this->assertEquals($expectedCollection, $collection);
    }

    /**
     * Base method for the *collectPermissionProperty tests
     *
     * @return array|string[]
     */
    private function collectPermissionsPropertyTestBase(RoleInterface $role): array
    {
        $serviceManager = new ServiceManager();

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $application->expects($this->once())->method('getServiceManager')->willReturn($serviceManager);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setApplication($application);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->once())
            ->method('getRoles')
            ->willReturn([$role]);

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);

        $roleProvider = $this->createMock(RoleProviderInterface::class);

        $baseRoleService = new BaseRoleService($roleProvider, '');

        $roleService = new RoleService(
            $identityProvider,
            $baseRoleService,
            new RecursiveRoleIteratorStrategy()
        );
        $serviceManager->setService(RoleService::class, $roleService);
        $serviceManager->setService(ModuleOptions::class, new ModuleOptions());
        $collector = new RbacCollector();
        $collector->collect($mvcEvent);

        $collector->unserialize($collector->serialize());
        return $collector->getCollection();
    }
}
