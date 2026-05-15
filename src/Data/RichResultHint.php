<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class RichResultHint
{
    public function __construct(
        public readonly string $vertical,
        public readonly string $callbackKey,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            vertical:    (string) ($data['hint']['vertical'] ?? ''),
            callbackKey: (string) ($data['hint']['callback_key'] ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'vertical'    => $this->vertical,
            'callbackKey' => $this->callbackKey,
        ];
    }
}
