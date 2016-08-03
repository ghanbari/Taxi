<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160802130525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE floating_cost DROP FOREIGN KEY FK_5E815DD1ED5CA9E6');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9ED5CA9E6');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, passenger_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, car_id INT DEFAULT NULL, start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', start_address VARCHAR(500) DEFAULT NULL, end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', end_address VARCHAR(500) DEFAULT NULL, type SMALLINT NOT NULL, desire SMALLINT NOT NULL, description LONGTEXT DEFAULT NULL, propagation_type SMALLINT NOT NULL, driver_rate NUMERIC(1, 0) DEFAULT NULL, passenger_rate NUMERIC(1, 0) DEFAULT NULL, route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', distance NUMERIC(6, 3) DEFAULT NULL, price INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_E19D9AD24502E565 (passenger_id), INDEX IDX_E19D9AD23414710B (agent_id), INDEX IDX_E19D9AD2C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD24502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD23414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
        $this->addSql('DROP TABLE requested');
        $this->addSql('ALTER TABLE driver_log CHANGE log status VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE floating_cost DROP FOREIGN KEY FK_5E815DD1ED5CA9E6');
        $this->addSql('ALTER TABLE floating_cost ADD CONSTRAINT FK_5E815DD1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wakeful CHANGE car_id car_id INT NOT NULL');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9ED5CA9E6');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE floating_cost DROP FOREIGN KEY FK_5E815DD1ED5CA9E6');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9ED5CA9E6');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('CREATE TABLE requested (id INT AUTO_INCREMENT NOT NULL, agent_id INT DEFAULT NULL, passenger_id INT DEFAULT NULL, car_id INT DEFAULT NULL, type SMALLINT NOT NULL, desire SMALLINT NOT NULL, description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, driver_rate NUMERIC(1, 0) DEFAULT NULL, passenger_rate NUMERIC(1, 0) DEFAULT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', distance NUMERIC(6, 3) DEFAULT NULL, price INT DEFAULT NULL, start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, start_address VARCHAR(500) DEFAULT NULL COLLATE utf8_unicode_ci, end_address VARCHAR(500) DEFAULT NULL COLLATE utf8_unicode_ci, propagation_type SMALLINT NOT NULL, INDEX IDX_98521BAF4502E565 (passenger_id), INDEX IDX_98521BAF3414710B (agent_id), INDEX IDX_98521BAFC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF4502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAFC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('DROP TABLE service');
        $this->addSql('ALTER TABLE driver_log CHANGE status log VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE floating_cost DROP FOREIGN KEY FK_5E815DD1ED5CA9E6');
        $this->addSql('ALTER TABLE floating_cost ADD CONSTRAINT FK_5E815DD1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9ED5CA9E6');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wakeful CHANGE car_id car_id INT DEFAULT NULL');
    }
}
