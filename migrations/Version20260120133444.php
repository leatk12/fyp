<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120133444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE independent_age_action CHANGE uuid uuid VARCHAR(36) NOT NULL, CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE independent_age_action RENAME INDEX uniq_3b2c4d2ed17f50a6 TO UNIQ_D2E66349D17F50A6');
        $this->addSql('ALTER TABLE independent_age_action RENAME INDEX idx_independent_age_action_record_id TO IDX_D2E663494DFD750C');
        $this->addSql('ALTER TABLE independent_age_record CHANGE uuid uuid VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE independent_age_record RENAME INDEX uniq_4c6f9b68d17f50a6 TO UNIQ_E1E704AD17F50A6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE independent_age_action CHANGE uuid uuid CHAR(36) NOT NULL, CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE independent_age_action RENAME INDEX uniq_d2e66349d17f50a6 TO UNIQ_3B2C4D2ED17F50A6');
        $this->addSql('ALTER TABLE independent_age_action RENAME INDEX idx_d2e663494dfd750c TO IDX_independent_age_action_record_id');
        $this->addSql('ALTER TABLE independent_age_record CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE independent_age_record RENAME INDEX uniq_e1e704ad17f50a6 TO UNIQ_4C6F9B68D17F50A6');
    }
}
