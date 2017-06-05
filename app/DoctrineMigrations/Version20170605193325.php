<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170605193325 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE discounts_used_by');
        $this->addSql('ALTER TABLE service ADD discount_code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD291D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD291D29306 ON service (discount_code_id)');
        $this->addSql('ALTER TABLE favorite_discount_codes CHANGE passenger_id passenger_id INT NOT NULL, CHANGE discount_code_id discount_code_id INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discounts_used_by (discount_code_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2865D69191D29306 (discount_code_id), INDEX IDX_2865D691A76ED395 (user_id), PRIMARY KEY(discount_code_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discounts_used_by ADD CONSTRAINT FK_2865D69191D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('ALTER TABLE discounts_used_by ADD CONSTRAINT FK_2865D691A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorite_discount_codes CHANGE passenger_id passenger_id INT DEFAULT NULL, CHANGE discount_code_id discount_code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD291D29306');
        $this->addSql('DROP INDEX IDX_E19D9AD291D29306 ON service');
        $this->addSql('ALTER TABLE service DROP discount_code_id');
    }
}
