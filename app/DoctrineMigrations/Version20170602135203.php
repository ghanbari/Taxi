<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170602135203 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD born DATE DEFAULT \'2017/01/01\'');
        $this->addSql('ALTER TABLE car CHANGE color color VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE driver DROP born');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE car CHANGE color color VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE driver ADD born DATE DEFAULT \'2017-01-01\' NOT NULL');
        $this->addSql('ALTER TABLE user DROP born');
    }
}
