<?php

namespace App\Mapper\User;

use App\ApiResource\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: UserDto::class,to: User::class)]
class UserDtoToEntityMapper implements MapperInterface
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserDto);

        $userEntity = $dto->id ? $this->userRepository->find($dto->id) : new User();
        if (!$userEntity) {
            throw new Exception('User not found');
        }

        return $userEntity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserDto);
        $entity = $to;
        assert($entity instanceof User);


        $this->setIfNotNull($entity, 'setEmail', $dto->email);

        $this->setIfNotNull($entity, 'setTelephone', $dto->telephone);
        $this->setIfNotNull($entity, 'setImgUrl', $dto->imgUrl);
        $this->setIfNotNull($entity, 'setBadge', $dto->badge);
        $this->setIfNotNull($entity, 'setFullName', $dto->fullName);


        if ($dto->password) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $dto->password));
        }

        return $entity;
    }

    private function setIfNotNull(object $entity, string $setter, mixed $value): void
    {
        if ($value !== null) {
            $entity->$setter($value);
        }
    }

    public function map(UserDto $userDto, string $entityClass): User
    {
        $user = new $entityClass();
        $user->setEmail($userDto->email);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $userDto->password)
        );
        return $user;
    }
}
