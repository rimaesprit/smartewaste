<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408013826 extends AbstractMigration
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
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD verification_token VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
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
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT 'NULL' COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP verification_token, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE reset_token reset_token VARCHAR(255) DEFAULT 'NULL'
        SQL);
    }
}
