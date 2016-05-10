<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160510103210 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message ADD gcm_id VARCHAR(255) DEFAULT NULL, ADD multicast_id INT DEFAULT NULL, ADD status SMALLINT NOT NULL, ADD error VARCHAR(255) DEFAULT NULL, ADD collapse_key VARCHAR(255) DEFAULT NULL, ADD priority VARCHAR(6) NOT NULL, ADD content_available TINYINT(1) DEFAULT \'0\' NOT NULL, ADD delay_while_idle TINYINT(1) DEFAULT \'0\' NOT NULL, ADD time_to_live INT NOT NULL, ADD restricted_package_name VARCHAR(255) DEFAULT NULL, ADD dry_run TINYINT(1) DEFAULT \'0\' NOT NULL, ADD data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD title VARCHAR(255) DEFAULT NULL, ADD body LONGTEXT DEFAULT NULL, ADD icon VARCHAR(255) DEFAULT NULL, ADD sound VARCHAR(255) DEFAULT NULL, ADD badge VARCHAR(255) DEFAULT NULL, ADD tag VARCHAR(255) DEFAULT NULL, ADD color VARCHAR(255) DEFAULT NULL, ADD click_action VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD expiry INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92FB68EC912ED9D ON device (api_key)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_92FB68EC912ED9D ON device');
        $this->addSql('ALTER TABLE message DROP gcm_id, DROP multicast_id, DROP status, DROP error, DROP collapse_key, DROP priority, DROP content_available, DROP delay_while_idle, DROP time_to_live, DROP restricted_package_name, DROP dry_run, DROP data, DROP title, DROP body, DROP icon, DROP sound, DROP badge, DROP tag, DROP color, DROP click_action, DROP created_at, DROP updated_at, DROP expiry');
    }
}
