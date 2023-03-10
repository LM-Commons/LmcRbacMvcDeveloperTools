<?php

namespace LmcRbacMvcDevToolsTest\Asset;

use Rbac\Role\RoleInterface;

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
}
