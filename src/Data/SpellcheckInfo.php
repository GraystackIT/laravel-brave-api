<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class SpellcheckInfo
{
    public function __construct(
        public readonly bool $changed,
        public readonly string $original,
        public readonly string $corrected,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            changed:   (bool) ($data['changed'] ?? false),
            original:  (string) ($data['original'] ?? ''),
            corrected: (string) ($data['corrected'] ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'changed'   => $this->changed,
            'original'  => $this->original,
            'corrected' => $this->corrected,
        ];
    }
}
