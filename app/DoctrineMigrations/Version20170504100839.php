<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170504100839 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver ADD parent_name VARCHAR(50) NOT NULL, ADD born DATE DEFAULT \'2017/01/01\' NOT NULL, ADD education SMALLINT NOT NULL, ADD cod_status SMALLINT NOT NULL, ADD is_marriage TINYINT(1) NOT NULL, ADD sheba_number VARCHAR(20) NOT NULL, ADD start_activity DATE NOT NULL, ADD end_activity DATE NOT NULL, ADD learning_course TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver DROP parent_name, DROP born, DROP education, DROP cod_status, DROP is_marriage, DROP sheba_number, DROP start_activity, DROP end_activity, DROP learning_course');
    }
}
