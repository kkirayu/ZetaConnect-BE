<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ClinicSettingController extends Controller
{
    /**
     * Tampilkan pengaturan klinik.
     */
    public function index()
    {
        $setting = ClinicSetting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan klinik belum disetel',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan klinik berhasil diambil',
            'data'    => $setting
        ], 200);
    }

    /**
     * Simpan atau update pengaturan klinik.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clinic_name'       => 'required|string|max:255',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo_url'          => 'nullable|string', // Bisa via string URL kalau bukan file
            'address'           => 'required|string',
            'phone_number'      => 'required|string',
            'email'             => 'required|email',
            'operational_hours' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $setting = ClinicSetting::first() ?? new ClinicSetting();

        $setting->clinic_name = $request->clinic_name;
        $setting->address = $request->address;
        $setting->phone_number = $request->phone_number;
        $setting->email = $request->email;
        $setting->operational_hours = $request->operational_hours;

        if ($request->hasFile('logo')) {
            // Delete old logo if exists and is local file
            if ($setting->logo_url && !filter_var($setting->logo_url, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $setting->logo_url));
            }
            $uploadedFileUrl = $request->file('logo')->storeOnCloudinary('zetaconnect/logos')->getSecurePath();
            $setting->logo_url = $uploadedFileUrl;
        } elseif ($request->has('logo_url')) {
            $setting->logo_url = $request->logo_url;
        }

        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan klinik berhasil disimpan',
            'data'    => $setting
        ], 200);
    }
}
