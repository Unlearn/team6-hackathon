<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add mobile and email fields to subcontractors table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors ADD mobile VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE subcontractors ADD email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subcontractors DROP mobile');
        $this->addSql('ALTER TABLE subcontractors DROP email');
    }
}
