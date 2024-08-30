<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\DevToolsTest\Asset;

use Laminas\Permissions\Rbac\RoleInterface;

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

    public function hasPermission($name): bool
    {
        return false;
    }

    /**
     * Add permission to the role.
     */
    public function addPermission(string $name): void
    {
    }

    /**
     * Add a child.
     */
    public function addChild(RoleInterface $child): void
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

    public function addParent(RoleInterface $parent): void
    {
        // TODO: Implement addParent() method.
    }

    public function getParents(): iterable
    {
        return [];
    }
}
