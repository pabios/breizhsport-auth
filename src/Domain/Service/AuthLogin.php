<?php

namespace App\Domain\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class AuthLogin extends  AbstractAuthenticator
{
    public function __construct(
        public JWTTokenManagerInterface $jwtManager,
        public EntityManagerInterface $entityManager,
        public UserPasswordHasherInterface $userPasswordHasher,
        public LoggerInterface $logger
    )
    {}

    public function supports(Request $request): ?bool
    {
        return  $request->getPathInfo() === '/api/login';
    }

    public function authenticatel(Request $request): Passport
    { // elle est KISS n'est-ce pas ?
        $content = json_decode($request->getContent(),true);

        $email = $content['email'];
        $password = $content['password'];

        $checkIdentifierFunction = function ($email_): User {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email'=>$email_]);
            if(!$user){
                throw new AuthenticationException('Email ou mot de passe incorrect');
            }

            return $user;
        };

        $checkPasswordFunction = function ($password_): bool {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['password'=>$password_]);

            if(!$user){
                throw new AuthenticationException('Email ou mot de passe incorrect');
            }

            return  $user->getPassword() === $password_;
        };



        return new Passport(
            new UserBadge($email, $checkIdentifierFunction),
            new CustomCredentials($checkPasswordFunction, $password)
        );
    }

    public function authenticate(Request $request): Passport
    {
        $content = json_decode($request->getContent(), true);

        $email = $content['email'];
        $password = $content['password'];

        $checkIdentifierFunction = function ($email_): User {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email_]);
            if (!$user) {
                throw new AuthenticationException('Email ou mot de passe incorrect');
            }

            return $user;
        };

        $checkPasswordFunction = function ($credentials, PasswordAuthenticatedUserInterface $user): bool {
            if (!$this->userPasswordHasher->isPasswordValid($user, $credentials)) {
                throw new AuthenticationException('Email ou mot de passe incorrect');
            }

            return true;
        };

        return new Passport(
            new UserBadge($email, $checkIdentifierFunction),
            new CustomCredentials($checkPasswordFunction,$password)
        );
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token , string $firewallName): ?JsonResponse
    {
        $user = $token->getUser();

        //
        $forFindIdUser = $this->entityManager->getRepository(User::class)->findOneBy(['email'=>$user->getUserIdentifier()]);
        //

        $payload = [
            'data'=>[
                'user'=> $user->getUserIdentifier(),
                'id' => $forFindIdUser->getId()
            ]
        ];

        $generateToken = $this->jwtManager->createFromPayload($user,$payload);



        $this->logger->info('Using passphrase: ' . getenv('JWT_PASSPHRASE'));

        $generateToken = $this->jwtManager->createFromPayload($user, $payload);
        $this->logger->info('Generated Token: ' . $generateToken);




        $data = [
            'success' => true,
            'message' => 'Authentication successFull',
            'user'=>[
                'email' => $user->getUserIdentifier(),
                'id' => $forFindIdUser->getId()
            ],
            'token' => $generateToken
        ];

        return new JsonResponse($data);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $content = json_decode($request->getContent(),true);


        $data = [
            'success' => false,
            'message' => 'Authentication Failure',
            'info'=>[
                'email' => $content['email']
            ]
        ];

        return new JsonResponse($data);
    }
}