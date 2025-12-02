<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subcontractors table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE subcontractors (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            abn VARCHAR(11) NOT NULL,
            current_step INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subcontractors');
    }
}
