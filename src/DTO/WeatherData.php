<?php

declare(strict_types=1);
namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WeatherData',
    description: 'Weather data',
    type: 'object',
)]
readonly class WeatherData
{
    public function __construct(
        #[OA\Property(property: 'city', description: 'City name', type: 'string', example: 'Sofia')]
        public string $city,
        #[OA\Property(property: 'temperature', description: 'Temperature value', type: 'number', format: 'float', example: 4.5)]
        public float $temperature,
        #[OA\Property(property: 'trend', description: 'Temperature trend', type: 'string', enum: ['hot', 'cold', 'static'], example: 'static')]
        public string $trend,
        #[OA\Property(property: 'formatted_temperature', description: 'Temperature formatted', type: 'string', example: '4.5 -')]
        public string $formattedTemperature,
        #[OA\Property(property: 'recorded_at', description: 'timestamp', type: 'string', format: 'date-time', example: '2025-11-10 15:26:14')]
        public \DateTimeImmutable $recordedAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'city'                  => $this->city,
            'temperature'           => $this->temperature,
            'trend'                 => $this->trend,
            'formatted_temperature' => $this->formattedTemperature,
            'recorded_at'           => $this->recordedAt->format('Y-m-d H:i:s'),
        ];
    }
}
