<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421222705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE camion CHANGE kilometrage kilometrage DOUBLE PRECISION DEFAULT NULL, CHANGE type_moteur type_moteur VARCHAR(255) DEFAULT NULL, CHANGE emission_co2 emission_co2 DOUBLE PRECISION DEFAULT NULL, CHANGE consommation consommation DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE camion RENAME INDEX uniq_3b46967e61b51b TO UNIQ_5DD566EC12B2DC9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet DROP FOREIGN KEY FK_872DDD1132B446C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_872DDD188CCBB6D ON dechet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet ADD type_dechet VARCHAR(255) NOT NULL, ADD date_depot DATE NOT NULL, ADD favori TINYINT(1) NOT NULL, ADD traite TINYINT(1) NOT NULL, ADD date_traitement DATETIME DEFAULT NULL, DROP type, DROP date_collecte, DROP origine, DROP methode_traitement, DROP statut, DROP conteneur_id, DROP created_at, DROP updated_at, CHANGE camion_id camion_id INT NOT NULL, CHANGE quantite poids DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet ADD CONSTRAINT FK_53C0FC603A706D3 FOREIGN KEY (camion_id) REFERENCES camion (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet RENAME INDEX idx_872ddd1132b446c TO IDX_53C0FC603A706D3
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE camion CHANGE type_moteur type_moteur VARCHAR(255) DEFAULT 'NULL', CHANGE emission_co2 emission_co2 DOUBLE PRECISION DEFAULT 'NULL', CHANGE consommation consommation DOUBLE PRECISION DEFAULT 'NULL', CHANGE kilometrage kilometrage DOUBLE PRECISION DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE camion RENAME INDEX uniq_5dd566ec12b2dc9c TO UNIQ_3B46967E61B51B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet DROP FOREIGN KEY FK_53C0FC603A706D3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet ADD type VARCHAR(100) NOT NULL, ADD date_collecte DATETIME DEFAULT 'NULL', ADD origine VARCHAR(255) DEFAULT 'NULL', ADD methode_traitement VARCHAR(100) DEFAULT 'NULL', ADD statut VARCHAR(50) DEFAULT 'NULL', ADD conteneur_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT 'current_timestamp()', ADD updated_at DATETIME DEFAULT 'current_timestamp()', DROP type_dechet, DROP date_depot, DROP favori, DROP traite, DROP date_traitement, CHANGE camion_id camion_id INT DEFAULT NULL, CHANGE poids quantite DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet ADD CONSTRAINT FK_872DDD1132B446C FOREIGN KEY (camion_id) REFERENCES camion (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_872DDD188CCBB6D ON dechet (conteneur_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dechet RENAME INDEX idx_53c0fc603a706d3 TO IDX_872DDD1132B446C
        SQL);
    }
}
