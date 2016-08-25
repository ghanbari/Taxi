<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160826011817 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wakeful ADD SPATIAL INDEX(point);');
        $this->addSql('ALTER TABLE service ADD SPATIAL INDEX(start_point);');
//        $this->addSql('ALTER TABLE service ADD SPATIAL INDEX(end_point);');
//        $this->addSql('ALTER TABLE service ADD SPATIAL INDEX(route);');
        $this->addSql('ALTER TABLE region ADD SPATIAL INDEX(region);');
        $this->addSql('ALTER TABLE ip_location ADD SPATIAL INDEX(location);');
//        $this->addSql('ALTER TABLE city ADD SPATIAL INDEX(point);');
        $this->addSql('ALTER TABLE car_route ADD SPATIAL INDEX(route);');
//        $this->addSql('ALTER TABLE car_log ADD SPATIAL INDEX(point);');
        $this->addSql('ALTER TABLE address ADD SPATIAL INDEX(point);');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wakeful DROP INDEX point;');
        $this->addSql('ALTER TABLE service DROP INDEX start_point;');
//        $this->addSql('ALTER TABLE service DROP INDEX end_point;');
//        $this->addSql('ALTER TABLE service DROP INDEX route;');
        $this->addSql('ALTER TABLE region DROP INDEX region;');
        $this->addSql('ALTER TABLE ip_location DROP INDEX location;');
//        $this->addSql('ALTER TABLE city DROP INDEX point;');
        $this->addSql('ALTER TABLE car_route DROP INDEX route;');
//        $this->addSql('ALTER TABLE car_log DROP INDEX point;');
        $this->addSql('ALTER TABLE address DROP INDEX point;');
    }
}
