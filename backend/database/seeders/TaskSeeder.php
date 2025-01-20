<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run()
    {
        // Verificar si la tabla existe
        if (!Schema::hasTable('tasks')) {
            $this->command->error('La tabla tasks no existe!');
            return;
        }

        try {
            DB::beginTransaction();

            // Obtener todos los usuarios
            $users = User::all();

            foreach ($users as $user) {
                // Crear tareas usando el modelo
                Task::create([
                    'user_id' => $user->id,
                    'title' => 'Mi primera tarea',
                    'description' => 'Esta es una tarea de ejemplo',
                    'status' => 'active'
                ]);

                Task::create([
                    'user_id' => $user->id,
                    'title' => 'Mi segunda tarea',
                    'description' => 'Esta es otra tarea de ejemplo',
                    'status' => 'inactive'
                ]);
            }

            DB::commit();
            $this->command->info('Tareas creadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error al crear las tareas: ' . $e->getMessage());
        }
    }
}