<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed dummy subcontractor data for development/demo purposes.
 */
final class Version20251202130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed dummy subcontractor and employee data';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        // Dummy subcontractors with realistic Australian business data
        // Trade IDs reference: 1=Building Envelope, 2=Roofing, 3=Waterproofing, 4=Cladding, 5=Glazing,
        // 6=Insulation, 7=MEP Systems, 8=Plumbing, 9=HVAC, 10=Electrical, 11=Fire sprinklers,
        // 12=Drywall, 13=Painting, 14=Flooring, 15=Ceilings, 16=Millwork, 17=Fire alarms,
        // 18=Security systems, 19=Low-voltage, 20=Elevators, 21=Excavation, 22=Paving, 23=Landscaping,
        // 24=Site utilities, 25=Rebar installers, 26=Post-tension, 27=Precast concrete, 28=Tilt-up panels,
        // 29=Shotcrete, 30=Epoxy injection, 31=Structural welding, 32=Metal studs, 33=Heavy timber,
        // 34=Glulam beams, 35=General contractors, 36=Concrete, 37=Steel erectors, 38=Masonry, 39=Carpenters
        $subcontractors = [
            ['name' => 'Smith Electrical Services', 'abn' => '12345678901', 'mobile' => '0412345678', 'email' => 'info@smithelectrical.com.au', 'trades' => [10, 19, 84]],
            ['name' => 'Coastal Plumbing Pty Ltd', 'abn' => '23456789012', 'mobile' => '0423456789', 'email' => 'admin@coastalplumbing.com.au', 'trades' => [8, 79, 80, 81]],
            ['name' => 'Metro HVAC Solutions', 'abn' => '34567890123', 'mobile' => '0434567890', 'email' => 'enquiries@metrohvac.com.au', 'trades' => [9, 68, 70, 71, 73]],
            ['name' => 'Aussie Roofing Co', 'abn' => '45678901234', 'mobile' => '0445678901', 'email' => 'jobs@aussieroofing.com.au', 'trades' => [2, 52, 53, 54]],
            ['name' => 'Sydney Concrete Works', 'abn' => '56789012345', 'mobile' => '0456789012', 'email' => 'quotes@sydneyconcrete.com.au', 'trades' => [36, 25, 26, 27, 28]],
            ['name' => 'Melbourne Steel Erectors', 'abn' => '67890123456', 'mobile' => '0467890123', 'email' => 'info@melbsteel.com.au', 'trades' => [37, 31, 32]],
            ['name' => 'Brisbane Glazing Specialists', 'abn' => '78901234567', 'mobile' => '0478901234', 'email' => 'sales@brisglaze.com.au', 'trades' => [5, 65, 66]],
            ['name' => 'Perth Painting Pros', 'abn' => '89012345678', 'mobile' => '0489012345', 'email' => 'bookings@perthpainting.com.au', 'trades' => [13, 55, 56]],
            ['name' => 'Adelaide Flooring Masters', 'abn' => '90123456789', 'mobile' => '0490123456', 'email' => 'hello@adelaideflooring.com.au', 'trades' => [14, 46, 47, 49, 50, 51]],
            ['name' => 'Gold Coast Fire Systems', 'abn' => '01234567890', 'mobile' => '0401234567', 'email' => 'service@gcfiresystems.com.au', 'trades' => [11, 17, 89, 90]],
            ['name' => 'Canberra Carpentry Services', 'abn' => '11223344556', 'mobile' => '0411223344', 'email' => 'team@canberracarpentry.com.au', 'trades' => [39, 33, 16]],
            ['name' => 'Hobart Masonry Works', 'abn' => '22334455667', 'mobile' => '0422334455', 'email' => 'enquiry@hobartmasonry.com.au', 'trades' => [38, 40, 41]],
            ['name' => 'Darwin Excavation & Paving', 'abn' => '33445566778', 'mobile' => '0433445566', 'email' => 'ops@darwinexcavation.com.au', 'trades' => [21, 22, 24]],
            ['name' => 'Newcastle Drywall Solutions', 'abn' => '44556677889', 'mobile' => '0444556677', 'email' => 'info@newcastledrywall.com.au', 'trades' => [12, 32]],
            ['name' => 'Sunshine Coast Landscaping', 'abn' => '55667788990', 'mobile' => '0455667788', 'email' => 'design@sunshinecoastlandscaping.com.au', 'trades' => [23]],
            ['name' => 'Geelong Security Systems', 'abn' => '66778899001', 'mobile' => '0466778899', 'email' => 'sales@geelongsecurity.com.au', 'trades' => [18, 95, 96]],
            ['name' => 'Wollongong Waterproofing', 'abn' => '77889900112', 'mobile' => '0477889900', 'email' => 'projects@wollongongwaterproofing.com.au', 'trades' => [3, 45]],
            ['name' => 'Cairns Cladding Contractors', 'abn' => '88990011223', 'mobile' => '0488990011', 'email' => 'contact@cairnscladding.com.au', 'trades' => [4, 40, 41, 42]],
            ['name' => 'Townsville Tiling Co', 'abn' => '99001122334', 'mobile' => '0499001122', 'email' => 'admin@townsvilletiling.com.au', 'trades' => [49, 46, 47]],
            ['name' => 'Ballarat Building Envelope', 'abn' => '10203040506', 'mobile' => '0410203040', 'email' => 'info@ballaratenvelope.com.au', 'trades' => [1, 6, 44, 45]],
        ];

        // Employee templates per subcontractor
        $employeeTemplates = [
            ['name' => 'John', 'job_title' => 'Director'],
            ['name' => 'Sarah', 'job_title' => 'Project Manager'],
            ['name' => 'Michael', 'job_title' => 'Site Supervisor'],
        ];

        $lastNames = ['Wilson', 'Brown', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen'];

        foreach ($subcontractors as $index => $sub) {
            // Generate slug from name
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $sub['name']));
            $slug = trim($slug, '-');
            
            // Insert subcontractor
            $this->addSql(
                "INSERT INTO subcontractors (name, abn, slug, mobile, email, current_step, created_at, updated_at) VALUES (:name, :abn, :slug, :mobile, :email, 5, :created_at, :updated_at)",
                [
                    'name' => $sub['name'],
                    'abn' => $sub['abn'],
                    'slug' => $slug,
                    'mobile' => $sub['mobile'],
                    'email' => $sub['email'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Get the subcontractor ID (SQLite uses last_insert_rowid)
            $subId = $index + 1;

            // Insert employees for this subcontractor
            foreach ($employeeTemplates as $empIndex => $emp) {
                $lastName = $lastNames[($index + $empIndex) % count($lastNames)];
                $empName = $emp['name'] . ' ' . $lastName;
                $empEmail = strtolower($emp['name']) . '.' . strtolower($lastName) . '@' . str_replace(['Pty Ltd', ' ', '&'], ['', '', ''], strtolower($sub['name'])) . '.com.au';
                $empMobile = '04' . str_pad((string)(($index * 3 + $empIndex) * 11111111 % 100000000), 8, '0', STR_PAD_LEFT);

                $this->addSql(
                    "INSERT INTO employees (subcontractor_id, name, job_title, mobile, email, created_at, updated_at) VALUES (:subcontractor_id, :name, :job_title, :mobile, :email, :created_at, :updated_at)",
                    [
                        'subcontractor_id' => $subId,
                        'name' => $empName,
                        'job_title' => $emp['job_title'],
                        'mobile' => $empMobile,
                        'email' => $empEmail,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            // Set main contact to the first employee (Director)
            $mainContactId = ($index * 3) + 1;
            $this->addSql(
                "UPDATE subcontractors SET main_contact_employee_id = :main_contact_id WHERE id = :id",
                ['main_contact_id' => $mainContactId, 'id' => $subId]
            );

            // Link trades to subcontractor
            foreach ($sub['trades'] as $tradeId) {
                $this->addSql(
                    "INSERT INTO subcontractor_trade (subcontractor_id, trade_id) VALUES (:subcontractor_id, :trade_id)",
                    ['subcontractor_id' => $subId, 'trade_id' => $tradeId]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Remove all seeded data
        $this->addSql('DELETE FROM subcontractor_trade');
        $this->addSql('DELETE FROM employees');
        $this->addSql('DELETE FROM subcontractors');
    }
}
