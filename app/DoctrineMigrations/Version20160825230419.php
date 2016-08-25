<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160825230419 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE car_log (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, at_time DATETIME NOT NULL, status SMALLINT NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', INDEX IDX_84984875C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE car_log ADD CONSTRAINT FK_84984875C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE driver_log');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE driver_log (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, at_time DATETIME NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', status SMALLINT NOT NULL, INDEX IDX_9DF4FACBC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_log ADD CONSTRAINT FK_9DF4FACBC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE car_log');
    }
}
