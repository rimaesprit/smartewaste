<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421181408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE camion CHANGE type_moteur type_moteur VARCHAR(255) DEFAULT NULL, CHANGE emission_co2 emission_co2 DOUBLE PRECISION DEFAULT NULL, CHANGE consommation consommation DOUBLE PRECISION DEFAULT NULL, CHANGE kilometrage kilometrage DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet CHANGE date_traitement date_traitement DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE camion CHANGE type_moteur type_moteur VARCHAR(255) DEFAULT 'NULL', CHANGE emission_co2 emission_co2 DOUBLE PRECISION DEFAULT 'NULL', CHANGE consommation consommation DOUBLE PRECISION DEFAULT 'NULL', CHANGE kilometrage kilometrage DOUBLE PRECISION DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet CHANGE date_traitement date_traitement DATETIME DEFAULT 'NULL'
        SQL);
    }
}
