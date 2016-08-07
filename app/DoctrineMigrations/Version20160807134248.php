<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160807134248 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message ADD service_id INT DEFAULT NULL, ADD type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FED5CA9E6 ON message (service_id)');
        $this->addSql('ALTER TABLE car CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE propagation_list CHANGE notify_status number SMALLINT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car CHANGE is_current is_current TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FED5CA9E6');
        $this->addSql('DROP INDEX IDX_B6BD307FED5CA9E6 ON message');
        $this->addSql('ALTER TABLE message DROP service_id, DROP type');
        $this->addSql('ALTER TABLE propagation_list CHANGE number notify_status SMALLINT NOT NULL');
    }
}
