<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160128004847 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE email_canonical email_canonical VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD87C61384');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD3C7323E0 ON passenger');
        $this->addSql('ALTER TABLE passenger ADD mobile_canonical VARCHAR(11) DEFAULT NULL, CHANGE mobile mobile VARCHAR(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD87C61384 FOREIGN KEY (referer_id) REFERENCES passenger (id) ON DELETE RESTRICT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD23CCBBCB ON passenger (mobile_canonical)');
        $this->addSql('ALTER TABLE driver CHANGE address_id address_id INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver CHANGE address_id address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD87C61384');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD23CCBBCB ON passenger');
        $this->addSql('ALTER TABLE passenger DROP mobile_canonical, CHANGE mobile mobile VARCHAR(11) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD87C61384 FOREIGN KEY (referer_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD3C7323E0 ON passenger (mobile)');
        $this->addSql('ALTER TABLE user CHANGE email_canonical email_canonical VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
