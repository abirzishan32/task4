<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_seen_at column to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD last_seen_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        // Update existing records to have a value
        $this->addSql('UPDATE user SET last_seen_at = last_login_at WHERE last_seen_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP last_seen_at');
    }
} 