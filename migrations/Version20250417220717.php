<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417220717 extends AbstractMigration
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
            CREATE TABLE camion (id INT AUTO_INCREMENT NOT NULL, matricule VARCHAR(255) NOT NULL, capacite DOUBLE PRECISION NOT NULL, etat VARCHAR(255) NOT NULL, type_moteur VARCHAR(255) DEFAULT NULL, emission_co2 DOUBLE PRECISION DEFAULT NULL, consommation DOUBLE PRECISION DEFAULT NULL, annee_fabrication INT DEFAULT NULL, kilometrage DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_5DD566EC12B2DC9C (matricule), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE dechet (id INT AUTO_INCREMENT NOT NULL, camion_id INT NOT NULL, type_dechet VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, date_depot DATE NOT NULL, favori TINYINT(1) NOT NULL, traite TINYINT(1) NOT NULL, date_traitement DATETIME DEFAULT NULL, INDEX IDX_53C0FC603A706D3 (camion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE poubelle (id INT AUTO_INCREMENT NOT NULL, localisation VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, niveau_remplissage DOUBLE PRECISION NOT NULL, type VARCHAR(50) NOT NULL, statut VARCHAR(50) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation (user_id INT DEFAULT NULL, ID_rec INT AUTO_INCREMENT NOT NULL, type_rec VARCHAR(255) NOT NULL, reclamation LONGTEXT NOT NULL, date_rec DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, etat_rec VARCHAR(50) DEFAULT 'Pending' NOT NULL, reponse LONGTEXT DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_CE606404A76ED395 (user_id), PRIMARY KEY(ID_rec)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE signabstract (user_id INT DEFAULT NULL, ID_signa INT AUTO_INCREMENT NOT NULL, type_sign VARCHAR(255) DEFAULT NULL, temps_sign DATETIME DEFAULT CURRENT_TIMESTAMP, zone VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, etat_sign VARCHAR(50) DEFAULT 'Pending' NOT NULL, feedback LONGTEXT DEFAULT NULL, INDEX IDX_B415F821A76ED395 (user_id), PRIMARY KEY(ID_signa)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE temp_email (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, email VARCHAR(180) NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_7132DD75A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, verification_token VARCHAR(255) DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, temp_email VARCHAR(180) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bienetre ADD CONSTRAINT FK_E8286F42F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet ADD CONSTRAINT FK_53C0FC603A706D3 FOREIGN KEY (camion_id) REFERENCES camion (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signabstract ADD CONSTRAINT FK_B415F821A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE temp_email ADD CONSTRAINT FK_7132DD75A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bienetre DROP FOREIGN KEY FK_E8286F42F231B082
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet DROP FOREIGN KEY FK_53C0FC603A706D3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signabstract DROP FOREIGN KEY FK_B415F821A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE temp_email DROP FOREIGN KEY FK_7132DD75A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bienetre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE camion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE dechet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE poubelle
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE signabstract
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE temp_email
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
