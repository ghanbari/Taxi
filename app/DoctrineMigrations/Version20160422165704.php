<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160422165704 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498B8E8428');
        $this->addSql('DROP INDEX IDX_8D93D6498B8E8428 ON user');
        $this->addSql('ALTER TABLE user ADD updated_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE created_by created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D649DE12AB56 ON user (created_by)');
        $this->addSql('ALTER TABLE agent ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE requested ADD start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', ADD end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', CHANGE desire desire VARCHAR(15) NOT NULL, CHANGE driver_rate driver_rate NUMERIC(2, 1) DEFAULT NULL, CHANGE passenger_rate passenger_rate NUMERIC(2, 1) DEFAULT NULL, CHANGE route route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE agent DROP deleted_at');
        $this->addSql('ALTER TABLE requested DROP start_point, DROP end_point, CHANGE desire desire VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE driver_rate driver_rate NUMERIC(2, 2) DEFAULT NULL, CHANGE passenger_rate passenger_rate NUMERIC(2, 2) DEFAULT NULL, CHANGE route route LINESTRING NOT NULL COMMENT \'(DC2Type:linestring)\'');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DE12AB56');
        $this->addSql('DROP INDEX IDX_8D93D649DE12AB56 ON user');
        $this->addSql('ALTER TABLE user DROP updated_at, CHANGE created_by created_by DATETIME NOT NULL, CHANGE created_at created_at INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498B8E8428 FOREIGN KEY (created_at) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D6498B8E8428 ON user (created_at)');
    }
}
