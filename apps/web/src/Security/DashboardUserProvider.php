<?php

declare(strict_types=1);

namespace App\Security;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<DashboardUser>
 */
final class DashboardUserProvider implements UserProviderInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function ensureTable(): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'CREATE TABLE IF NOT EXISTS %s (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(180) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL
            )',
                DashboardUser::TABLE,
            ),
        );
    }

    public function userExists(string $username): bool
    {
        return (bool) $this->connection->fetchOne(
            \sprintf('SELECT 1 FROM %s WHERE username = ?', DashboardUser::TABLE),
            [$username],
        );
    }

    public function saveUser(string $username, string $passwordHash): bool
    {
        if ($this->userExists($username)) {
            $this->connection->update(
                DashboardUser::TABLE,
                ['password_hash' => $passwordHash],
                ['username' => $username],
            );

            return true;
        }

        $this->connection->insert(DashboardUser::TABLE, [
            'username' => $username,
            'password_hash' => $passwordHash,
        ]);

        return false;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $row = $this->connection->fetchAssociative(
            \sprintf(
                'SELECT username, password_hash FROM %s WHERE username = ?',
                DashboardUser::TABLE,
            ),
            [$identifier],
        );

        if (false === $row) {
            $exception = new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return new DashboardUser(
            (string) $row['username'],
            (string) $row['password_hash'],
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof DashboardUser) {
            throw new UnsupportedUserException(\sprintf('Unsupported user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return DashboardUser::class === $class;
    }
}
