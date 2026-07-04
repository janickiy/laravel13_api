<?php

namespace App\DTO\Notes;

use App\DTO\DataTransferObject;

final readonly class NoteData implements DataTransferObject
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
    ) {
    }

    public static function fromArray(array $data, int|string|null $id = null): self
    {
        return new self(
            id: (int) ($id ?? $data['id'] ?? 0),
            title: (string) $data['title'],
            content: (string) $data['content'],
        );
    }

    public function id(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
