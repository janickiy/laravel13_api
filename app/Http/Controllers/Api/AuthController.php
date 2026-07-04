<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegistrationRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        private UserRepository $userRepository,
    ) {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    #[OA\Post(
        path: '/api/v1/register',
        summary: 'Регистрация нового пользователя',
        description: 'Создает новый аккаунт пользователя и возвращает токен доступа.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123!'),
                    new OA\Property(property: 'confirm_password', type: 'string', format: 'password', example: 'Secret123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Пользователь успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов'),
                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'updated_at', type: 'string', example: '2026-02-09T02:36:58.000000Z'),
                        new OA\Property(property: 'created_at', type: 'string', example: '2026-02-09T02:36:58.000000Z'),
                        new OA\Property(property: 'id', type: 'intenger', example: 1),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации (например, такой email уже занят)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The email has already been taken.'),
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function register(RegistrationRequest $request): JsonResponse
    {
        $user = $this->userRepository->createRegisteredUser($request->validated());

        return response()->json($user, 201);
    }

    #[OA\Post(
        path: '/api/v1/login',
        summary: 'Авторизация пользователя',
        description: 'Проверяет учетные данные и выдает API токен (Sanctum/Passport).',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный вход',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'access_token', type: 'string', example: '1|eyJhbGciOiJIUzI1...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неверный логин или пароль',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            )
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->userRepository->attemptLogin($request->validated());

        if (!$token) {
            return response()->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(['token' => $token]);
    }

    public function logout(): JsonResponse
    {
        $this->userRepository->logoutCurrentToken();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
