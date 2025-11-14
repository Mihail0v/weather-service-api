<?php

declare(strict_types=1);
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'weather_records')]
#[ORM\Index(columns: ['city_name'], name: 'index_city_name')]
#[ORM\Index(columns: ['recorded_at'], name: 'index_recorded_at')]
class WeatherRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $cityName;

    #[ORM\Column(type: Types::FLOAT)]
    private float $temperatureCelsius;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $trend = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $recordedAt;

    public function __construct(
        string $cityName,
        float $temperatureCelsius,
        ?\DateTimeImmutable $recordedAt = null,
    ) {
        $this->cityName = $cityName;
        $this->temperatureCelsius = $temperatureCelsius;
        $this->recordedAt = $recordedAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getTemperatureCelsius(): float
    {
        return $this->temperatureCelsius;
    }

    public function getTrend(): ?string
    {
        return $this->trend;
    }

    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function calculateAndSetTrend(float $averageTemperature): void
    {
        $difference = $this->temperatureCelsius - $averageTemperature;

        if ($difference > 1.0) {
            $this->trend = 'hot';
        } elseif ($difference < -1.0) {
            $this->trend = 'cold';
        } else {
            $this->trend = 'static';
        }
    }

    public function getFormattedTemperature(): string
    {
        $temp = number_format($this->temperatureCelsius, 1);

        return match ($this->trend) {
            'hot'   => "{$temp} 🥵",
            'cold'  => "{$temp} 🥶",
            default => "{$temp} -",
        };
    }
}
