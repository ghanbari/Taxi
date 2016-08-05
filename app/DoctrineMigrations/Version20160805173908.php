<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160805173908 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE canceled_reason (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service ADD canceled_by INT DEFAULT NULL, ADD canceled_reason INT NOT NULL, ADD canceled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C8D4ECF FOREIGN KEY (canceled_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C9712EEB FOREIGN KEY (canceled_reason) REFERENCES canceled_reason (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_E19D9AD2C8D4ECF ON service (canceled_by)');
        $this->addSql('CREATE INDEX IDX_E19D9AD2C9712EEB ON service (canceled_reason)');
        $this->addSql('ALTER TABLE service_log CHANGE status status SMALLINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX service_log_UNIQUE ON service_log (status, service_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2C9712EEB');
        $this->addSql('DROP TABLE canceled_reason');
        $this->addSql('DROP INDEX IDX_E19D9AD2C8D4ECF ON service');
        $this->addSql('DROP INDEX IDX_E19D9AD2C9712EEB ON service');
        $this->addSql('ALTER TABLE service DROP canceled_by, DROP canceled_reason, DROP canceled_at');
        $this->addSql('DROP INDEX service_log_UNIQUE ON service_log');
        $this->addSql('ALTER TABLE service_log CHANGE status status VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
