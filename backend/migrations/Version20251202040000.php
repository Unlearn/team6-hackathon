<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202040000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trades field to subcontractors table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors ADD trades JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors DROP trades');
    }
}
