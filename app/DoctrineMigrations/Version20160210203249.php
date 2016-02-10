<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160210203249 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE driver_log (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, at_time DATETIME NOT NULL, log VARCHAR(15) NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', INDEX IDX_9DF4FACBC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, status VARCHAR(15) NOT NULL, at_time DATETIME NOT NULL, INDEX IDX_8F3F68C5ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wakeful (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, at_time DATETIME NOT NULL, point POINT NOT NULL COMMENT \'(DC2Type:point)\', UNIQUE INDEX UNIQ_CF07BB1BC3C6F69F (car_id), INDEX wakeful_spatial_point (point), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE requested (id INT AUTO_INCREMENT NOT NULL, passenger_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, car_id INT DEFAULT NULL, type VARCHAR(15) NOT NULL, desire VARCHAR(15) DEFAULT NULL, description LONGTEXT DEFAULT NULL, driver_rate NUMERIC(2, 2) DEFAULT NULL, passenger_rate NUMERIC(2, 2) DEFAULT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, route LINESTRING NOT NULL COMMENT \'(DC2Type:linestring)\', distance NUMERIC(6, 3) DEFAULT NULL, price INT DEFAULT NULL, INDEX IDX_98521BAF4502E565 (passenger_id), INDEX IDX_98521BAF3414710B (agent_id), INDEX IDX_98521BAFC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_log ADD CONSTRAINT FK_9DF4FACBC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wakeful ADD CONSTRAINT FK_CF07BB1BC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF4502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAFC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE username_canonical username_canonical VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE car ADD created_by INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD is_current TINYINT(1) DEFAULT \'1\' NOT NULL, ADD status VARCHAR(15) NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE discription description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_773DE69DDE12AB56 ON car (created_by)');
        $this->addSql('CREATE UNIQUE INDEX car_driver_UNIQUE ON car (driver_id, is_current)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5ED5CA9E6');
        $this->addSql('DROP TABLE driver_log');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE wakeful');
        $this->addSql('DROP TABLE requested');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DDE12AB56');
        $this->addSql('DROP INDEX IDX_773DE69DDE12AB56 ON car');
        $this->addSql('DROP INDEX car_driver_UNIQUE ON car');
        $this->addSql('ALTER TABLE car DROP created_by, DROP created_at, DROP is_current, DROP status, CHANGE deleted_at deleted_at DATETIME NOT NULL, CHANGE description discription LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user CHANGE username_canonical username_canonical VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE username username VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
