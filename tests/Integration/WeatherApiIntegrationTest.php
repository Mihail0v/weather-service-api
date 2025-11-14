<?php

declare(strict_types=1);
namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WeatherApiIntegrationTest extends WebTestCase
{
    public function testHealthCheckEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testGetWeatherForCity(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/weather/Sofia');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Sofia', $data['city']);
        $this->assertIsNumeric($data['temperature']); // JSON can serialize floats as ints if no decimal
        $this->assertContains($data['trend'], ['hot', 'cold', 'static']);
        $this->assertArrayHasKey('formatted_temperature', $data);
        $this->assertArrayHasKey('recorded_at', $data);
    }

    public function testCachingWorks(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/weather/TestCity');
        $this->assertResponseIsSuccessful();
        $firstResponse = json_decode($client->getResponse()->getContent(), true);
        $firstTimestamp = $firstResponse['recorded_at'];

        $client->request('GET', '/api/v1/weather/TestCity');
        $this->assertResponseIsSuccessful();
        $secondResponse = json_decode($client->getResponse()->getContent(), true);
        $secondTimestamp = $secondResponse['recorded_at'];

        $this->assertEquals($firstTimestamp, $secondTimestamp);
        $this->assertEquals($firstResponse['temperature'], $secondResponse['temperature']);
    }

    public function testCaseInsensitiveCityNames(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/weather/sofia');
        $this->assertResponseIsSuccessful();
        $lowerCase = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/api/v1/weather/SOFIA');
        $this->assertResponseIsSuccessful();
        $upperCase = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($lowerCase['recorded_at'], $upperCase['recorded_at']);
    }
}
