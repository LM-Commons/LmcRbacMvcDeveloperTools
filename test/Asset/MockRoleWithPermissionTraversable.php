<?php


namespace LmcRbac\Mvc\DevToolsTest\Asset;

use Lmc\Rbac\Role\RoleInterface;


class MockRoleWithPermissionTraversable implements RoleInterface
{
    public function getPermissions(): \ArrayObject
    {
        return new \ArrayObject(['permission-method-a', 'permission-method-b']);
    }

    public function getName(): string
    {
        return 'role-with-permission-traversable';
    }
    public function hasPermission($permission): bool
    {
        return false;
    }

    public function addPermission(string|\Lmc\Rbac\Permission\PermissionInterface $permission): void{
        return;
    }

    /**
     * Add a child.
     */
    public function addChild(RoleInterface $role): void{
        return;
    }

    /**
     * Get the children roles.
     *
     * @return RoleInterface[]
     */
    public function getChildren(): iterable{
        return [];
    }

    /**
     * Get the children roles.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return false;
    }
}
