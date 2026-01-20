<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005114750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE independent_age_record (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, full_name VARCHAR(255) NOT NULL, age INT NOT NULL, gender VARCHAR(20) DEFAULT NULL, heard_about VARCHAR(40) DEFAULT NULL, street_address VARCHAR(255) DEFAULT NULL, ward VARCHAR(120) DEFAULT NULL, postcode VARCHAR(12) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, additional_needs LONGTEXT DEFAULT NULL, benefits JSON DEFAULT NULL, immigration_status VARCHAR(40) DEFAULT NULL, status_describe LONGTEXT DEFAULT NULL, is_english_first_language TINYINT(1) DEFAULT NULL, first_language VARCHAR(120) DEFAULT NULL, housing VARCHAR(40) DEFAULT NULL, housing_other VARCHAR(255) DEFAULT NULL, permissions JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE independent_age_record');
    }
}
