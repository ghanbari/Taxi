<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170728140823 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD291D29306');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD291D29306 FOREIGN KEY (discount_code_id) REFERENCES favorite_discount_codes (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD291D29306');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD291D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
    }
}
