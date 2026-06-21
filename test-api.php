<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$requestDataUser = [
    'name' => 'John Doe',
    'email' => 'walkin_'.time().'@zeta.com',
    'password' => 'password123',
    'phone_number' => '08123456789',
    'role' => 'Owner',
    'status' => 'Aktif',
    'address' => 'Test Address'
];
$validatorUser = Validator::make($requestDataUser, [
    'name'         => 'required|string|max:255',
    'email'        => 'required|string|email|max:255|unique:users',
    'password'     => 'required|string|min:8',
    'phone_number' => 'required|string',
    'role'         => 'required|in:Admin,Dokter,Resepsionis,Apoteker,Kasir,Owner',
    'status'       => 'required|in:Aktif,Tidak Aktif',
    'address'      => 'required|string',
]);
if ($validatorUser->fails()) {
    die("User validation failed: " . json_encode($validatorUser->errors()));
}

$user = User::create([
    'name'         => $requestDataUser['name'],
    'email'        => $requestDataUser['email'],
    'password'     => bcrypt($requestDataUser['password']), 
    'phone_number' => $requestDataUser['phone_number'],
    'role'         => $requestDataUser['role'],
    'status'       => $requestDataUser['status'],
    'address'      => $requestDataUser['address'],
]);
echo "User created: " . $user->id . "\n";

$requestDataPet = [
    'owner_id' => $user->id,
    'name' => 'Fluffy',
    'species' => 'Kucing',
    'gender' => 'Jantan'
];
$validatorPet = Validator::make($requestDataPet, [
    'owner_id'           => 'required|exists:users,id',
    'name'               => 'required|string|max:255',
    'species'            => 'required|in:Kucing,Anjing,Burung,Lainnya',
    'breed'              => 'nullable|string|max:255',
    'gender'             => 'required|in:Jantan,Betina',
]);
if ($validatorPet->fails()) {
    die("Pet validation failed: " . json_encode($validatorPet->errors()));
}
$pet = Pet::create($validatorPet->validated());
echo "Pet created: " . $pet->id . "\n";

$ownerId = $user->id;
$requestDataAppt = [
    'owner_id' => $ownerId,
    'pet_id' => $pet->id,
    'service_id' => 1,
    'booking_type' => 'Walk-in',
    'schedule_date' => date('Y-m-d'),
    'schedule_time' => '10:00',
    'initial_complaint' => 'Test complaint',
    'status' => 'Menunggu'
];
$validatorAppt = Validator::make($requestDataAppt, [
    'owner_id'          => 'required|exists:users,id',
    'pet_id'            => [
        'required',
        Rule::exists('pets', 'id')->where('owner_id', $ownerId),
    ],
    'service_id'        => 'required|exists:services,id',
    'doctor_id'         => 'nullable|exists:users,id',
    'booking_type'      => 'required|in:Online,Walk-in',
    'schedule_date'     => 'required|date',
    'schedule_time'     => 'required|date_format:H:i',
    'initial_complaint' => 'required|string',
    'queue_number'      => 'nullable|string', 
    'status'            => 'nullable|in:Menunggu,Disetujui,Dalam Periksa,Selesai,Batal'
]);
if ($validatorAppt->fails()) {
    die("Appointment validation failed: " . json_encode($validatorAppt->errors()));
}

echo "All validations passed!\n";
