<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRegisteredUser(array $data): User
    {
        $payload = Arr::only($data, ['name', 'email', 'password']);
        $payload['password'] = Hash::make((string) $payload['password']);

        /** @var User $user */
        $user = $this->create($payload);

        return $user;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    public function attemptLogin(array $credentials): ?string
    {
        $token = JWTAuth::attempt(Arr::only($credentials, ['email', 'password']));

        return $token ?: null;
    }

    public function logoutCurrentToken(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
