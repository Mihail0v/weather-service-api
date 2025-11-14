<?php

declare(strict_types=1);
namespace App\Controller;

use App\Service\WeatherService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class WeatherController extends AbstractController
{
    public function __construct(
        private readonly WeatherService $weatherService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/health',
        summary: 'Health check endpoint',
        description: 'Returns the health status of the service',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'healthy'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2025-11-10 15:26:14'),
                    ],
                ),
            ),
        ],
    )]
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return $this->json([
            'status'    => 'healthy',
            'timestamp' => new \DateTimeImmutable()->format('Y-m-d H:i:s'),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/weather/{city}',
        summary: 'Get current weather for a city',
        description: 'Returns current weather data',
        tags: ['Weather'],
        parameters: [
            new OA\Parameter(
                name: 'city',
                description: 'City name (e.g., Sofia, London, Paris)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 100),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Weather data retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/WeatherData'),
            ),
            new OA\Response(
                response: 500,
                description: 'Failed to fetch weather data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Failed to fetch weather data'),
                        new OA\Property(property: 'message', type: 'string', example: 'External API error'),
                    ],
                ),
            ),
        ],
    )]
    #[Route('/weather/{city}', name: 'weather_get', methods: ['GET'])]
    public function getWeather(string $city): JsonResponse
    {
        try {
            $weatherData = $this->weatherService->getWeather($city);

            return $this->json($weatherData->toArray());
        } catch (\Throwable $e) {
            $this->logger->error('Failed to get weather', [
                'city'  => $city,
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error'   => 'Failed to fetch weather data',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
