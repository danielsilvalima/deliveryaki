<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      if (\App\Models\User::count() === 0) {
        \App\Models\User::factory(30)->create();
      }
      $this->call(HorarioExpedienteSeeder::class);
      $this->call(AgendaHorarioExpedienteSeeder::class);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
