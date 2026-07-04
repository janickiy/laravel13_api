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


    public function index(): JsonResponse
    {
        $notes = $this->noteRepository->allForUser((int) Auth::id());

        return response()->json($notes);
    }


    public function show(int $id): JsonResponse
    {
        $note = $this->noteRepository->findForUser($id, (int) Auth::id());

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }


    public function store(StoreRequest $request): JsonResponse
    {
        $note = $this->noteRepository->createForUserFromArray($request->validated(), (int) Auth::id());

        return response()->json($note, Response::HTTP_CREATED);
    }


    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $note = $this->noteRepository->updateForUserFromArray($request->validated(), $id, (int) Auth::id());

        return response()->json($note);
    }


    public function destroy(DeleteRequest $request): JsonResponse
    {
        $this->noteRepository->deleteForUser($request->id, Auth::id());

        return response()->json(['message' => 'Note deleted']);
    }
}
