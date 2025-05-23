<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\M_Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role yang sama tidak dibuat dua kali
        $arr = [
            'owner',
            'pegawai',
            'customer'
        ];

        foreach ($arr as $index => $name) {
            M_Role::firstOrCreate([
                'id' => $index + 1, // ID akan dimulai dari 1
            ], [
                'nama_role' => $name
            ]);
        }
    }
}
