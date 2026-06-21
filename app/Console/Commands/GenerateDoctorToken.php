<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateDoctorToken extends Command
{
    protected $signature = 'app:generate-doctor-token {email : Email dokter yang akan dipakai untuk generate token}';

    protected $description = 'Generate token Sanctum untuk dokter agar Swagger UI bisa dipakai test API';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if ($email === '') {
            $this->error('Email dokter wajib diisi.');

            return self::FAILURE;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@') ?: 'Doctor',
                'password' => Hash::make(Str::random(32)),
                'phone_number' => '081234567890',
                'role' => 'Dokter',
                'status' => 'Aktif',
                'address' => 'Alamat dummy untuk testing Swagger',
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info('User dokter baru berhasil dibuat.');
        }

        $token = $user->createToken('TestToken')->plainTextToken;

        $this->info('Doctor email: ' . $user->email);
        $this->info('Plain text token: ' . $token);

        return self::SUCCESS;
    }
}
