<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260120115827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uuid to independent_age_record and create independent_age_action linked via record_uuid -> record.uuid';
    }

    public function up(Schema $schema): void
    {
        // Add uuid column if missing
        $this->addSql("
        SET @col_exists := (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'independent_age_record'
              AND COLUMN_NAME = 'uuid'
        );
    ");

        $this->addSql("SET @sql := IF(@col_exists = 0, 'ALTER TABLE independent_age_record ADD uuid CHAR(36) DEFAULT NULL', 'SELECT 1');");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        // Backfill UUIDs if any NULL
        $this->addSql("UPDATE independent_age_record SET uuid = UUID() WHERE uuid IS NULL");

        // Make NOT NULL if currently nullable
        $this->addSql("
        SET @is_nullable := (
            SELECT IS_NULLABLE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'independent_age_record'
              AND COLUMN_NAME = 'uuid'
            LIMIT 1
        );
    ");
        $this->addSql("SET @sql := IF(@is_nullable = 'YES', 'ALTER TABLE independent_age_record MODIFY uuid CHAR(36) NOT NULL', 'SELECT 1');");
        $this->addSql("PREPARE stmt2 FROM @sql; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;");

        // Add unique index if missing
        $this->addSql("
        SET @idx_exists := (
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'independent_age_record'
              AND INDEX_NAME = 'UNIQ_4C6F9B68D17F50A6'
        );
    ");
        $this->addSql("SET @sql := IF(@idx_exists = 0, 'CREATE UNIQUE INDEX UNIQ_4C6F9B68D17F50A6 ON independent_age_record (uuid)', 'SELECT 1');");
        $this->addSql("PREPARE stmt3 FROM @sql; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;");

        // Create independent_age_action (safe now because we dropped it)
        $this->addSql("
        CREATE TABLE independent_age_action (
            id INT AUTO_INCREMENT NOT NULL,
            uuid CHAR(36) NOT NULL,
            record_uuid CHAR(36) NOT NULL,
            date DATE NOT NULL,
            action LONGTEXT NOT NULL,
            outcome NUMERIC(10, 2) DEFAULT NULL,
            UNIQUE INDEX UNIQ_3B2C4D2ED17F50A6 (uuid),
            INDEX IDX_3B2C4D2E34C8C172 (record_uuid),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
    ");

        $this->addSql("
        ALTER TABLE independent_age_action
        ADD CONSTRAINT FK_3B2C4D2E34C8C172
        FOREIGN KEY (record_uuid) REFERENCES independent_age_record (uuid)
        ON DELETE CASCADE
    ");
    }


    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE independent_age_action DROP FOREIGN KEY FK_3B2C4D2E34C8C172");
        $this->addSql("DROP TABLE independent_age_action");
        $this->addSql("DROP INDEX UNIQ_4C6F9B68D17F50A6 ON independent_age_record");
        $this->addSql("ALTER TABLE independent_age_record DROP uuid");
    }
}
