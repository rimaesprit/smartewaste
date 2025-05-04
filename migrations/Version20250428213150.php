<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428213150 extends AbstractMigration
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
            ALTER TABLE image CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, CHANGE date_rec date_rec DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE etat_rec etat_rec VARCHAR(50) DEFAULT 'Pending' NOT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signabstract CHANGE type_sign type_sign VARCHAR(255) DEFAULT NULL, CHANGE temps_sign temps_sign DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE zone zone VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE etat_sign etat_sign VARCHAR(50) DEFAULT 'Pending' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE email_verification_token email_verification_token VARCHAR(255) DEFAULT NULL, CHANGE temp_email temp_email VARCHAR(180) DEFAULT NULL
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
            ALTER TABLE image CHANGE image_name image_name VARCHAR(255) DEFAULT 'NULL', CHANGE updated_at updated_at DATETIME DEFAULT 'NULL' COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle CHANGE latitude latitude DOUBLE PRECISION DEFAULT 'NULL', CHANGE longitude longitude DOUBLE PRECISION DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP latitude, DROP longitude, CHANGE date_rec date_rec DATETIME DEFAULT 'current_timestamp()' NOT NULL, CHANGE etat_rec etat_rec VARCHAR(50) DEFAULT '''Pending''' NOT NULL, CHANGE address address VARCHAR(255) DEFAULT 'NULL', CHANGE photo photo VARCHAR(255) DEFAULT 'NULL', CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signabstract CHANGE type_sign type_sign VARCHAR(255) DEFAULT 'NULL', CHANGE temps_sign temps_sign DATETIME DEFAULT 'current_timestamp()', CHANGE zone zone VARCHAR(255) DEFAULT 'NULL', CHANGE adresse adresse VARCHAR(255) DEFAULT 'NULL', CHANGE etat_sign etat_sign VARCHAR(50) DEFAULT '''Pending''' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE reset_token reset_token VARCHAR(255) DEFAULT 'NULL', CHANGE verification_token verification_token VARCHAR(255) DEFAULT 'NULL', CHANGE email_verification_token email_verification_token VARCHAR(255) DEFAULT 'NULL', CHANGE temp_email temp_email VARCHAR(180) DEFAULT 'NULL'
        SQL);
    }
}
