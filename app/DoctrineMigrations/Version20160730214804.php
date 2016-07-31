<?php

namespace Itaxi\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160730214804 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAF3414710B');
        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAF4502E565');
        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAFC3C6F69F');
        $this->addSql('ALTER TABLE requested CHANGE driver_rate driver_rate NUMERIC(1, 0) DEFAULT NULL, CHANGE passenger_rate passenger_rate NUMERIC(1, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF4502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAFC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE RESTRICT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAF4502E565');
        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAF3414710B');
        $this->addSql('ALTER TABLE requested DROP FOREIGN KEY FK_98521BAFC3C6F69F');
        $this->addSql('ALTER TABLE requested CHANGE driver_rate driver_rate NUMERIC(2, 1) DEFAULT NULL, CHANGE passenger_rate passenger_rate NUMERIC(2, 1) DEFAULT NULL');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF4502E565 FOREIGN KEY (passenger_id) REFERENCES passenger (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAF3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE requested ADD CONSTRAINT FK_98521BAFC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
    }
}
