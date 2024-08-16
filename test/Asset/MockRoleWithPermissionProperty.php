<?php

declare(strict_types=1);

namespace LmcRbac\Mvc\DevToolsTest\Asset;

use Lmc\Rbac\Permission\PermissionInterface;
use Lmc\Rbac\Role\RoleInterface;

class MockRoleWithPermissionProperty implements RoleInterface
{
    private array $permissions = ['permission-property-a', 'permission-property-b'];

    public function getName(): string
    {
        return 'role-with-permission-property';
    }

    public function hasPermission(string|PermissionInterface $permission): bool
    {
        return false;
    }

    public function addPermission(string|PermissionInterface $permission): void
    {
    }

    /**
     * Add a child.
     */
    public function addChild(RoleInterface $role): void
    {
    }

    /**
     * Get the children roles.
     *
     * @return RoleInterface[]
     */
    public function getChildren(): iterable
    {
        return [];
    }

    /**
     * Get the children roles.
     */
    public function hasChildren(): bool
    {
        return false;
    }
}
