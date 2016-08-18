<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160819020622 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, priority SMALLINT NOT NULL, type SMALLINT NOT NULL, status SMALLINT DEFAULT NULL, rtl INT NOT NULL, lft INT NOT NULL, root INT DEFAULT NULL, lvl INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_97A0ADA3A76ED395 (user_id), INDEX IDX_97A0ADA3727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3727ACA70 FOREIGN KEY (parent_id) REFERENCES ticket (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3727ACA70');
        $this->addSql('DROP TABLE ticket');
    }
}
