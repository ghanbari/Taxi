<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160804092306 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, passenger_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, car_id INT DEFAULT NULL, start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', start_address VARCHAR(500) DEFAULT NULL, end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', end_address VARCHAR(500) DEFAULT NULL, type SMALLINT NOT NULL, desire SMALLINT NOT NULL, description LONGTEXT DEFAULT NULL, propagation_type SMALLINT NOT NULL, driver_rate NUMERIC(1, 0) DEFAULT NULL, passenger_rate NUMERIC(1, 0) DEFAULT NULL, route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', distance NUMERIC(6, 3) DEFAULT NULL, price INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_E19D9AD24502E565 (passenger_id), INDEX IDX_E19D9AD23414710B (agent_id), INDEX IDX_E19D9AD2C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE floating_cost (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, cost INT NOT NULL, description VARCHAR(50) NOT NULL, INDEX IDX_5E815DD1ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE propagation_list (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, car_id INT NOT NULL, number SMALLINT NOT NULL, answer SMALLINT DEFAULT NULL, notify_status SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FBA31CC9ED5CA9E6 (service_id), INDEX IDX_FBA31CC9C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_log (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, status VARCHAR(15) NOT NULL, at_time DATETIME NOT NULL, INDEX IDX_AD6F1BB2ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD24502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD23414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE floating_cost ADD CONSTRAINT FK_5E815DD1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE service_log ADD CONSTRAINT FK_AD6F1BB2ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE requested');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_token_per_user ON token (token, user_id)');
        $this->addSql('ALTER TABLE user ADD api_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C912ED9D ON user (api_key)');
        $this->addSql('ALTER TABLE device CHANGE device_identifier device_identifier VARCHAR(50) NOT NULL, CHANGE device_model device_model VARCHAR(100) NOT NULL, CHANGE api_key api_key VARCHAR(255) NOT NULL, CHANGE app_name app_name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE driver_log ADD status SMALLINT NOT NULL, DROP log');
        $this->addSql('ALTER TABLE car CHANGE status status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE wakeful CHANGE car_id car_id INT NOT NULL');
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
        $this->addSql('ALTER TABLE service_log DROP FOREIGN KEY FK_AD6F1BB2ED5CA9E6');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, status VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, at_time DATETIME NOT NULL, INDEX IDX_8F3F68C5ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE requested (id INT AUTO_INCREMENT NOT NULL, agent_id INT DEFAULT NULL, passenger_id INT DEFAULT NULL, car_id INT DEFAULT NULL, type VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, desire VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, description LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, driver_rate NUMERIC(2, 1) DEFAULT NULL, passenger_rate NUMERIC(2, 1) DEFAULT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', distance NUMERIC(6, 3) DEFAULT NULL, price INT DEFAULT NULL, start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_98521BAF4502E565 (passenger_id), INDEX IDX_98521BAF3414710B (agent_id), INDEX IDX_98521BAFC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF4502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAFC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE floating_cost');
        $this->addSql('DROP TABLE propagation_list');
        $this->addSql('DROP TABLE service_log');
        $this->addSql('ALTER TABLE car CHANGE status status VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE device CHANGE device_identifier device_identifier VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE device_model device_model VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE app_name app_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE api_key api_key VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE driver_log ADD log VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, DROP status');
        $this->addSql('DROP INDEX UNIQUE_token_per_user ON token');
        $this->addSql('DROP INDEX UNIQ_8D93D649C912ED9D ON user');
        $this->addSql('ALTER TABLE user DROP api_key');
        $this->addSql('ALTER TABLE wakeful CHANGE car_id car_id INT DEFAULT NULL');
    }
}
