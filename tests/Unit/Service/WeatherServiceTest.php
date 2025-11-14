<?php

declare(strict_types=1);
namespace App\Tests\Unit\Service;

use App\Cache\CacheInterface;
use App\DTO\WeatherData;
use App\Repository\WeatherRepositoryInterface;
use App\Service\WeatherApiClient;
use App\Service\WeatherService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WeatherServiceTest extends TestCase
{
    private WeatherService $service;

    private MockObject&WeatherRepositoryInterface $repository;

    private MockObject&CacheInterface $cache;

    private MockObject&WeatherApiClient $apiClient;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(WeatherRepositoryInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->apiClient = $this->createMock(WeatherApiClient::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->service = new WeatherService(
            $this->repository,
            $this->cache,
            $this->apiClient,
            $logger,
        );
    }

    public function testGetWeatherFromCache(): void
    {
        $cachedData = new WeatherData(
            city: 'Sofia',
            temperature: 4.0,
            trend: 'static',
            formattedTemperature: '4.0 -',
            recordedAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $this->cache->method('get')->willReturn($cachedData);

        $result = $this->service->getWeather('Sofia');

        $this->assertEquals('Sofia', $result->city);
        $this->assertEquals(4.0, $result->temperature);
        $this->assertEquals('static', $result->trend);
    }

    public function testGetWeatherFromApiWithCacheMiss(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->apiClient->method('fetchTemperature')->willReturn(4.5);
        $this->repository->method('calculateAverageTemperature')->willReturn(4.0);

        $result = $this->service->getWeather('Sofia');

        $this->assertEquals('Sofia', $result->city);
        $this->assertEquals(4.5, $result->temperature);
        $this->assertEquals('static', $result->trend);
    }

    public function testGetWeatherCalculatesHotTrend(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->apiClient->method('fetchTemperature')->willReturn(20.0);
        $this->repository->method('calculateAverageTemperature')->willReturn(15.0);

        $result = $this->service->getWeather('Madrid');

        $this->assertEquals('hot', $result->trend);
        $this->assertStringContainsString('🥵', $result->formattedTemperature);
    }

    public function testGetWeatherCalculatesColdTrend(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->apiClient->method('fetchTemperature')->willReturn(-5.0);
        $this->repository->method('calculateAverageTemperature')->willReturn(0.0);

        $result = $this->service->getWeather('Moscow');

        $this->assertEquals('cold', $result->trend);
        $this->assertStringContainsString('🥶', $result->formattedTemperature);
    }

    public function testCacheKeyIsCaseInsensitive(): void
    {
        $cachedData = new WeatherData('Sofia', 4.0, 'static', '4.0 -', new \DateTimeImmutable());
        $this->cache->method('get')->willReturn($cachedData);

        $result1 = $this->service->getWeather('Sofia');
        $result2 = $this->service->getWeather('SOFIA');

        $this->assertEquals($result1->city, $result2->city);
    }
}
