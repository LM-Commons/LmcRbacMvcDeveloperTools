<?php

declare(strict_types=1);

namespace LmcRbac\Mvc\DevToolsTest\Asset;

use ArrayObject;
use Lmc\Rbac\Permission\PermissionInterface;
use Lmc\Rbac\Role\RoleInterface;

class MockRoleWithPermissionTraversable implements RoleInterface
{
    public function getPermissions(): ArrayObject
    {
        return new ArrayObject(['permission-method-a', 'permission-method-b']);
    }

    public function getName(): string
    {
        return 'role-with-permission-traversable';
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
