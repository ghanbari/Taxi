<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160603173933 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE plaque (id INT AUTO_INCREMENT NOT NULL, first_number SMALLINT NOT NULL, second_number SMALLINT NOT NULL, city_number SMALLINT NOT NULL, area_code VARCHAR(2) NOT NULL, UNIQUE INDEX plaque_UNIQUE (first_number, second_number, city_number, area_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP INDEX car_driver_UNIQUE ON car');
        $this->addSql('ALTER TABLE car ADD plaque_id INT NOT NULL, ADD brand VARCHAR(255) NOT NULL, DROP plaque');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DD89B8F16 FOREIGN KEY (plaque_id) REFERENCES plaque (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_773DE69DD89B8F16 ON car (plaque_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DD89B8F16');
        $this->addSql('DROP TABLE plaque');
        $this->addSql('DROP INDEX UNIQ_773DE69DD89B8F16 ON car');
        $this->addSql('ALTER TABLE car ADD plaque VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, DROP plaque_id, DROP brand');
        $this->addSql('CREATE UNIQUE INDEX car_driver_UNIQUE ON car (driver_id, is_current)');
    }
}
