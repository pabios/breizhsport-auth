<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController  extends AbstractController
{


    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return new Response(
            '<html><body><h1>Bienvenue sur le microservice Breizhsport {Auth}</h1></body></html>'
        );
    }

    #[Route('/test-token', name: 'test_token')]
    public function testJWT(JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $jwtSecretKey = getenv('JWT_SECRET_KEY') ?: 'Non défini';
        $jwtPublicKey = getenv('JWT_PUBLIC_KEY') ?: 'Non défini';
        $jwtPassphrase = getenv('JWT_PASSPHRASE') ?: 'Non défini';

        $user = new User();
        $user->setEmail('test@mo.com');
        $user->setRoles(['ROLE_USER']);

        // Génère le token JWT
        $token = $jwtManager->createFromPayload($user, ['id' => 100, 'email' => 'test@mo.com']);

        return new JsonResponse([
            'env' => [
                'JWT_SECRET_KEY' => $jwtSecretKey,
                'JWT_PUBLIC_KEY' => $jwtPublicKey,
                'JWT_PASSPHRASE' => $jwtPassphrase,
            ],
            'token' => $token,
        ]);
    }

}