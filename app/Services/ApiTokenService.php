<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiTokenService
{
    public function apiUser(): User
    {
        return User::query()->firstOrCreate(
            ['email' => config('khf.api_user_email')],
            [
                'name' => 'Frontend API',
                'password' => Hash::make(Str::password(64)),
                'is_active' => true,
            ],
        );
    }

    public function generateFrontendToken(): string
    {
        $user = $this->apiUser();

        $user->tokens()
            ->where('name', config('khf.frontend_token_name'))
            ->delete();

        return $user->createToken(config('khf.frontend_token_name'), ['api:read'])->plainTextToken;
    }

    public function hasFrontendToken(): bool
    {
        return $this->apiUser()
            ->tokens()
            ->where('name', config('khf.frontend_token_name'))
            ->exists();
    }
}
