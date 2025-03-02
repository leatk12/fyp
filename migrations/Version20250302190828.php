<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302190828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE revenue ADD transaction_id INT DEFAULT NULL');
        //$this->addSql('ALTER TABLE revenue ADD CONSTRAINT FK_E9116C852FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        //$this->addSql('CREATE INDEX IDX_E9116C852FC0CB0F ON revenue (transaction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenue DROP FOREIGN KEY FK_E9116C852FC0CB0F');
        $this->addSql('DROP INDEX IDX_E9116C852FC0CB0F ON revenue');
        $this->addSql('ALTER TABLE revenue DROP transaction_id');
    }
}
