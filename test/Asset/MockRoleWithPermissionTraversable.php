<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\DevToolsTest\Asset;

use ArrayObject;
use Laminas\Permissions\Rbac\RoleInterface;

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

    public function hasPermission(string $name): bool
    {
        return false;
    }

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
