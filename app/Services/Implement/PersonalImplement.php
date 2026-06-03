<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\Personal;
use App\Services\PersonalService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PersonalImplement implements PersonalService
{
    function get($request) {}
    function show($id) {}
    function post($request) {}
    function registerFace($request) {
        try {
            $faceRecognitionBaseUrl = env('FACERECOGNITION_API_URL');
            if (empty($faceRecognitionBaseUrl)) {
                throw new \Exception('FACERECOGNITION_API_URL is not configured', 500);
            }
            $employeeId = is_array($request)? ($request['employee_id'] ?? null) : $request->input('employee_id');
            if (empty($employeeId)) {
                throw new \Exception('employee_id is required', 422);
            }
            $employee = Employee::with('personal')->find($employeeId);
            if (!$employee) {
                throw new \Exception('Employee not found', 404);
            }
            $personal = $employee->personal;
            if (!$personal) {
                throw new \Exception('Personal data not found', 404);
            }
            $imageInput = is_array($request)
                ? ($request['image'] ?? $request['photo'] ?? null)
                : ($request->file('image') ?? $request->input('image') ?? $request->input('photo'));

            if (empty($imageInput)) {
                throw new \Exception('image is required', 422);
            }

            $extension = 'jpg';
            $binaryImage = null;

            if ($imageInput instanceof UploadedFile) {
                $extension = strtolower($imageInput->getClientOriginalExtension() ?: 'jpg');
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }
                $binaryImage = file_get_contents($imageInput->getRealPath());
            } elseif (is_string($imageInput)) {
                $rawPhoto = $imageInput;
                if (preg_match('/^data:image\/(png|jpe?g);base64,/', $rawPhoto, $matches)) {
                    $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                    $rawPhoto = substr($rawPhoto, strpos($rawPhoto, ',') + 1);
                }

                $rawPhoto = str_replace(' ', '+', $rawPhoto);
                $binaryImage = base64_decode($rawPhoto, true);
            }

            if (empty($binaryImage)) {
                throw new \Exception('Invalid image payload', 422);
            }

            $imageName = 'profile_' . $employee->id . '_' . time() . '.' . $extension;
            $photoPath = 'profile/' . $imageName;
            Storage::disk('public')->put($photoPath, $binaryImage);
            $absolutePath = storage_path('app/public/' . $photoPath);

            $faceRecognitionResponse = Http::timeout(15)
                ->attach('image', file_get_contents($absolutePath), $imageName)
                ->post(rtrim($faceRecognitionBaseUrl, '/') . '/register', [
                    'employeeId' => $employee->id,
                ]);

            if (!$faceRecognitionResponse->successful()) {
                if (Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }

                $message = $faceRecognitionResponse->json('detail')
                    ?? $faceRecognitionResponse->json('message')
                    ?? 'Face recognition service is unavailable';

                throw new \Exception($message, 502);
            }

            $responseDetail = $faceRecognitionResponse->json('detail');
            if (!empty($responseDetail)) {
                if (Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                throw new \Exception($responseDetail, 422);
            }

            $personal->update(['avatar' => $photoPath]);

            return $employee->load('personal','employment');
        } catch (\Throwable $th) {
            $statusCode = (int) $th->getCode();
            if ($statusCode < 100 || $statusCode > 599) {
                $statusCode = 500;
            }

            throw new \Exception($th->getMessage(), $statusCode);
        }
    }
    function put($request)
    {
        try {
            $personal = Personal::findOrFail($request['id']);
            $request['birth_date'] = !empty($request['birth_date'])
                ? Carbon::parse($request['birth_date'])->format('Y-m-d')
                : null;

            $request['expired_date_identity_id'] = !empty($request['expired_date_identity_id'])
                ? Carbon::parse($request['expired_date_identity_id'])->format('Y-m-d')
                : null;
            $request['gendre'] = $request['gendre'] == "male" ? 1 : 2;

            // ✅ Set fullname otomatis
            $request['fullname'] = $request['first_name'] . ' ' . $request['last_name'];
            $personal->update($request);

            return response()->json(["message" => "Data berhasil diubah", "data" => $personal], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
