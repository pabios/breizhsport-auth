<?php

namespace App\Controller;

use App\ApiResource\UserDto;
use App\Domain\Service\UserInputSanitizer;
use App\Entity\User;
use App\Mapper\User\UserDtoToEntityMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;


class SignUpController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserDtoToEntityMapper $userDtoToEntityMapper,
        private UserInputSanitizer $sanitizer
    ) {}

    #[Route('/sign_up', name: 'sign_up', methods: ['POST'])]
    public function signUp(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new JsonException('Invalid JSON format');
            }

            // Valider les données d'entrée
            $validationError = $this->sanitizer->validateUserInput($data);
            if ($validationError) {
                return $validationError;
            }

            // Nettoyage des entrées pour éviter les injections
            $email = $this->sanitizer->sanitizeString($data['email']);
            $password = $this->sanitizer->sanitizeString($data['password']);

            // Mapper le DTO vers l'entité User
            $userDto = new UserDto();
            $userDto->email = $email;
            $userDto->password = $password;

            $user = $this->userDtoToEntityMapper->map($userDto, User::class);

            // Persister l'utilisateur dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'User successfully created'], 201);

        } catch (JsonException $e) {
            return new JsonResponse(['message' => 'Invalid JSON format: ' . $e->getMessage()], 400);

        } catch (ValidationFailedException $e) {
            return new JsonResponse(['message' => 'Validation error: ' . $e->getMessage()], 400);

        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}

//class SignUpController extends AbstractController
//{
//    public function __construct(
//        private EntityManagerInterface $entityManager, private UserDtoToEntityMapper $userDtoToEntityMapper)
//    {
//    }
//    #[Route('/sign_up', name: 'sign_up', methods: ['POST'])]
//    public function signUp(Request $request): JsonResponse
//    {
//        try {
//            $data = json_decode($request->getContent(), true);
//            if (json_last_error() !== JSON_ERROR_NONE) {
//                throw new JsonException('Invalid JSON format');
//            }
//
//            if (!isset($data['email']) || !isset($data['password'])) {
//                return new JsonResponse(['message' => 'Email and password are required'], 400);
//            }
//
//            // Créer le DTO à partir des données de la requête
//            $userDto = new UserDto();
//            $userDto->email = $data['email'];
//            $userDto->password = $data['password'];
//            // Ajouter d'autres champs si nécessaires (nom, prénom, etc.)
//
//            // Mapper le DTO vers l'entité User
//            $user = $this->userDtoToEntityMapper->map($userDto, User::class);
//
//            // Persister l'utilisateur dans la base de données
//            $this->entityManager->persist($user);
//            $this->entityManager->flush();
//
//            // Retourner une réponse JSON en cas de succès
//            return new JsonResponse(['message' => 'User successfully created'], 201);
//
//        } catch (JsonException $e) {
//            return new JsonResponse(['message' => 'Invalid JSON format: ' . $e->getMessage()], 400);
//
//        } catch (ORMException $e) {
//            return new JsonResponse(['message' => 'Database error: ' . $e->getMessage()], 500);
//
//        } catch (\Exception $e) {
//            return new JsonResponse(['message' => 'An error occurred: ' . $e->getMessage()], 500);
//        }
//    }
//}