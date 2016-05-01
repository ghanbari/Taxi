<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160501235538 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, device_id INT DEFAULT NULL, INDEX IDX_B6BD307F94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, device_token LONGTEXT NOT NULL, device_identifier VARCHAR(255) NOT NULL, is_sound_allowed TINYINT(1) NOT NULL, is_alert_allowed TINYINT(1) NOT NULL, device_name VARCHAR(255) NOT NULL, os VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, device_model VARCHAR(255) NOT NULL, device_version VARCHAR(30) NOT NULL, app_version VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, api_key VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_92FB68EF01DC0AC (device_identifier), INDEX IDX_92FB68E7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD23CCBBCB ON passenger');
        $this->addSql('ALTER TABLE passenger ADD token VARCHAR(255) NOT NULL, ADD token_requested_at DATETIME NOT NULL, DROP mobile_canonical');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD3C7323E0 ON passenger (mobile)');
        $this->addSql('DROP INDEX UNIQ_11667CD923CCBBCB ON driver');
        $this->addSql('ALTER TABLE driver DROP mobile_canonical');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11667CD93C7323E0 ON driver (mobile)');
        $this->addSql('ALTER TABLE passenger CHANGE token token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger CHANGE token_requested_at token_requested_at DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F94A4C7D4');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP INDEX UNIQ_11667CD93C7323E0 ON driver');
        $this->addSql('ALTER TABLE driver ADD mobile_canonical VARCHAR(11) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11667CD923CCBBCB ON driver (mobile_canonical)');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD3C7323E0 ON passenger');
        $this->addSql('ALTER TABLE passenger ADD mobile_canonical VARCHAR(11) DEFAULT NULL COLLATE utf8_unicode_ci, DROP token, DROP token_requested_at');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD23CCBBCB ON passenger (mobile_canonical)');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE passenger CHANGE token token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE passenger CHANGE token_requested_at token_requested_at DATETIME NOT NULL');
    }
}
