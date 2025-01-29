<?php

namespace App\Domain\Service;

use App\Entity\User;
use Firebase\JWT\JWT;

// @todo ce n'est plus un factice aujourd'hui a renomer
class FacticeReatlToken
{
    private string $secretKey = 'your-very-secure-secret-key';

    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'fallback-secret-key';
    }

    private int $tokenValidity = 5 * 24 * 60 * 60; // 5 jours en secondes

    public function generate(User $user): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->tokenValidity;

        $payload = [
            'iss' => 'BreizhSport',  // Émetteur du token
            'sub' => $user->getId(), // ID utilisateur
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'iat' => $issuedAt,      // Issued At (date de création)
            'exp' => $expirationTime // Expiration (5 jours)
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256'); // Signature avec clé secrète
    }

}