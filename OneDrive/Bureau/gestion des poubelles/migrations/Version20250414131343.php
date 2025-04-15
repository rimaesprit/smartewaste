<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414131343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bienetre (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, nom VARCHAR(255) NOT NULL, review LONGTEXT NOT NULL, rate INT NOT NULL, sentiment VARCHAR(100) NOT NULL, INDEX IDX_E8286F42F231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE poubelle (id INT AUTO_INCREMENT NOT NULL, localisation VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, niveau_remplissage DOUBLE PRECISION NOT NULL, type VARCHAR(50) NOT NULL, statut VARCHAR(50) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bienetre ADD CONSTRAINT FK_E8286F42F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bienetre DROP FOREIGN KEY FK_E8286F42F231B082
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bienetre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE poubelle
        SQL);
    }
}
