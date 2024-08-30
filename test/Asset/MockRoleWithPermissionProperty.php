<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\DevToolsTest\Asset;

use Laminas\Permissions\Rbac\RoleInterface;

class MockRoleWithPermissionProperty implements RoleInterface
{
    private array $permissions = ['permission-property-a', 'permission-property-b'];

    public function getName(): string
    {
        return 'role-with-permission-property';
    }

    public function hasPermission(string $permission): bool
    {
        return false;
    }

    public function addPermission(string $permission): void
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

    /**
     * Get the children roles.
     */
    public function hasChildren(): bool
    {
        return false;
    }

    public function addParent(RoleInterface $parent): void
    {

    }

    public function getParents(): iterable
    {
        return [];
    }
}
