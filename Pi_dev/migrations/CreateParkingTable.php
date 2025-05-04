<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class CreateParkingTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table parking si elle n\'existe pas';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS parking (
            id INT AUTO_INCREMENT NOT NULL,
            camion_id INT NOT NULL,
            conteneur_id INT NOT NULL,
            date_entree DATETIME NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_B237527AB2F96AFC (camion_id),
            INDEX IDX_B237527A8895EFB8 (conteneur_id),
            CONSTRAINT FK_B237527AB2F96AFC FOREIGN KEY (camion_id) REFERENCES camion (id),
            CONSTRAINT FK_B237527A8895EFB8 FOREIGN KEY (conteneur_id) REFERENCES conteneur (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS parking');
    }
} 