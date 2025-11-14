<?php

declare(strict_types=1);
namespace App\Service;

use App\Cache\CacheInterface;
use App\DTO\WeatherData;
use App\Entity\WeatherRecord;
use App\Repository\WeatherRepositoryInterface;
use Psr\Log\LoggerInterface;

class WeatherService
{
    private const int CACHE_TTL = 60 * 60;

    public function __construct(
        private readonly WeatherRepositoryInterface $repository,
        private readonly CacheInterface $cache,
        private readonly WeatherApiClient $apiClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getWeather(string $cityName): WeatherData
    {
        $cacheKey = $this->getCacheKey($cityName);
        $cached = $this->cache->get($cacheKey);

        if ($cached instanceof WeatherData) {
            return $cached;
        }

        $temperature = $this->apiClient->fetchTemperature($cityName);
        $weatherRecord = new WeatherRecord($cityName, $temperature);

        $average = $this->repository->calculateAverageTemperature($cityName, 10);
        $weatherRecord->calculateAndSetTrend($average);

        $this->repository->save($weatherRecord);

        $weatherData = new WeatherData(
            city: $weatherRecord->getCityName(),
            temperature: $weatherRecord->getTemperatureCelsius(),
            trend: $weatherRecord->getTrend() ?? 'static',
            formattedTemperature: $weatherRecord->getFormattedTemperature(),
            recordedAt: $weatherRecord->getRecordedAt(),
        );

        $this->cache->set($cacheKey, $weatherData, self::CACHE_TTL);

        $this->logger->info('Weather data fetched and cached', [
            'city'        => $cityName,
            'temperature' => $temperature,
        ]);

        return $weatherData;
    }

    private function getCacheKey(string $cityName): string
    {
        return 'city:' . strtolower($cityName);
    }
}
