<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160123131938 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_at INT DEFAULT NULL, deleted_by INT DEFAULT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, name VARCHAR(50) NOT NULL, age SMALLINT DEFAULT NULL, sex SMALLINT DEFAULT NULL, describtion LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, created_by DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, disc SMALLINT NOT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), INDEX IDX_8D93D6498B8E8428 (created_at), INDEX IDX_8D93D6491F6FA0AF (deleted_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE passenger (id INT NOT NULL, referer_id INT DEFAULT NULL, mobile VARCHAR(11) NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_3BEFE8DD3C7323E0 (mobile), INDEX IDX_3BEFE8DD87C61384 (referer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, deleted_by INT DEFAULT NULL, type VARCHAR(50) NOT NULL, plaque VARCHAR(15) NOT NULL, color VARCHAR(15) NOT NULL, born DATE NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, discription LONGTEXT DEFAULT NULL, deleted_at DATETIME NOT NULL, INDEX IDX_773DE69DC3423909 (driver_id), INDEX IDX_773DE69D1F6FA0AF (deleted_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE driver (id INT NOT NULL, address_id INT DEFAULT NULL, agency_id INT NOT NULL, contract_number VARCHAR(20) NOT NULL, contact LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', national_code VARCHAR(255) NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_11667CD9AAD0FA19 (contract_number), UNIQUE INDEX UNIQ_11667CD9D3C17DD2 (national_code), UNIQUE INDEX UNIQ_11667CD9F5B7AF75 (address_id), INDEX IDX_11667CD9CDEADB2A (agency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agent (id INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, address_id INT NOT NULL, contacts LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', description LONGTEXT DEFAULT NULL, disc SMALLINT NOT NULL, INDEX IDX_268B9C9D642B8210 (admin_id), UNIQUE INDEX UNIQ_268B9C9DF5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agency (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, city INT NOT NULL, title VARCHAR(255) NOT NULL, point POINT NOT NULL COMMENT \'(DC2Type:point)\', postal_code VARCHAR(10) DEFAULT NULL, address LONGTEXT NOT NULL, INDEX IDX_D4E6F812D5B0234 (city), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, parent INT DEFAULT NULL, name VARCHAR(255) NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', lft INT NOT NULL, rgt INT NOT NULL, lvl INT NOT NULL, INDEX IDX_2D5B02343D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498B8E8428 FOREIGN KEY (created_at) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491F6FA0AF FOREIGN KEY (deleted_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD87C61384 FOREIGN KEY (referer_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DDBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D1F6FA0AF FOREIGN KEY (deleted_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9CDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9DF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE agency ADD CONSTRAINT FK_70C0C6E6BF396750 FOREIGN KEY (id) REFERENCES agent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F812D5B0234 FOREIGN KEY (city) REFERENCES city (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02343D8E604F FOREIGN KEY (parent) REFERENCES city (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498B8E8428');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491F6FA0AF');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DDBF396750');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D1F6FA0AF');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9BF396750');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D642B8210');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD87C61384');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DC3423909');
        $this->addSql('ALTER TABLE agency DROP FOREIGN KEY FK_70C0C6E6BF396750');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9CDEADB2A');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9F5B7AF75');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9DF5B7AF75');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F812D5B0234');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02343D8E604F');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE passenger');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE agent');
        $this->addSql('DROP TABLE agency');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE city');
    }
}
