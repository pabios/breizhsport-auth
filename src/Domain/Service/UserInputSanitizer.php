<?php

namespace App\Domain\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserInputSanitizer
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateUserInput(array $data): ?JsonResponse
    {
        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Length(['max' => 180]),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 4, 'max' => 64]),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/',
                    'message' => 'The password must contain at least one uppercase letter, one lowercase letter, and one digit.',
                ]),
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['message' => 'Invalid input', 'errors' => $errors], 400);
        }

        return null; // Validation OK
    }

    public function sanitizeString(string $input): string
    {
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}