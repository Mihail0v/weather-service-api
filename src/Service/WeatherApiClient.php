<?php

declare(strict_types=1);
namespace App\Service;

use Psr\Log\LoggerInterface;

class WeatherApiClient
{
    private const array MOCK_TEMPERATURES = [
        'sofia'   => 4.1,
        'london'  => 8.5,
        'paris'   => 12.3,
        'berlin'  => 6.7,
        'madrid'  => 15.2,
        'rome'    => 14.8,
        'default' => 10.0,
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetchTemperature(string $cityName): float
    {
        $this->logger->info('Fetching temperature from API', [
            'city' => $cityName,
        ]);

        $city = strtolower($cityName);
        $baseTemp = self::MOCK_TEMPERATURES[$city] ?? self::MOCK_TEMPERATURES['default'];

        return round($baseTemp + (rand(-20, 20) / 10), 1);
    }
}
