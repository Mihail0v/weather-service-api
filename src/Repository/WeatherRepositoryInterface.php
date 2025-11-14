<?php

declare(strict_types=1);
namespace App\Repository;

use App\Entity\WeatherRecord;

interface WeatherRepositoryInterface
{
    public function findLatestByCity(string $cityName): ?WeatherRecord;

    public function calculateAverageTemperature(string $cityName, int $days = 10): float;

    public function save(WeatherRecord $record): void;
}
