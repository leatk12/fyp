<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302182541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenue ADD booking_customer_id INT DEFAULT NULL, CHANGE customer_id customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE revenue ADD CONSTRAINT FK_E9116C85B40E183A FOREIGN KEY (booking_customer_id) REFERENCES booking_customer (id)');
        $this->addSql('ALTER TABLE revenue ADD CONSTRAINT FK_E9116C859395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_E9116C85B40E183A ON revenue (booking_customer_id)');
        $this->addSql('CREATE INDEX IDX_E9116C859395C3F3 ON revenue (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revenue DROP FOREIGN KEY FK_E9116C85B40E183A');
        $this->addSql('ALTER TABLE revenue DROP FOREIGN KEY FK_E9116C859395C3F3');
        $this->addSql('DROP INDEX IDX_E9116C85B40E183A ON revenue');
        $this->addSql('DROP INDEX IDX_E9116C859395C3F3 ON revenue');
        $this->addSql('ALTER TABLE revenue DROP booking_customer_id, CHANGE customer_id customer_id INT NOT NULL');
    }
}
