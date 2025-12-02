<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202043000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug column to subcontractors table';
    }

    public function up(Schema $schema): void
    {
        // Add slug column with NOT NULL constraint
        $this->addSql('ALTER TABLE subcontractors ADD COLUMN slug VARCHAR(255) NOT NULL DEFAULT ""');
        
        // Generate slugs for existing records
        $this->addSql('UPDATE subcontractors SET slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, " ", "-"), "&", "and"), ".", ""), ",", ""), "  ", "-"))');
        
        // Create unique index on slug
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CCDFF28_SLUG ON subcontractors (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8CCDFF28_SLUG');
        $this->addSql('ALTER TABLE subcontractors DROP COLUMN slug');
    }
}
