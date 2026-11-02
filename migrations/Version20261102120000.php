<?php


declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add separate Hub Monitoring record and action tables only.';
    }

    public function isTransactional(): bool
    {
        // MySQL/MariaDB table creation is not transactionally reversible.
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
            $schema->hasTable('hub_record')
            || $schema->hasTable('hub_action'),
            'A Hub table already exists. Review its schema before proceeding.'
        );

        $record = $schema->createTable('hub_record');

        $record->addColumn('id', Types::INTEGER, [
            'autoincrement' => true,
        ]);

        $record->addColumn('uuid', Types::STRING, [
            'length' => 36,
        ]);

        $record->addColumn('date', Types::DATE_IMMUTABLE);

        $record->addColumn('full_name', Types::STRING, [
            'length' => 255,
        ]);

        $record->addColumn('age', Types::INTEGER, [
            'notnull' => false,
        ]);

        $nullableStrings = [
            'gender' => 30,
            'street_address' => 255,
            'town' => 120,
            'postcode' => 12,
            'phone' => 30,
            'email' => 255,
            'heard_about_other' => 255,
            'benefits_other' => 255,
            'first_language' => 120,
            'other_landlord' => 255,
            'housing_other' => 255,
            'great_places_signature' => 255,
            'sharing_signature' => 255,
            'image_signature' => 255,
            'e_safety_signature' => 255,
        ];

        foreach ($nullableStrings as $name => $length) {
            $record->addColumn($name, Types::STRING, [
                'length' => $length,
                'notnull' => false,
            ]);
        }

        foreach ([
                     'support_other',
                     'additional_needs',
                     'status_describe',
                 ] as $name) {
            $record->addColumn($name, Types::TEXT, [
                'notnull' => false,
            ]);
        }

        foreach ([
                     'heard_about',
                     'support_needs',
                     'employment',
                     'benefits',
                     'immigration_statuses',
                     'housing',
                     'permissions',
                     'header_checks',
                 ] as $name) {
            $record->addColumn($name, Types::JSON);
        }

        foreach ([
                     'looking_for_work',
                     'english_first_language',
                     'caring_responsibilities',
                     'great_places_consent',
                     'do_not_contact',
                     'sharing_consent',
                     'image_consent',
                     'e_safety_agreement',
                 ] as $name) {
            $record->addColumn($name, Types::BOOLEAN, [
                'notnull' => false,
            ]);
        }

        $record->setPrimaryKey(['id']);
        $record->addUniqueIndex(['uuid']);

        $action = $schema->createTable('hub_action');

        $action->addColumn('id', Types::INTEGER, [
            'autoincrement' => true,
        ]);

        $action->addColumn('uuid', Types::STRING, [
            'length' => 36,
        ]);

        $action->addColumn('record_id', Types::INTEGER);
        $action->addColumn('date', Types::DATE_IMMUTABLE);

        $action->addColumn('action_location', Types::STRING, [
            'length' => 60,
            'notnull' => false,
        ]);

        $action->addColumn('action_location_other', Types::STRING, [
            'length' => 255,
            'notnull' => false,
        ]);

        $action->addColumn('action', Types::TEXT);

        $action->addColumn('achievements', Types::TEXT, [
            'notnull' => false,
        ]);

        $action->addColumn('outcome', Types::DECIMAL, [
            'precision' => 10,
            'scale' => 2,
            'notnull' => false,
        ]);

        $action->setPrimaryKey(['id']);
        $action->addUniqueIndex(['uuid']);
        $action->addIndex(['record_id']);

        $action->addForeignKeyConstraint(
            'hub_record',
            ['record_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        foreach ([$record, $action] as $table) {
            $table->addOption('engine', 'InnoDB');
            $table->addOption('charset', 'utf8mb4');
            $table->addOption('collation', 'utf8mb4_unicode_ci');
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Automatic rollback is disabled to protect Hub records. '
            . 'Review and back up Hub data before any manual removal.'
        );
    }
}