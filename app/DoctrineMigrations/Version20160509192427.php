<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160509192427 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_92FB68EF01DC0AC ON device');
        $this->addSql('ALTER TABLE device ADD app_name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX device_identifier_UNIQUE ON device (device_identifier, app_name)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX device_identifier_UNIQUE ON device');
        $this->addSql('ALTER TABLE device DROP app_name');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92FB68EF01DC0AC ON device (device_identifier)');
    }
}
