<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160211140038 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD wrong_password_count SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE driver ADD mobile VARCHAR(11) DEFAULT NULL, ADD mobile_canonical VARCHAR(11) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11667CD923CCBBCB ON driver (mobile_canonical)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_11667CD923CCBBCB ON driver');
        $this->addSql('ALTER TABLE driver DROP mobile, DROP mobile_canonical');
        $this->addSql('ALTER TABLE user DROP wrong_password_count');
    }
}
