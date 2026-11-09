<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add appointment performer name to Hub actions.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !($this->connection->getDatabasePlatform()
                instanceof AbstractMySQLPlatform),
            'This migration is intended for MySQL or MariaDB.'
        );

        $this->abortIf(
            !$schema->hasTable('hub_action'),
            'The hub_action table does not exist.'
        );

        $this->abortIf(
            $schema->getTable('hub_action')->hasColumn('performed_by'),
            'The performed_by column already exists. Review migration status.'
        );

        $this->addSql(
            'ALTER TABLE hub_action '
            . 'ADD performed_by VARCHAR(255) DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Automatic rollback is disabled to protect recorded performer names.'
        );
    }
}