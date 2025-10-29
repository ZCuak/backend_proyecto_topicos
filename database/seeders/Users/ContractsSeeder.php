<?php

namespace Database\Seeders\Users;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No hay usuarios en la base de datos. Ejecuta UserSeeder primero.');
            return;
        }

        $contracts = [];

        foreach ($users as $index => $user) {
            $contractType = match ($index % 3) {
                0 => 'NOMBRADO',
                1 => 'PERMANENTE',
                2 => 'EVENTUAL',
            };

            
            $dateStart = Carbon::now()->subMonths(rand(1, 24));
            $dateEnd = null;
            $isActive = true;
            $vacationDays = 30;
            $probationMonths = null;
            $terminationReason = null;

            switch ($contractType) {
                case 'NOMBRADO':
                    // Contrato indefinido
                    $dateEnd = null;
                    $vacationDays = 30;
                    $probationMonths = 0;
                    break;

                case 'PERMANENTE':
                    // Contrato a largo plazo
                    $dateEnd = $dateStart->copy()->addYears(2);
                    $vacationDays = 30;
                    $probationMonths = 3;
                    break;

                case 'EVENTUAL':
                    // Contrato temporal (2 meses)
                    $dateEnd = $dateStart->copy()->addMonths(2);
                    $vacationDays = 0; // No tienen vacaciones
                    $probationMonths = 0;

                    // Algunos contratos eventuales ya expiraron
                    if ($index % 5 == 0) {
                        $dateEnd = Carbon::now()->subDays(rand(1, 30));
                        $isActive = false;
                        $terminationReason = 'Fin de contrato temporal';
                    }
                    break;
            }

            // Salario base según función
            $salary = match ($user->usertype_id) {
                1 => rand(1500, 2000), // Conductor
                2 => rand(1200, 1500), // Ayudante
                3 => rand(2000, 2500), // Supervisor
                4 => rand(1800, 2200), // Coordinador
                default => rand(1200, 1800),
            };

            $contracts[] = [
                'type' => $contractType,
                'date_start' => $dateStart->toDateString(),
                'date_end' => $dateEnd ? $dateEnd->toDateString() : null,
                'vacation_days_per_year' => $vacationDays,
                'is_active' => $isActive,
                'user_id' => $user->id,
                'salary' => $salary,
                'position_id' => $user->usertype_id, // Usando usertype como position
                'department_id' => 1, // Departamento genérico (ajustar según tu estructura)
                'probation_period_months' => $probationMonths,
                'termination_reason' => $terminationReason,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar todos los contratos
        foreach ($contracts as $contract) {
            Contract::create($contract);
        }

        $this->command->info('✅ Se crearon ' . count($contracts) . ' contratos.');
        $this->command->info('   - NOMBRADOS: ' . collect($contracts)->where('type', 'NOMBRADO')->count());
        $this->command->info('   - PERMANENTES: ' . collect($contracts)->where('type', 'PERMANENTE')->count());
        $this->command->info('   - EVENTUALES: ' . collect($contracts)->where('type', 'EVENTUAL')->count());
        $this->command->info('   - Activos: ' . collect($contracts)->where('is_active', true)->count());
        $this->command->info('   - Inactivos: ' . collect($contracts)->where('is_active', false)->count());
    }
}
