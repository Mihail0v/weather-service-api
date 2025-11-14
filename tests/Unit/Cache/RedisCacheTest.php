<?php

declare(strict_types=1);
namespace App\Tests\Unit\Cache;

use App\Cache\RedisCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;

interface TestRedisClient extends ClientInterface
{
    public function get(string $key): mixed;

    public function setex(string $key, int $ttl, mixed $value): mixed;

    public function del(array $keys): int;

    public function ping(mixed $message = null): mixed;
}

class RedisCacheTest extends TestCase
{
    private MockObject&TestRedisClient $redis;

    private RedisCache $cache;

    protected function setUp(): void
    {
        $this->redis = $this->createMock(TestRedisClient::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->cache = new RedisCache($this->redis, $logger);
    }

    public function testGetReturnsNullWhenKeyNotFound(): void
    {
        $this->redis->method('get')->willReturn(null);

        $this->assertNull($this->cache->get('test-key'));
    }

    public function testGetReturnsUnserializedValue(): void
    {
        $data = ['city' => 'Sofia', 'temperature' => 4.0];
        $this->redis->method('get')->willReturn(serialize($data));

        $this->assertEquals($data, $this->cache->get('test-key'));
    }

    public function testGetReturnsNullOnException(): void
    {
        $this->redis->method('get')->willThrowException(new \Exception('Connection failed'));

        $this->assertNull($this->cache->get('test-key'));
    }

    public function testSetReturnsTrue(): void
    {
        $this->redis->method('setex')->willReturn('OK');

        $this->assertTrue($this->cache->set('test-key', ['data' => 'value'], 3600));
    }

    public function testSetReturnsFalseOnException(): void
    {
        $this->redis->method('setex')->willThrowException(new \Exception('Write failed'));

        $this->assertFalse($this->cache->set('test-key', 'value', 3600));
    }

    public function testDeleteReturnsTrue(): void
    {
        $this->redis->method('del')->willReturn(1);

        $this->assertTrue($this->cache->delete('test-key'));
    }

    public function testDeleteReturnsFalseOnException(): void
    {
        $this->redis->method('del')->willThrowException(new \Exception('Delete failed'));

        $this->assertFalse($this->cache->delete('test-key'));
    }

    public function testIsAvailableReturnsTrueWhenConnected(): void
    {
        $this->redis->method('ping')->willReturn('PONG');

        $this->assertTrue($this->cache->isAvailable());
    }

    public function testIsAvailableReturnsFalseOnException(): void
    {
        $this->redis->method('ping')->willThrowException(new \Exception('Connection error'));

        $this->assertFalse($this->cache->isAvailable());
    }
}
