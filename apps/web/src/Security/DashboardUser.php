<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class DashboardUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const TABLE = 'dashboard_user';

    public function __construct(
        private readonly string $username,
        private readonly string $passwordHash,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_DASHBOARD'];
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function eraseCredentials(): void
    {
    }
}
