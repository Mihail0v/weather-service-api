<?php

declare(strict_types=1);
namespace App\Cache;

use Predis\ClientInterface;
use Psr\Log\LoggerInterface;

class RedisCache implements CacheInterface
{
    private const string KEY_PREFIX = 'weather:';

    public function __construct(
        private readonly ClientInterface $redis,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function get(string $key): mixed
    {
        try {
            $value = $this->redis->get($this->prefixKey($key));

            if ($value === null) {
                return null;
            }

            return unserialize($value);
        } catch (\Throwable $e) {
            $this->logger->error('Redis failed', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function set(string $key, mixed $value, int $ttl): bool
    {
        try {
            $serialized = serialize($value);
            $result = $this->redis->setex($this->prefixKey($key), $ttl, $serialized);

            return $result !== null;
        } catch (\Throwable $e) {
            $this->logger->error('Redis set failed', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            $this->redis->del([$this->prefixKey($key)]);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Redis delete failed', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isAvailable(): bool
    {
        try {
            $this->redis->ping();

            return true;
        } catch (\Throwable $e) {
            $this->logger->warning('Redis unavailable', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function prefixKey(string $key): string
    {
        return self::KEY_PREFIX . $key;
    }
}
