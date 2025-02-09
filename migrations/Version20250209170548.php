<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209170548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE2C414CE8');
        $this->addSql('DROP INDEX IDX_E00CEDDE2C414CE8 ON booking');
        $this->addSql('ALTER TABLE booking DROP recurrence_id');
        $this->addSql('ALTER TABLE recurrence ADD recurring VARCHAR(255) NOT NULL, DROP type, DROP count');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD recurrence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE2C414CE8 FOREIGN KEY (recurrence_id) REFERENCES recurrence (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E00CEDDE2C414CE8 ON booking (recurrence_id)');
        $this->addSql('ALTER TABLE recurrence ADD type VARCHAR(50) NOT NULL, ADD count INT DEFAULT NULL, DROP recurring');
    }
}
