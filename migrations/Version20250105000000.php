<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250105000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create weather_records table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE weather_records (
                id SERIAL PRIMARY KEY,
                city_name VARCHAR(100) NOT NULL,
                temperature_celsius DOUBLE PRECISION NOT NULL,
                recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ');

        $this->addSql('
            CREATE INDEX index_city_date ON weather_records (city_name, recorded_at)
        ');

        $this->addSql('
            COMMENT ON COLUMN weather_records.recorded_at IS \'(DC2Type:datetime_immutable)\'
        ');

        $this->addSql('
            COMMENT ON COLUMN weather_records.created_at IS \'(DC2Type:datetime_immutable)\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE weather_records');
    }
}
