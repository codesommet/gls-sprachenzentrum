<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Site;
use Illuminate\Database\Seeder;

class EmployeeRHSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::all()->keyBy('slug');

        if ($sites->isEmpty()) {
            $this->command->warn('Aucun site trouvé. Lancez SitesTableSeeder d\'abord.');
            return;
        }

        $employees = [
            // Casablanca
            ['name' => 'Salma Benkirane',    'slug' => 'casablanca', 'role' => 'Administration', 'phone' => '+212 661-112233', 'email' => 'salma.benkirane@glszentrum.com',    'hired_at' => '2024-09-01'],
            ['name' => 'Youssef Amrani',     'slug' => 'casablanca', 'role' => 'Réception',      'phone' => '+212 662-223344', 'email' => 'youssef.amrani@glszentrum.com',     'hired_at' => '2025-01-15'],
            ['name' => 'Khadija El Fassi',   'slug' => 'casablanca', 'role' => 'Commercial',     'phone' => '+212 663-334455', 'email' => 'khadija.elfassi@glszentrum.com',    'hired_at' => '2025-03-10'],
            ['name' => 'Omar Benchekroun',   'slug' => 'casablanca', 'role' => 'Manager',        'phone' => '+212 664-445566', 'email' => 'omar.benchekroun@glszentrum.com',  'hired_at' => '2024-06-01'],

            // Rabat
            ['name' => 'Fatima Zahra Idrissi', 'slug' => 'rabat', 'role' => 'Administration', 'phone' => '+212 665-556677', 'email' => 'fz.idrissi@glszentrum.com',       'hired_at' => '2024-11-01'],
            ['name' => 'Amine Tazi',           'slug' => 'rabat', 'role' => 'Réception',      'phone' => '+212 666-667788', 'email' => 'amine.tazi@glszentrum.com',        'hired_at' => '2025-02-01'],
            ['name' => 'Hajar Ouazzani',       'slug' => 'rabat', 'role' => 'Commercial',     'phone' => '+212 667-778899', 'email' => 'hajar.ouazzani@glszentrum.com',    'hired_at' => '2025-05-15'],

            // Marrakech
            ['name' => 'Rachid Alaoui',     'slug' => 'marrakech', 'role' => 'Manager',        'phone' => '+212 668-889900', 'email' => 'rachid.alaoui@glszentrum.com',     'hired_at' => '2024-08-01'],
            ['name' => 'Nadia Chraibi',      'slug' => 'marrakech', 'role' => 'Réception',      'phone' => '+212 669-990011', 'email' => 'nadia.chraibi@glszentrum.com',     'hired_at' => '2025-04-01'],
            ['name' => 'Imane Lahlou',       'slug' => 'marrakech', 'role' => 'Administration', 'phone' => '+212 670-001122', 'email' => 'imane.lahlou@glszentrum.com',      'hired_at' => '2025-06-01'],

            // Kénitra
            ['name' => 'Mehdi Bouziane',     'slug' => 'kenitra', 'role' => 'Coordination', 'phone' => '+212 671-112233', 'email' => 'mehdi.bouziane@glszentrum.com',    'hired_at' => '2025-01-10'],
            ['name' => 'Sara El Moussaoui',  'slug' => 'kenitra', 'role' => 'Réception',    'phone' => '+212 672-223344', 'email' => 'sara.elmoussaoui@glszentrum.com',  'hired_at' => '2025-07-01'],

            // Salé
            ['name' => 'Karim Naciri',       'slug' => 'sale', 'role' => 'Administration', 'phone' => '+212 673-334455', 'email' => 'karim.naciri@glszentrum.com',      'hired_at' => '2025-02-15'],
            ['name' => 'Zineb Harrak',       'slug' => 'sale', 'role' => 'Commercial',     'phone' => '+212 674-445566', 'email' => 'zineb.harrak@glszentrum.com',      'hired_at' => '2025-08-01'],

            // Agadir
            ['name' => 'Abdelkader Rifi',    'slug' => 'agadir', 'role' => 'Manager',    'phone' => '+212 675-556677', 'email' => 'abdelkader.rifi@glszentrum.com',   'hired_at' => '2024-10-01'],
            ['name' => 'Soukaina Bennani',   'slug' => 'agadir', 'role' => 'Réception',  'phone' => '+212 676-667788', 'email' => 'soukaina.bennani@glszentrum.com',  'hired_at' => '2025-03-01'],
        ];

        $scheduleTemplates = [
            'Administration' => ['start' => '09:00', 'end' => '17:00', 'break_start' => '12:30', 'break_end' => '13:30'],
            'Réception'      => ['start' => '08:30', 'end' => '16:30', 'break_start' => '12:00', 'break_end' => '13:00'],
            'Commercial'     => ['start' => '09:30', 'end' => '18:00', 'break_start' => '13:00', 'break_end' => '14:00'],
            'Manager'        => ['start' => '08:00', 'end' => '17:00', 'break_start' => '12:00', 'break_end' => '13:00'],
            'Coordination'   => ['start' => '09:00', 'end' => '17:30', 'break_start' => '12:30', 'break_end' => '13:30'],
            'Autre'          => ['start' => '09:00', 'end' => '17:00', 'break_start' => '12:00', 'break_end' => '13:00'],
        ];

        // Generate schedules for March 2026 (same month as the payroll data)
        $monthStart = new \DateTime('2026-03-02');
        $monthEnd   = new \DateTime('2026-03-31');

        foreach ($employees as $emp) {
            $site = $sites->get($emp['slug']);
            if (! $site) {
                continue;
            }

            $employee = Employee::firstOrCreate(
                ['email' => $emp['email']],
                [
                    'name'      => $emp['name'],
                    'site_id'   => $site->id,
                    'role'      => $emp['role'],
                    'phone'     => $emp['phone'],
                    'is_active' => true,
                    'hired_at'  => $emp['hired_at'],
                ]
            );

            $template = $scheduleTemplates[$emp['role']] ?? $scheduleTemplates['Autre'];
            $calculated = EmployeeSchedule::calculateMinutes($template);

            // Seed weekdays for the month — skip random days to simulate realistic absences
            $cursor = clone $monthStart;
            $dayIndex = 0;
            while ($cursor <= $monthEnd) {
                $dow = (int) $cursor->format('w');

                // Skip weekends
                if ($dow !== 0 && $dow !== 6) {
                    // ~85% attendance: skip roughly 1 in 7 workdays
                    $skip = ($dayIndex % 7 === 3 && $dayIndex % 3 === 0);

                    if (! $skip) {
                        EmployeeSchedule::firstOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'date'        => $cursor->format('Y-m-d'),
                            ],
                            [
                                'site_id'            => $site->id,
                                'start_time'         => $template['start'],
                                'end_time'           => $template['end'],
                                'break_start'        => $template['break_start'],
                                'break_end'          => $template['break_end'],
                                'total_span_minutes' => $calculated['total_span_minutes'],
                                'break_minutes'      => $calculated['break_minutes'],
                                'worked_minutes'     => $calculated['worked_minutes'],
                            ]
                        );
                    }

                    $dayIndex++;
                }

                $cursor->modify('+1 day');
            }
        }

        $this->command->info('16 employés RH + planning Mars 2026 créés avec succès.');
    }
}
