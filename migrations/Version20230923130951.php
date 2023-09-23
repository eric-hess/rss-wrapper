<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230923130951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD is_verified BOOLEAN NOT NULL DEFAULT false;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP COLUMN is_verified;');
    }
}
