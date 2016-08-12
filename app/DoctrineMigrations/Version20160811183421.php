<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160811183421 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE favorite_route (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(3) NOT NULL, is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6956883F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency_exchange_log (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, exchange NUMERIC(14, 5) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9335771F38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bank (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_enable TINYINT(1) NOT NULL, INDEX IDX_D860BF7A38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_base_price (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, region_id INT DEFAULT NULL, price INT NOT NULL, INDEX IDX_560487EA38248176 (currency_id), INDEX IDX_560487EA98260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, service_id INT DEFAULT NULL, currency_id INT NOT NULL, log_id INT DEFAULT NULL, wallet_id INT DEFAULT NULL, gateway_id INT DEFAULT NULL, moved_wallet_id INT DEFAULT NULL, amount INT NOT NULL, is_virtual TINYINT(1) DEFAULT \'0\' NOT NULL, direction SMALLINT NOT NULL, type SMALLINT NOT NULL, status SMALLINT DEFAULT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D1ED5CA9E6 (service_id), INDEX IDX_723705D138248176 (currency_id), INDEX IDX_723705D1EA675D86 (log_id), INDEX IDX_723705D1712520F3 (wallet_id), INDEX IDX_723705D1577F8E00 (gateway_id), INDEX IDX_723705D12674BBB4 (moved_wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gateway (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, config LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', is_enabled TINYINT(1) NOT NULL, is_default TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_14FEDD7F5E237E06 (name), INDEX IDX_14FEDD7F38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, balance NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, INDEX IDX_7C68921F38248176 (currency_id), INDEX IDX_7C68921F7E3C61F9 (owner_id), UNIQUE INDEX user_wallet_per_currency_UNIQUE (currency_id, owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, region POLYGON NOT NULL COMMENT \'(DC2Type:polygon)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_currency (region_id INT NOT NULL, currency_id INT NOT NULL, INDEX IDX_1A918CCD98260155 (region_id), INDEX IDX_1A918CCD38248176 (currency_id), PRIMARY KEY(region_id, currency_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE credit_card (id INT AUTO_INCREMENT NOT NULL, bank_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, number VARCHAR(30) NOT NULL, expire_at DATE NOT NULL, INDEX IDX_11D627EE11C8FB41 (bank_id), INDEX IDX_11D627EE7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE currency_exchange_log ADD CONSTRAINT FK_9335771F38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE bank ADD CONSTRAINT FK_D860BF7A38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE region_base_price ADD CONSTRAINT FK_560487EA38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE region_base_price ADD CONSTRAINT FK_560487EA98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D138248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1EA675D86 FOREIGN KEY (log_id) REFERENCES currency_exchange_log (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1577F8E00 FOREIGN KEY (gateway_id) REFERENCES gateway (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12674BBB4 FOREIGN KEY (moved_wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE gateway ADD CONSTRAINT FK_14FEDD7F38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921F38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE region_currency ADD CONSTRAINT FK_1A918CCD98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE region_currency ADD CONSTRAINT FK_1A918CCD38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE credit_card ADD CONSTRAINT FK_11D627EE11C8FB41 FOREIGN KEY (bank_id) REFERENCES bank (id)');
        $this->addSql('ALTER TABLE credit_card ADD CONSTRAINT FK_11D627EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD238248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD238248176 ON service (currency_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD238248176');
        $this->addSql('ALTER TABLE currency_exchange_log DROP FOREIGN KEY FK_9335771F38248176');
        $this->addSql('ALTER TABLE bank DROP FOREIGN KEY FK_D860BF7A38248176');
        $this->addSql('ALTER TABLE region_base_price DROP FOREIGN KEY FK_560487EA38248176');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D138248176');
        $this->addSql('ALTER TABLE gateway DROP FOREIGN KEY FK_14FEDD7F38248176');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921F38248176');
        $this->addSql('ALTER TABLE region_currency DROP FOREIGN KEY FK_1A918CCD38248176');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1EA675D86');
        $this->addSql('ALTER TABLE credit_card DROP FOREIGN KEY FK_11D627EE11C8FB41');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1577F8E00');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1712520F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D12674BBB4');
        $this->addSql('ALTER TABLE region_base_price DROP FOREIGN KEY FK_560487EA98260155');
        $this->addSql('ALTER TABLE region_currency DROP FOREIGN KEY FK_1A918CCD98260155');
        $this->addSql('DROP TABLE favorite_route');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE currency_exchange_log');
        $this->addSql('DROP TABLE bank');
        $this->addSql('DROP TABLE region_base_price');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE gateway');
        $this->addSql('DROP TABLE wallet');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE region_currency');
        $this->addSql('DROP TABLE credit_card');
        $this->addSql('DROP INDEX IDX_E19D9AD238248176 ON service');
        $this->addSql('ALTER TABLE service DROP currency_id');
    }
}
