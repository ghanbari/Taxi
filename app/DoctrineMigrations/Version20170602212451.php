<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170602212451 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discount_code (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, code VARCHAR(10) NOT NULL, max_usage INT NOT NULL, max_usage_per_user INT NOT NULL, location POINT NOT NULL COMMENT \'(DC2Type:point)\', location_radius INT NOT NULL, discount INT NOT NULL, created_at DATE NOT NULL, expired_at DATE NOT NULL, UNIQUE INDEX UNIQ_E997352277153098 (code), INDEX IDX_E9973522B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discounts_used_by (discount_code_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2865D69191D29306 (discount_code_id), INDEX IDX_2865D691A76ED395 (user_id), PRIMARY KEY(discount_code_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discount_code ADD CONSTRAINT FK_E9973522B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discounts_used_by ADD CONSTRAINT FK_2865D69191D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('ALTER TABLE discounts_used_by ADD CONSTRAINT FK_2865D691A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discounts_used_by DROP FOREIGN KEY FK_2865D69191D29306');
        $this->addSql('DROP TABLE discount_code');
        $this->addSql('DROP TABLE discounts_used_by');
    }
}
