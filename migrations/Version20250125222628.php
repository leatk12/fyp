<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250125222628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD recurring_id INT DEFAULT NULL, DROP recurring');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEB149C95E FOREIGN KEY (recurring_id) REFERENCES recurrence (id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEB149C95E ON booking (recurring_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEB149C95E');
        $this->addSql('DROP INDEX IDX_E00CEDDEB149C95E ON booking');
        $this->addSql('ALTER TABLE booking ADD recurring VARCHAR(10) DEFAULT NULL, DROP recurring_id');
    }
}
