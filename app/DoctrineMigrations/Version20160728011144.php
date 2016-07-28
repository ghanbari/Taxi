<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160728011144 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQUE_token_per_user ON token (token, user_id)');
        $this->addSql('ALTER TABLE device CHANGE device_identifier device_identifier VARCHAR(50) NOT NULL, CHANGE device_model device_model VARCHAR(100) NOT NULL, CHANGE api_key api_key VARCHAR(255) NOT NULL, CHANGE app_name app_name VARCHAR(50) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE device CHANGE device_identifier device_identifier VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE device_model device_model VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE app_name app_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE api_key api_key VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('DROP INDEX UNIQUE_token_per_user ON token');
    }
}
