<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160826011417 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, device_id INT DEFAULT NULL, service_id INT DEFAULT NULL, gcm_id VARCHAR(255) DEFAULT NULL, multicast_id VARCHAR(255) DEFAULT NULL, status SMALLINT NOT NULL, error VARCHAR(255) DEFAULT NULL, collapse_key VARCHAR(255) DEFAULT NULL, priority VARCHAR(6) NOT NULL, content_available TINYINT(1) DEFAULT \'0\' NOT NULL, delay_while_idle TINYINT(1) DEFAULT \'0\' NOT NULL, time_to_live INT NOT NULL, restricted_package_name VARCHAR(255) DEFAULT NULL, dry_run TINYINT(1) DEFAULT \'0\' NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, sound VARCHAR(255) DEFAULT NULL, badge VARCHAR(255) DEFAULT NULL, tag VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, click_action VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, expiry INT NOT NULL, type SMALLINT NOT NULL, is_downloaded TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_B6BD307F94A4C7D4 (device_id), INDEX IDX_B6BD307FED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, is_expired TINYINT(1) NOT NULL, INDEX IDX_5F37A13BA76ED395 (user_id), UNIQUE INDEX UNIQUE_token_per_user (token, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorite_route (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, deleted_by INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, name VARCHAR(50) DEFAULT NULL, age SMALLINT DEFAULT NULL, sex VARCHAR(1) DEFAULT NULL, description LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, wrong_password_count SMALLINT DEFAULT 0 NOT NULL, is_multi_device_allowed TINYINT(1) DEFAULT \'1\' NOT NULL, api_key VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, disc SMALLINT NOT NULL, UNIQUE INDEX UNIQ_8D93D649C912ED9D (api_key), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), INDEX IDX_8D93D649DE12AB56 (created_by), INDEX IDX_8D93D6491F6FA0AF (deleted_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, device_token LONGTEXT NOT NULL, device_identifier VARCHAR(50) NOT NULL, is_sound_allowed TINYINT(1) NOT NULL, is_alert_allowed TINYINT(1) NOT NULL, device_name VARCHAR(255) NOT NULL, os VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, device_model VARCHAR(100) NOT NULL, device_version VARCHAR(30) NOT NULL, app_name VARCHAR(50) NOT NULL, app_version VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, api_key VARCHAR(255) NOT NULL, last_login_at DATETIME DEFAULT NULL, play_service_version VARCHAR(100) DEFAULT NULL, device_date_time VARCHAR(255) DEFAULT NULL, device_timezone VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_92FB68EC912ED9D (api_key), INDEX IDX_92FB68E7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE passenger (id INT NOT NULL, referer_id INT DEFAULT NULL, mobile VARCHAR(11) NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, wrong_token_count SMALLINT NOT NULL, UNIQUE INDEX UNIQ_3BEFE8DD3C7323E0 (mobile), INDEX IDX_3BEFE8DD87C61384 (referer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car_log (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, at_time DATETIME NOT NULL, status SMALLINT NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', INDEX IDX_84984875C3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, plaque_id INT NOT NULL, deleted_by INT DEFAULT NULL, created_by INT DEFAULT NULL, brand VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, color VARCHAR(15) NOT NULL, born DATE NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, description LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, is_current TINYINT(1) DEFAULT \'0\' NOT NULL, status SMALLINT NOT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_773DE69DC3423909 (driver_id), UNIQUE INDEX UNIQ_773DE69DD89B8F16 (plaque_id), INDEX IDX_773DE69D1F6FA0AF (deleted_by), INDEX IDX_773DE69DDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car_route (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, route LINESTRING NOT NULL COMMENT \'(DC2Type:linestring)\', created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, is_finished TINYINT(1) NOT NULL, INDEX IDX_DD6FA5AEC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE driver (id INT NOT NULL, address_id INT NOT NULL, agency_id INT NOT NULL, mobile VARCHAR(11) NOT NULL, contract_number VARCHAR(20) NOT NULL, contact LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', national_code VARCHAR(255) NOT NULL, rate NUMERIC(2, 1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_11667CD93C7323E0 (mobile), UNIQUE INDEX UNIQ_11667CD9AAD0FA19 (contract_number), UNIQUE INDEX UNIQ_11667CD9D3C17DD2 (national_code), UNIQUE INDEX UNIQ_11667CD9F5B7AF75 (address_id), INDEX IDX_11667CD9CDEADB2A (agency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plaque (id INT AUTO_INCREMENT NOT NULL, first_number SMALLINT NOT NULL, second_number SMALLINT NOT NULL, city_number SMALLINT NOT NULL, area_code VARCHAR(2) NOT NULL, UNIQUE INDEX plaque_UNIQUE (first_number, second_number, city_number, area_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agent (id INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, address_id INT NOT NULL, name VARCHAR(50) NOT NULL, contacts LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', description LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, disc SMALLINT NOT NULL, INDEX IDX_268B9C9D642B8210 (admin_id), UNIQUE INDEX UNIQ_268B9C9DF5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agency (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, city INT NOT NULL, title VARCHAR(255) NOT NULL, point POINT NOT NULL COMMENT \'(DC2Type:point)\', postal_code VARCHAR(10) DEFAULT NULL, address LONGTEXT NOT NULL, INDEX IDX_D4E6F812D5B0234 (city), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ip_location (id INT AUTO_INCREMENT NOT NULL, reporter INT DEFAULT NULL, location POINT NOT NULL COMMENT \'(DC2Type:point)\', real_ip VARCHAR(50) NOT NULL, ip VARCHAR(50) NOT NULL, INDEX IDX_200044CC76D6E0F2 (reporter), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, parent INT DEFAULT NULL, name VARCHAR(255) NOT NULL, point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', lft INT NOT NULL, rgt INT NOT NULL, lvl INT NOT NULL, INDEX IDX_2D5B02343D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, passenger_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, car_id INT DEFAULT NULL, canceled_by INT DEFAULT NULL, canceled_reason INT DEFAULT NULL, currency_id INT DEFAULT NULL, start_point POINT NOT NULL COMMENT \'(DC2Type:point)\', start_address VARCHAR(500) DEFAULT \'???\' NOT NULL, end_point POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', end_address VARCHAR(500) DEFAULT \'???\' NOT NULL, type SMALLINT NOT NULL, desire SMALLINT NOT NULL, description LONGTEXT DEFAULT NULL, propagation_type SMALLINT NOT NULL, driver_rate NUMERIC(1, 0) DEFAULT NULL, passenger_rate NUMERIC(1, 0) DEFAULT NULL, route LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', distance INT DEFAULT NULL, price INT DEFAULT NULL, real_price INT DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, canceled_at DATETIME DEFAULT NULL, status SMALLINT NOT NULL, INDEX IDX_E19D9AD24502E565 (passenger_id), INDEX IDX_E19D9AD23414710B (agent_id), INDEX IDX_E19D9AD2C3C6F69F (car_id), INDEX IDX_E19D9AD2C8D4ECF (canceled_by), INDEX IDX_E19D9AD2C9712EEB (canceled_reason), INDEX IDX_E19D9AD238248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE floating_cost (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, cost INT NOT NULL, description VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5E815DD1ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wakeful (id INT AUTO_INCREMENT NOT NULL, car_id INT NOT NULL, at_time DATETIME NOT NULL, point POINT NOT NULL COMMENT \'(DC2Type:point)\', UNIQUE INDEX UNIQ_CF07BB1BC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE propagation_list (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, driver_id INT NOT NULL, number SMALLINT NOT NULL, answer SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FBA31CC9ED5CA9E6 (service_id), INDEX IDX_FBA31CC9C3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canceled_reason (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_log (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, status SMALLINT NOT NULL, at_time DATETIME NOT NULL, INDEX IDX_AD6F1BB2ED5CA9E6 (service_id), UNIQUE INDEX service_log_UNIQUE (status, service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(3) NOT NULL, is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6956883F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency_exchange_log (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, exchange NUMERIC(14, 5) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9335771F38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bank (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_enable TINYINT(1) NOT NULL, INDEX IDX_D860BF7A38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_base_price (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, region_id INT DEFAULT NULL, price INT NOT NULL, INDEX IDX_560487EA38248176 (currency_id), INDEX IDX_560487EA98260155 (region_id), UNIQUE INDEX currency_in_region_UNIQUE (currency_id, region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, service_id INT DEFAULT NULL, currency_id INT NOT NULL, log_id INT DEFAULT NULL, wallet_id INT DEFAULT NULL, gateway_id INT DEFAULT NULL, moved_wallet_id INT DEFAULT NULL, amount INT NOT NULL, is_virtual TINYINT(1) DEFAULT \'0\' NOT NULL, direction SMALLINT NOT NULL, type SMALLINT NOT NULL, status SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D1ED5CA9E6 (service_id), INDEX IDX_723705D138248176 (currency_id), INDEX IDX_723705D1EA675D86 (log_id), INDEX IDX_723705D1712520F3 (wallet_id), INDEX IDX_723705D1577F8E00 (gateway_id), INDEX IDX_723705D12674BBB4 (moved_wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gateway (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, config LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', is_enabled TINYINT(1) NOT NULL, is_default TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_14FEDD7F5E237E06 (name), INDEX IDX_14FEDD7F38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, balance NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, INDEX IDX_7C68921F38248176 (currency_id), INDEX IDX_7C68921F7E3C61F9 (owner_id), UNIQUE INDEX user_wallet_per_currency_UNIQUE (currency_id, owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, region POLYGON NOT NULL COMMENT \'(DC2Type:polygon)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_currency (region_id INT NOT NULL, currency_id INT NOT NULL, INDEX IDX_1A918CCD98260155 (region_id), INDEX IDX_1A918CCD38248176 (currency_id), PRIMARY KEY(region_id, currency_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE credit_card (id INT AUTO_INCREMENT NOT NULL, bank_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, number VARCHAR(30) NOT NULL, expire_at DATE NOT NULL, INDEX IDX_11D627EE11C8FB41 (bank_id), INDEX IDX_11D627EE7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, priority SMALLINT NOT NULL, type SMALLINT NOT NULL, status SMALLINT DEFAULT NULL, rtl INT NOT NULL, lft INT NOT NULL, root INT DEFAULT NULL, lvl INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_97A0ADA3A76ED395 (user_id), INDEX IDX_97A0ADA3727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491F6FA0AF FOREIGN KEY (deleted_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD87C61384 FOREIGN KEY (referer_id) REFERENCES passenger (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DDBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car_log ADD CONSTRAINT FK_84984875C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DD89B8F16 FOREIGN KEY (plaque_id) REFERENCES plaque (id)');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D1F6FA0AF FOREIGN KEY (deleted_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE car_route ADD CONSTRAINT FK_DD6FA5AEC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9CDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9DF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE agency ADD CONSTRAINT FK_70C0C6E6BF396750 FOREIGN KEY (id) REFERENCES agent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F812D5B0234 FOREIGN KEY (city) REFERENCES city (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE ip_location ADD CONSTRAINT FK_200044CC76D6E0F2 FOREIGN KEY (reporter) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02343D8E604F FOREIGN KEY (parent) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD24502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD23414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C8D4ECF FOREIGN KEY (canceled_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2C9712EEB FOREIGN KEY (canceled_reason) REFERENCES canceled_reason (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD238248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE floating_cost ADD CONSTRAINT FK_5E815DD1ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wakeful ADD CONSTRAINT FK_CF07BB1BC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE propagation_list ADD CONSTRAINT FK_FBA31CC9C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE service_log ADD CONSTRAINT FK_AD6F1BB2ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
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

        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DE12AB56');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491F6FA0AF');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E7E3C61F9');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DDBF396750');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D1F6FA0AF');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DDE12AB56');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9BF396750');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D642B8210');
        $this->addSql('ALTER TABLE ip_location DROP FOREIGN KEY FK_200044CC76D6E0F2');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2C8D4ECF');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921F7E3C61F9');
        $this->addSql('ALTER TABLE credit_card DROP FOREIGN KEY FK_11D627EE7E3C61F9');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F94A4C7D4');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD87C61384');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD24502E565');
        $this->addSql('ALTER TABLE car_log DROP FOREIGN KEY FK_84984875C3C6F69F');
        $this->addSql('ALTER TABLE car_route DROP FOREIGN KEY FK_DD6FA5AEC3C6F69F');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2C3C6F69F');
        $this->addSql('ALTER TABLE wakeful DROP FOREIGN KEY FK_CF07BB1BC3C6F69F');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DC3423909');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9C3423909');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DD89B8F16');
        $this->addSql('ALTER TABLE agency DROP FOREIGN KEY FK_70C0C6E6BF396750');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD23414710B');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9CDEADB2A');
        $this->addSql('ALTER TABLE driver DROP FOREIGN KEY FK_11667CD9F5B7AF75');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9DF5B7AF75');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F812D5B0234');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02343D8E604F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FED5CA9E6');
        $this->addSql('ALTER TABLE floating_cost DROP FOREIGN KEY FK_5E815DD1ED5CA9E6');
        $this->addSql('ALTER TABLE propagation_list DROP FOREIGN KEY FK_FBA31CC9ED5CA9E6');
        $this->addSql('ALTER TABLE service_log DROP FOREIGN KEY FK_AD6F1BB2ED5CA9E6');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1ED5CA9E6');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2C9712EEB');
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
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3727ACA70');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE favorite_route');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE passenger');
        $this->addSql('DROP TABLE car_log');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE car_route');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE plaque');
        $this->addSql('DROP TABLE agent');
        $this->addSql('DROP TABLE agency');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE ip_location');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE floating_cost');
        $this->addSql('DROP TABLE wakeful');
        $this->addSql('DROP TABLE propagation_list');
        $this->addSql('DROP TABLE canceled_reason');
        $this->addSql('DROP TABLE service_log');
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
        $this->addSql('DROP TABLE ticket');
    }
}
