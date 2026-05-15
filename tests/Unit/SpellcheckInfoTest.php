<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\Data\SpellcheckInfo;

it('builds from a full spellcheck array', function (): void {
    $info = SpellcheckInfo::fromArray([
        'changed'   => true,
        'original'  => 'larval',
        'corrected' => 'laravel',
    ]);

    expect($info->changed)->toBeTrue()
        ->and($info->original)->toBe('larval')
        ->and($info->corrected)->toBe('laravel');
});

it('defaults changed to false when absent', function (): void {
    $info = SpellcheckInfo::fromArray(['original' => 'test', 'corrected' => 'test']);

    expect($info->changed)->toBeFalse();
});

it('handles empty array gracefully', function (): void {
    $info = SpellcheckInfo::fromArray([]);

    expect($info->changed)->toBeFalse()
        ->and($info->original)->toBe('')
        ->and($info->corrected)->toBe('');
});

it('serialises to array', function (): void {
    $info = new SpellcheckInfo(changed: true, original: 'larval', corrected: 'laravel');

    expect($info->toArray())->toBe([
        'changed'   => true,
        'original'  => 'larval',
        'corrected' => 'laravel',
    ]);
});
