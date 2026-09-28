<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$names = ['Siti Aminah', 'Budi Santoso', 'Rina Wati', 'Andi Pratama', 'Nur Hayati', 'Ahmad Fauzi', 'Dewi Lestari', 'Joko Susilo', 'Maya Sari', 'Hendra Wijaya'];
$genders = ['L', 'P'];
$placeId = DB::table('treatment_places')->where('is_active', true)->value('id');
if (!$placeId) {
    $placeId = DB::table('treatment_places')->insertGetId(['name' => 'Puskesmas Sukamaju', 'type' => 'Puskesmas', 'address' => 'Jl. Sehat Bersama No. 1', 'phone' => '0210000000', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
}
DB::transaction(function () use ($names, $genders, $placeId): void {
    foreach ($names as $index => $name) {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $phone = '62812900900' . $number;
        $nik = '32730116019000' . $number;
        if (DB::table('users')->where('phone', $phone)->exists() || DB::table('patients')->where('nik', $nik)->exists()) continue;
        $userId = DB::table('users')->insertGetId(['name' => $name, 'phone' => $phone, 'password' => Hash::make('pasien123'), 'role' => 'pasien', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('patients')->insert(['user_id' => $userId, 'medical_record_number' => 'RM-2026-' . $number, 'nik' => $nik, 'full_name' => $name, 'birth_place' => 'Sukamaju', 'birth_date' => '1990-01-' . $number, 'gender' => $genders[array_rand($genders)], 'phone' => $phone, 'rt' => '00' . random_int(1, 4), 'rw' => '009', 'full_address' => 'Jl. Sehat Bersama No. ' . ($index + 1) . ', Sukamaju', 'treatment_place_id' => $placeId, 'treatment_start_date' => '2026-01-15', 'daily_dose_frequency' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
    }
});
echo "10 data pasien contoh selesai diproses.\n";
