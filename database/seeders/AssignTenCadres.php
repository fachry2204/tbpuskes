<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$names = ['Nurhayati Rahma', 'Fitri Handayani', 'Ayu Lestari', 'Siti Rahmawati', 'Dian Puspita', 'Wulan Sari', 'Lilis Komariah', 'Rani Oktaviani', 'Maya Anggraini', 'Nisa Khairani'];
$cadreIds = [];
DB::transaction(function () use ($names, &$cadreIds): void {
    foreach ($names as $index => $name) {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $nik = '32730165019000' . $number;
        $phone = '62813900900' . $number;
        $cadre = DB::table('cadres')->where('nik', $nik)->first();
        if ($cadre) { $cadreIds[] = $cadre->id; continue; }
        $userId = DB::table('users')->insertGetId(['name' => $name, 'phone' => $phone, 'password' => Hash::make('kader123'), 'role' => 'kader', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $cadreIds[] = DB::table('cadres')->insertGetId(['user_id' => $userId, 'nik' => $nik, 'full_name' => $name, 'birth_place' => 'Sukamaju', 'birth_date' => '1990-02-' . $number, 'gender' => 'P', 'phone' => $phone, 'rt' => '00' . random_int(1, 4), 'rw' => '009', 'full_address' => 'Jl. Sehat Bersama No. ' . ($index + 20) . ', Sukamaju', 'working_area' => 'Wilayah Binaan ' . ($index + 1), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
    }
    $patients = DB::table('patients')->where('status', 'active')->orderBy('id')->limit(count($cadreIds))->pluck('id');
    foreach ($patients as $index => $patientId) DB::table('patients')->where('id', $patientId)->update(['cadre_id' => $cadreIds[$index % count($cadreIds)], 'updated_at' => now()]);
});
echo "10 kader perempuan ditambahkan dan pasien aktif dibagi ke kader.\n";
