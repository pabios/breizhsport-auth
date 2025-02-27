<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;

// Utilisez une interface générique pour le cache
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * redis-cli -h localhost -p 6379
 * KEYS "breizhsport/auth/health_check_*"
 * GET "breizhsport/auth/health_check_fa53b91ccc1b78668d5af58e1ed3a485"
 */

class HealthCheckController extends AbstractController
{
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(Connection $connection, CacheItemPoolInterface $cache): JsonResponse
    {
        // Clé de cache unique (par exemple, basée sur l’URL ou un timestamp)
        $cacheKey = 'health_check_' . md5('auth');  // Ajustez pour product

        // Vérifie si le résultat est en cache
        $cachedResult = $cache->getItem($cacheKey);
        if ($cachedResult->isHit()) {
            return new JsonResponse($cachedResult->get(), 200);
        }

        try {
            $connection->connect();
            $response = [
                'status' => 'OK',
                'service' => 'auth-service',
                'timestamp' => time(),
                'redis_key_cache' => $cacheKey
            ];
            $cachedResult->set($response);
            $cachedResult->expiresAfter(3600); // Cache pendant 1 heure
            $cache->save($cachedResult);

            return new JsonResponse($response, 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => 'Service unavailable: ' . $e->getMessage(),
                'timestamp' => time(),
            ], 503);
        }
    }

    #[Route('/health/redis', name: 'health_redis', methods: ['GET'])]
    public function healthRedis(CacheItemPoolInterface $cache): JsonResponse
    {
        // Clé de cache utilisée par /health
        $cacheKey = 'health_check_' . md5('auth');

        // Récupère l’élément du cache
        $cachedItem = $cache->getItem($cacheKey);

        if ($cachedItem->isHit()) {
            $data = $cachedItem->get();
            // Récupère l’heure et la minute d’enregistrement à partir du timestamp
            $timestamp = $data['timestamp'] ?? time();
            $recordedTime = date('H:i', $timestamp);  // Format "HH:MM" (ex: "14:30")

            // Retourne les données avec des métadatas supplémentaires
            return new JsonResponse([
                'status' => 'OK',
                'service' => $data['service'] ?? 'auth-service',
                'timestamp' => $timestamp,
                'recorded_at' => $recordedTime,  // Heure et minute d’enregistrement
                'hey' => $data['hey'] ?? 'bonjour',
                'redis_key' => $cacheKey,
                'cache_hit' => true,
            ], 200);
        }

        return new JsonResponse([
            'status' => 'ERROR',
            'message' => 'No cached data found for health check',
            'redis_key' => $cacheKey,
            'cache_hit' => false
        ], 404);
    }
}