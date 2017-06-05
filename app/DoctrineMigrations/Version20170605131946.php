<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170605131946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE favorite_discount_codes (id INT AUTO_INCREMENT NOT NULL, passenger_id INT DEFAULT NULL, discount_code_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, INDEX IDX_C2ECE0884502E565 (passenger_id), INDEX IDX_C2ECE08891D29306 (discount_code_id), UNIQUE INDEX unique_code_per_user (passenger_id, discount_code_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favorite_discount_codes ADD CONSTRAINT FK_C2ECE0884502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorite_discount_codes ADD CONSTRAINT FK_C2ECE08891D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE user_discount_codes');
        $this->addSql('ALTER TABLE user CHANGE born born DATE DEFAULT \'2017/01/01\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_discount_codes (user_id INT NOT NULL, discount_code_id INT NOT NULL, INDEX IDX_A502C683A76ED395 (user_id), INDEX IDX_A502C68391D29306 (discount_code_id), PRIMARY KEY(user_id, discount_code_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_discount_codes ADD CONSTRAINT FK_A502C68391D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('ALTER TABLE user_discount_codes ADD CONSTRAINT FK_A502C683A76ED395 FOREIGN KEY (user_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE favorite_discount_codes');
        $this->addSql('ALTER TABLE user CHANGE born born DATE DEFAULT \'2017-01-01\'');
    }
}
