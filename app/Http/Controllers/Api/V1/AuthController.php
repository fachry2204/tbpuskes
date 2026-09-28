<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $login = trim((string) $request->validated('login'));
        $phone = $this->normalizePhone($login);
        $user = User::query()->where('username', $login)->orWhere('email', $login)->when($phone, fn ($query) => $query->orWhere('phone', $phone))->first();
        if (! $user || ! $user->is_active || ! Hash::check((string) $request->validated('password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Kredensial tidak valid.'], 422);
        }
        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('web-'.($request->userAgent() ?: 'client'), ['*'], now()->addDays(30))->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Login berhasil.', 'data' => ['token' => $token, 'user' => $user]]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil.']);
    }

    private function normalizePhone(string $phone): ?string
    {
        $number = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        if (str_starts_with($number, '+62')) $number = substr($number, 1);
        if (str_starts_with($number, '0')) $number = '62'.substr($number, 1);
        return preg_match('/^628[0-9]{7,12}$/', $number) ? $number : null;
    }
}
