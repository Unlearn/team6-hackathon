<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create complete database schema for SQLite including subcontractors, employees, trades, and junction table';
    }

    public function up(Schema $schema): void
    {
        // Create subcontractors table
        $this->addSql('CREATE TABLE subcontractors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            abn VARCHAR(11) NOT NULL,
            slug VARCHAR(255) NOT NULL DEFAULT "",
            mobile VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            logo VARCHAR(255) DEFAULT NULL,
            documents TEXT DEFAULT NULL,
            current_step INT NOT NULL DEFAULT 1,
            main_contact_employee_id INT DEFAULT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            FOREIGN KEY (main_contact_employee_id) REFERENCES employees (id) ON DELETE SET NULL
        )');
        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CCDFF28_SLUG ON subcontractors (slug)');
        $this->addSql('CREATE INDEX IDX_8CCDFF28_MAIN_CONTACT ON subcontractors (main_contact_employee_id)');

        // Create employees table
        $this->addSql('CREATE TABLE employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            subcontractor_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            job_title VARCHAR(255) DEFAULT NULL,
            mobile VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            profile_picture VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            FOREIGN KEY (subcontractor_id) REFERENCES subcontractors (id) ON DELETE CASCADE
        )');
        
        $this->addSql('CREATE INDEX IDX_BA82C3008CCDFF28 ON employees (subcontractor_id)');

        // Create trades table
        $this->addSql('CREATE TABLE trades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL
        )');

        // Create junction table for many-to-many relationship
        $this->addSql('CREATE TABLE subcontractor_trade (
            subcontractor_id INT NOT NULL,
            trade_id INT NOT NULL,
            PRIMARY KEY (subcontractor_id, trade_id),
            FOREIGN KEY (subcontractor_id) REFERENCES subcontractors(id) ON DELETE CASCADE,
            FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE
        )');
        
        $this->addSql('CREATE INDEX idx_subcontractor_trade_subcontractor ON subcontractor_trade(subcontractor_id)');
        $this->addSql('CREATE INDEX idx_subcontractor_trade_trade ON subcontractor_trade(trade_id)');

        // Seed trades
        foreach ([
            'Building Envelope',
            'Roofing',
            'Waterproofing',
            'Cladding',
            'Glazing',
            'Insulation',
            'MEP Systems',
            'Plumbing',
            'HVAC',
            'Electrical',
            'Fire sprinklers',
            'Drywall',
            'Painting',
            'Flooring',
            'Ceilings',
            'Millwork',
            'Fire alarms',
            'Security systems',
            'Low-voltage',
            'Elevators',
            'Excavation',
            'Paving',
            'Landscaping',
            'Site utilities',
            'Rebar installers',
            'Post-tension',
            'Precast concrete',
            'Tilt-up panels',
            'Shotcrete',
            'Epoxy injection',
            'Structural welding',
            'Metal studs',
            'Heavy timber',
            'Glulam beams',
            'General contractors',
            'Concrete',
            'Steel erectors',
            'Masonry',
            'Carpenters',
            'Stucco',
            'EIFS systems',
            'Stone veneer',
            'Brick pointing',
            'Expansion joints',
            'Sealants',
            'Louvers',
            'Canopies',
            'Metal fascia',
            'Soffit installers',
            'Sheet metal',
            'Roof hatches',
            'Skylights',
            'Green roofs',
            'Solar panels',
            'Lightning protection',
            'Roof walkways',
            'Gutter systems',
            'Flashing',
            'Overhead doors',
            'Roll-up doors',
            'Loading docks',
            'Door hardware',
            'Automatic doors',
            'Revolving doors',
            'Storefront systems',
            'Blast doors',
            'Vault doors',
            'Demountable partitions',
            'Operable walls',
            'Glass partitions',
            'Metal partitions',
            'Toilet partitions',
            'Shower enclosures',
            'Terrazzo',
            'Polished concrete',
            'Epoxy coatings',
            'Tile setters',
            'Hardwood flooring',
            'Rubber flooring',
            'Raised flooring',
            'Athletic flooring',
            'Anti-static flooring',
            'Wallcovering',
            'Acoustic panels',
            'Fabric walls',
            'Wood paneling',
            'Metal panels',
            'Whiteboard installers',
            'Corner guards',
            'Wall protection',
            'Metal ceilings',
            'Wood ceilings',
            'Specialty ceilings',
            'Ceiling clouds',
            'Stretched fabric',
            'Ductwork',
            'Piping insulators',
            'Boilers',
            'Chillers',
            'Cooling towers',
            'Air handlers',
            'Kitchen exhaust',
            'Laboratory exhaust',
            'Clean rooms',
            'Refrigeration',
            'Medical gas',
            'Process piping',
            'Grease traps',
            'Backflow preventers',
            'Water treatment',
            'Sewage ejectors',
            'Generators',
            'Transformers',
            'Switchgear',
            'Motor controls',
            'Lighting controls',
            'Emergency lighting',
            'Exit signs',
            'Audio/visual',
            'Sound systems',
            'Intercom systems',
            'Nurse call',
            'CCTV',
            'Card access',
            'Fiber optics',
            'Data centers',
            'Building automation',
            'Clean room construction',
            'Walk-in coolers',
            'Walk-in freezers',
            'Modular buildings',
            'Prefab restrooms',
        ] as $trade) {
            $this->addSql("INSERT INTO trades (name) VALUES (:trade)", ['trade' => $trade]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subcontractor_trade');
        $this->addSql('DROP TABLE trades');
        $this->addSql('DROP TABLE employees');
        $this->addSql('DROP TABLE subcontractors');
    }
}
