<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170604134039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE followed_by (discount_code_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_59A4450291D29306 (discount_code_id), INDEX IDX_59A44502A76ED395 (user_id), PRIMARY KEY(discount_code_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE followed_by ADD CONSTRAINT FK_59A4450291D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('ALTER TABLE followed_by ADD CONSTRAINT FK_59A44502A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discount_code CHANGE location origin_location POINT NOT NULL COMMENT \'(DC2Type:point)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE followed_by');
        $this->addSql('ALTER TABLE discount_code CHANGE origin_location location POINT NOT NULL COMMENT \'(DC2Type:point)\'');
    }
}
