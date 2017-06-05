<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170605102151 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_discount_codes (user_id INT NOT NULL, discount_code_id INT NOT NULL, INDEX IDX_A502C683A76ED395 (user_id), INDEX IDX_A502C68391D29306 (discount_code_id), PRIMARY KEY(user_id, discount_code_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_discount_codes ADD CONSTRAINT FK_A502C683A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_discount_codes ADD CONSTRAINT FK_A502C68391D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('DROP TABLE followed_by');
        $this->addSql('ALTER TABLE user CHANGE born born DATE DEFAULT \'2017/01/01\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE followed_by (discount_code_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_59A4450291D29306 (discount_code_id), INDEX IDX_59A44502A76ED395 (user_id), PRIMARY KEY(discount_code_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE followed_by ADD CONSTRAINT FK_59A4450291D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('ALTER TABLE followed_by ADD CONSTRAINT FK_59A44502A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE user_discount_codes');
        $this->addSql('ALTER TABLE user CHANGE born born DATE DEFAULT \'2017-01-01\'');
    }
}
