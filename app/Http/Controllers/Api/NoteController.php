<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Notes\DeleteRequest;
use App\Http\Requests\Api\Notes\StoreRequest;
use App\Http\Requests\Api\Notes\UpdateRequest;
use App\Repositories\NoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Notes',
    description: 'Работа с заметками авторизованного пользователя',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT token from /api/v1/login',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
#[OA\Schema(
    schema: 'Note',
    required: ['id', 'user_id', 'title', 'content', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Планы на день'),
        new OA\Property(property: 'content', type: 'string', example: 'Подготовить релиз и проверить API.'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-04T19:20:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-04T19:20:00.000000Z'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'NotePayload',
    required: ['title', 'content'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Планы на день'),
        new OA\Property(property: 'content', type: 'string', example: 'Подготовить релиз и проверить API.'),
    ],
    type: 'object',
)]
class NoteController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        private NoteRepository $noteRepository,
    ) {
        $this->middleware('auth:api');
    }

    #[OA\Get(
        path: '/api/v1/notes',
        operationId: 'notesIndex',
        summary: 'Список заметок',
        description: 'Возвращает все заметки текущего авторизованного пользователя.',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список заметок',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Note'),
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function index(): JsonResponse
    {
        $notes = $this->noteRepository->allForUser((int) Auth::id());

        return response()->json($notes);
    }

    #[OA\Get(
        path: '/api/v1/notes/{id}',
        operationId: 'notesShow',
        summary: 'Просмотр заметки',
        description: 'Возвращает заметку текущего пользователя по ID.',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заметки',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заметка найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Note'),
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Заметка не найдена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Note not found'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function show(int $id): JsonResponse
    {
        $note = $this->noteRepository->findForUser($id, (int) Auth::id());

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }

    #[OA\Post(
        path: '/api/v1/notes/store',
        operationId: 'notesStore',
        summary: 'Создание заметки',
        description: 'Создает заметку для текущего авторизованного пользователя.',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/NotePayload'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Заметка создана',
                content: new OA\JsonContent(ref: '#/components/schemas/Note'),
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function store(StoreRequest $request): JsonResponse
    {
        $note = $this->noteRepository->createForUserFromArray($request->validated(), (int) Auth::id());

        return response()->json($note, Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/v1/notes/update/{id}',
        operationId: 'notesUpdate',
        summary: 'Обновление заметки',
        description: 'Обновляет заголовок и содержимое заметки текущего пользователя.',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заметки',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/NotePayload'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заметка обновлена',
                content: new OA\JsonContent(ref: '#/components/schemas/Note'),
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $note = $this->noteRepository->updateForUserFromArray($request->validated(), $id, (int) Auth::id());

        return response()->json($note);
    }

    #[OA\Delete(
        path: '/api/v1/notes/delete/{id}',
        operationId: 'notesDestroy',
        summary: 'Удаление заметки',
        description: 'Удаляет заметку текущего пользователя по ID.',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заметки',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заметка удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Note deleted'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The id field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function destroy(DeleteRequest $request): JsonResponse
    {
        $this->noteRepository->deleteForUser($request->id, Auth::id());

        return response()->json(['message' => 'Note deleted']);
    }
}
