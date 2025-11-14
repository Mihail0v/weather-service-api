<?php

declare(strict_types=1);
namespace App\Repository;

use App\Entity\WeatherRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctrineWeatherRepository implements WeatherRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $entityManager->getRepository(WeatherRecord::class);
    }

    public function findLatestByCity(string $cityName): ?WeatherRecord
    {
        return $this->repository->findOneBy(
            ['cityName' => $cityName],
            ['recordedAt' => 'DESC'],
        );
    }

    public function calculateAverageTemperature(string $cityName, int $days = 10): float
    {
        $startDate = new \DateTimeImmutable("-{$days} days");

        $result = $this->repository->createQueryBuilder('w')
            ->select('AVG(w.temperatureCelsius) as averageTemp')
            ->where('w.cityName = :cityName')
            ->andWhere('w.recordedAt >= :startDate')
            ->setParameter('cityName', $cityName)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }

    public function save(WeatherRecord $record): void
    {
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }
}
