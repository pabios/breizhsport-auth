<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'id' => (string) Uuid::v4(),
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'password', // Le mot de passe brut
            'roles' => ['ROLE_USER'],
            'telephone' => self::faker()->phoneNumber(),
            'fullName' => self::faker()->name(),
            'imgUrl' => self::faker()->imageUrl(),
            'badge' => self::faker()->word(),
            'isActif' => true,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            if ($user->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }
        });
    }

    /**
     * Crée un utilisateur admin avec des informations spécifiques.
     */
    public static function createAdmin(): User
    {
        return self::createOne([
            'email' => 'admin@mo.com',
            'password' => 'Admin123',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }
}