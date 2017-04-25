<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170424134542 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car ADD third_party_insurance DATE DEFAULT \'2000-01-01\' NOT NULL, ADD pull_insurance DATE DEFAULT \'2000-01-01\' NOT NULL, ADD technical_diagnosis DATE DEFAULT \'2000-01-01\' NOT NULL, ADD traffic_plan DATE DEFAULT \'2000-01-01\' NOT NULL, ADD body_quality VARCHAR(10) DEFAULT \'good\' NOT NULL, ADD inside_quality VARCHAR(10) DEFAULT \'good\' NOT NULL, ADD ownership VARCHAR(10) DEFAULT \'own\' NOT NULL, DROP brand, DROP rate, CHANGE type type SMALLINT NOT NULL, CHANGE color color VARCHAR(6) NOT NULL, CHANGE born born VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car ADD brand VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD rate NUMERIC(2, 1) DEFAULT \'0.0\' NOT NULL, DROP third_party_insurance, DROP pull_insurance, DROP technical_diagnosis, DROP traffic_plan, DROP body_quality, DROP inside_quality, DROP ownership, CHANGE type type VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE color color VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, CHANGE born born DATE NOT NULL');
    }
}
