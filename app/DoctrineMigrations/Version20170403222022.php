<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170403222022 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE base_cost (id INT AUTO_INCREMENT NOT NULL, entrance_fee INT NOT NULL, cost_per_meter NUMERIC(5, 2) NOT NULL, discount_percent SMALLINT NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service ADD base_cost_id INT DEFAULT NULL, ADD real_distance INT DEFAULT NULL, CHANGE end_point end_point POINT NOT NULL COMMENT \'(DC2Type:point)\', CHANGE distance distance INT NOT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD229B93C1B FOREIGN KEY (base_cost_id) REFERENCES base_cost (id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD229B93C1B ON service (base_cost_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD229B93C1B');
        $this->addSql('DROP TABLE base_cost');
        $this->addSql('DROP INDEX IDX_E19D9AD229B93C1B ON service');
        $this->addSql('ALTER TABLE service DROP base_cost_id, DROP real_distance, CHANGE end_point end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', CHANGE distance distance INT DEFAULT NULL');
    }
}
