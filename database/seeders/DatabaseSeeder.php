<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Rack;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users for each role
        User::firstOrCreate(['email' => 'owner@nexstock.com'], [
            'name' => 'Owner NexStock',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        User::firstOrCreate(['email' => 'admin@nexstock.com'], [
            'name' => 'Admin Gudang NexStock',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
        ]);

        User::firstOrCreate(['email' => 'staff@nexstock.com'], [
            'name' => 'Staff Gudang NexStock',
            'password' => Hash::make('password'),
            'role' => 'staff_gudang',
        ]);

        // 2. Seed Kategori (Categories)
        Category::firstOrCreate(['nama_kategori' => 'Makanan']);
        Category::firstOrCreate(['nama_kategori' => 'Minuman']);

        // 3. Seed Racks
        Rack::firstOrCreate(['kode_rak' => 'A1'], [
            'kapasitas_maksimum_volume' => 1000,
            'kapasitas_terpakai' => 0,
        ]);
        Rack::firstOrCreate(['kode_rak' => 'A2'], [
            'kapasitas_maksimum_volume' => 1000,
            'kapasitas_terpakai' => 0,
        ]);
        Rack::firstOrCreate(['kode_rak' => 'B1'], [
            'kapasitas_maksimum_volume' => 800,
            'kapasitas_terpakai' => 0,
        ]);
        Rack::firstOrCreate(['kode_rak' => 'B2'], [
            'kapasitas_maksimum_volume' => 800,
            'kapasitas_terpakai' => 0,
        ]);

        // 4. Seed Suppliers
        Supplier::firstOrCreate(['nama_supplier' => 'PT Indofood Sukses Makmur'], [
            'kontak' => '081234567890 (Budi)',
        ]);
        Supplier::firstOrCreate(['nama_supplier' => 'PT Mayora Indah Tbk'], [
            'kontak' => '081298765432 (Santi)',
        ]);
        Supplier::firstOrCreate(['nama_supplier' => 'CV Sumber Pangan'], [
            'kontak' => '081122334455 (Joko)',
        ]);
    }
}
