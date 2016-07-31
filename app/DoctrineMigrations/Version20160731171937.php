<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160731171937 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE propagation_list (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, car_id INT NOT NULL, number SMALLINT NOT NULL, answer SMALLINT NOT NULL, notify_status SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FBA31CC9ED5CA9E6 (service_id), INDEX IDX_FBA31CC9C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES requested (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE requested ADD start_address VARCHAR(500) DEFAULT NULL, ADD end_address VARCHAR(500) DEFAULT NULL, ADD propagation_type SMALLINT NOT NULL, CHANGE type type SMALLINT NOT NULL, CHANGE desire desire SMALLINT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE propagation_list');
        $this->addSql('ALTER TABLE requested DROP start_address, DROP end_address, DROP propagation_type, CHANGE type type VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, CHANGE desire desire VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
