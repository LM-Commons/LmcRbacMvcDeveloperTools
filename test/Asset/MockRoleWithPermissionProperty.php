<?php

namespace LmcRbac\Mvc\DevToolsTest\Asset;

use Lmc\Rbac\Role\RoleInterface;

class MockRoleWithPermissionProperty implements RoleInterface
{
    private array $permissions = ['permission-property-a', 'permission-property-b'];

    public function getName(): string
    {
        return 'role-with-permission-property';
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
     */
    public function hasChildren(): bool
    {
        return false;
    }}
