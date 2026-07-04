<?php

namespace App\Repositories;

use App\DTO\Notes\NoteData;
use App\Models\Notes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class NoteRepository extends BaseRepository
{
    public function __construct(Notes $model)
    {
        parent::__construct($model);
    }

    public function createFromData(NoteData $data): Builder|Model
    {
        return $this->create($data->toArray());
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromArray(array $data): Builder|Model
    {
        return $this->createFromData(NoteData::fromArray($data));
    }

    public function updateFromData(NoteData $data): bool
    {
        return $this->update($data->id(), $data->toArray());
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromArray(array $data): bool
    {
        return $this->updateFromData(NoteData::fromArray($data));
    }

    public function allForUser(int $userId): Collection
    {
        return $this->model
            ->query()
            ->where('user_id', $userId)
            ->get();
    }

    public function findForUser(int|string $id, int $userId): ?Notes
    {
        /** @var Notes|null $note */
        $note = $this->model
            ->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        return $note;
    }

    public function createForUser(NoteData $data, int $userId): Notes
    {
        /** @var Notes $note */
        $note = $this->create(array_merge($data->toArray(), ['user_id' => $userId]));

        return $note;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createForUserFromArray(array $data, int $userId): Notes
    {
        return $this->createForUser(NoteData::fromArray($data), $userId);
    }

    public function updateForUser(NoteData $data, int $userId): ?Notes
    {
        $note = $this->findForUser($data->id(), $userId);

        if (!$note) {
            return null;
        }

        $note->fill($data->toArray());
        $note->save();

        return $note;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateForUserFromArray(array $data, int|string $id, int $userId): ?Notes
    {
        return $this->updateForUser(NoteData::fromArray($data, $id), $userId);
    }

    public function deleteForUser(int|string $id, int $userId): bool
    {
        return $this->model
            ->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

}
