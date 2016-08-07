<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160806130432 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9C3C6F69F');
        $this->addSql('DROP INDEX IDX_FBA31CC9C3C6F69F ON propagation_list');
        $this->addSql('ALTER TABLE propagation_list DROP number, CHANGE car_id driver_id INT NOT NULL');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_FBA31CC9C3423909 ON propagation_list (driver_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9C3423909');
        $this->addSql('DROP INDEX IDX_FBA31CC9C3423909 ON propagation_list');
        $this->addSql('ALTER TABLE propagation_list ADD number SMALLINT NOT NULL, CHANGE driver_id car_id INT NOT NULL');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('CREATE INDEX IDX_FBA31CC9C3C6F69F ON propagation_list (car_id)');
    }
}
