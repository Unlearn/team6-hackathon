<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202060000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add documents column to subcontractors table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors ADD documents JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors DROP documents');
    }
}
