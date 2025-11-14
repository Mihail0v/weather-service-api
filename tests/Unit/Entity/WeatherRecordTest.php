<?php

declare(strict_types=1);
namespace App\Tests\Unit\Entity;

use App\Entity\WeatherRecord;
use PHPUnit\Framework\TestCase;

class WeatherRecordTest extends TestCase
{
    public function testWeatherRecordCreation(): void
    {
        $cityName = 'Sofia';
        $temperature = 4.5;
        $recordedAt = new \DateTimeImmutable('2024-01-01 12:00:00');

        $record = new WeatherRecord($cityName, $temperature, $recordedAt);

        $this->assertEquals($cityName, $record->getCityName());
        $this->assertEquals($temperature, $record->getTemperatureCelsius());
        $this->assertEquals($recordedAt, $record->getRecordedAt());
        $this->assertNull($record->getTrend());
    }

    public function testWeatherRecordWithDefaultTimestamp(): void
    {
        $record = new WeatherRecord('Sofia', 4.5);

        $this->assertInstanceOf(\DateTimeImmutable::class, $record->getRecordedAt());
        $this->assertEqualsWithDelta(
            time(),
            $record->getRecordedAt()->getTimestamp(),
            2,
        );
    }

    public function testCalculateAndSetTrendHot(): void
    {
        $record = new WeatherRecord('Madrid', 20.0);
        $averageTemperature = 15.0;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('hot', $record->getTrend());
    }

    public function testCalculateAndSetTrendCold(): void
    {
        $record = new WeatherRecord('Moscow', -5.0);
        $averageTemperature = 0.0;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('cold', $record->getTrend());
    }

    public function testCalculateAndSetTrendStatic(): void
    {
        $record = new WeatherRecord('Sofia', 4.5);
        $averageTemperature = 4.0;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('static', $record->getTrend());
    }

    public function testCalculateAndSetTrendBoundaryPositive(): void
    {
        $record = new WeatherRecord('City', 10.0);
        $averageTemperature = 8.9;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('hot', $record->getTrend());
    }

    public function testCalculateAndSetTrendBoundaryNegative(): void
    {
        $record = new WeatherRecord('City', 10.0);
        $averageTemperature = 11.1;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('cold', $record->getTrend());
    }

    public function testCalculateAndSetTrendExactlyOne(): void
    {
        $record = new WeatherRecord('City', 10.0);
        $averageTemperature = 9.0;

        $record->calculateAndSetTrend($averageTemperature);

        $this->assertEquals('static', $record->getTrend());
    }

    public function testGetFormattedTemperatureHot(): void
    {
        $record = new WeatherRecord('Madrid', 20.0);
        $record->calculateAndSetTrend(15.0);

        $formatted = $record->getFormattedTemperature();

        $this->assertStringContainsString('20.0', $formatted);
        $this->assertStringContainsString('🥵', $formatted);
    }

    public function testGetFormattedTemperatureCold(): void
    {
        $record = new WeatherRecord('Moscow', -5.0);
        $record->calculateAndSetTrend(0.0);

        $formatted = $record->getFormattedTemperature();

        $this->assertStringContainsString('-5.0', $formatted);
        $this->assertStringContainsString('🥶', $formatted);
    }

    public function testGetFormattedTemperatureStatic(): void
    {
        $record = new WeatherRecord('Sofia', 4.5);
        $record->calculateAndSetTrend(4.0);

        $formatted = $record->getFormattedTemperature();

        $this->assertStringContainsString('4.5', $formatted);
        $this->assertStringContainsString('-', $formatted);
        $this->assertStringNotContainsString('🥵', $formatted);
        $this->assertStringNotContainsString('🥶', $formatted);
    }

    public function testGetFormattedTemperatureWithoutTrend(): void
    {
        $record = new WeatherRecord('Sofia', 4.5);

        $formatted = $record->getFormattedTemperature();

        $this->assertStringContainsString('4.5', $formatted);
        $this->assertStringContainsString('-', $formatted);
    }

    public function testTemperatureFormattingPrecision(): void
    {
        $record = new WeatherRecord('Test', 4.567);
        $record->calculateAndSetTrend(4.0);

        $formatted = $record->getFormattedTemperature();

        $this->assertStringContainsString('4.6', $formatted);
    }
}
