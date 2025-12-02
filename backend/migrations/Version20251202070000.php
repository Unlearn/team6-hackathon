<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create employees table, add main_contact_employee_id to subcontractors, and remove employees JSON column';
    }

    public function up(Schema $schema): void
    {
        // Create employees table
        $this->addSql('CREATE TABLE employees (
            id SERIAL NOT NULL,
            subcontractor_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            job_title VARCHAR(255) DEFAULT NULL,
            mobile VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            profile_picture VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            PRIMARY KEY(id)
        )');

        // Create index
        $this->addSql('CREATE INDEX IDX_BA82C3008CCDFF28 ON employees (subcontractor_id)');

        // Add foreign key constraint
        $this->addSql('ALTER TABLE employees ADD CONSTRAINT FK_BA82C3008CCDFF28 
            FOREIGN KEY (subcontractor_id) REFERENCES subcontractors (id) ON DELETE CASCADE');

        // Add main_contact_employee_id to subcontractors
        $this->addSql('ALTER TABLE subcontractors ADD COLUMN main_contact_employee_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_8CCDFF28_MAIN_CONTACT ON subcontractors (main_contact_employee_id)');
        $this->addSql('ALTER TABLE subcontractors ADD CONSTRAINT FK_8CCDFF28_MAIN_CONTACT 
            FOREIGN KEY (main_contact_employee_id) REFERENCES employees (id) ON DELETE SET NULL');

        // Remove employees JSON column from subcontractors
        $this->addSql('ALTER TABLE subcontractors DROP COLUMN employees');
    }

    public function down(Schema $schema): void
    {
        // Remove main_contact_employee_id from subcontractors
        $this->addSql('ALTER TABLE subcontractors DROP CONSTRAINT FK_8CCDFF28_MAIN_CONTACT');
        $this->addSql('ALTER TABLE subcontractors DROP COLUMN main_contact_employee_id');

        // Drop employees table
        $this->addSql('DROP TABLE employees');

        // Add back employees JSON column to subcontractors
        $this->addSql('ALTER TABLE subcontractors ADD employees JSON DEFAULT NULL');
    }
}
