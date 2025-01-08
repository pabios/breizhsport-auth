<?php

namespace App\Mapper\User;

use App\ApiResource\UserDto;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: User::class,to: UserDto::class)]
class UserEntitytoDtoMapper implements MapperInterface
{

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof User);

        $dto = new UserDto();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto = $to;
        assert($entity instanceof  User);
        assert($dto instanceof UserDto);

        $dto->email = $entity->getEmail();
        $dto->password = $entity->getPassword();

        $dto->telephone = $entity->getTelephone();
        $dto->roles = $entity->getRoles();
        $dto->fullName = $entity->getFullName();
        $dto->badge = $entity->getBadge();


        return $dto;
    }
}