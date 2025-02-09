<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209190311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, balance DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, organisation VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expense (id INT AUTO_INCREMENT NOT NULL, supplier_id_id INT NOT NULL, account_id_id INT DEFAULT NULL, date_incurred DATETIME NOT NULL, description VARCHAR(255) DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2D3A8DA6A65F9C7D (supplier_id_id), INDEX IDX_2D3A8DA649CB4726 (account_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, invoice_number VARCHAR(50) NOT NULL, date_issued DATETIME NOT NULL, due_date DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, total DOUBLE PRECISION DEFAULT NULL, INDEX IDX_90651744B171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, invoice_id_id INT NOT NULL, description VARCHAR(255) DEFAULT NULL, quantity DOUBLE PRECISION DEFAULT NULL, unit_price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_D3D1D693429ECEE2 (invoice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, organisation VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, account_id_id INT NOT NULL, date DATETIME NOT NULL, description VARCHAR(255) DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, INDEX IDX_723705D149CB4726 (account_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6A65F9C7D FOREIGN KEY (supplier_id_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA649CB4726 FOREIGN KEY (account_id_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D693429ECEE2 FOREIGN KEY (invoice_id_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D149CB4726 FOREIGN KEY (account_id_id) REFERENCES account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6A65F9C7D');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA649CB4726');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744B171EB6C');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D693429ECEE2');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D149CB4726');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE transaction');
    }
}
