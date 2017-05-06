<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170505115955 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address ADD area SMALLINT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address DROP area');
        $this->addSql('CREATE SPATIAL INDEX point ON address (point)');
        $this->addSql('CREATE SPATIAL INDEX route ON car_route (route)');
        $this->addSql('ALTER TABLE driver CHANGE born born DATE DEFAULT \'2017-01-01\' NOT NULL');
        $this->addSql('CREATE SPATIAL INDEX location ON ip_location (location)');
        $this->addSql('CREATE SPATIAL INDEX region ON region (region)');
        $this->addSql('CREATE SPATIAL INDEX start_point ON service (start_point)');
        $this->addSql('CREATE SPATIAL INDEX point ON wakeful (point)');
    }
}
