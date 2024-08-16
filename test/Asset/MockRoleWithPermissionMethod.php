<?php

declare(strict_types=1);

namespace LmcRbac\Mvc\DevToolsTest\Asset;

use Lmc\Rbac\Permission\PermissionInterface;
use Lmc\Rbac\Role\RoleInterface;

class MockRoleWithPermissionMethod implements RoleInterface
{
    public function getPermissions(): array
    {
        return ['permission-method-a', 'permission-method-b'];
    }

    public function getName(): string
    {
        return 'role-with-permission-method';
    }

    public function hasPermission($permission): bool
    {
        return false;
    }

    /**
     * Add permission to the role.
     */
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

    public function hasChildren(): bool
    {
        return false;
    }
}
