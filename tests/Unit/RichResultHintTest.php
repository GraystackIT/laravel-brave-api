<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\Data\RichResultHint;

it('builds from a rich result array', function (): void {
    $hint = RichResultHint::fromArray([
        'type' => 'rich',
        'hint' => [
            'vertical'     => 'weather',
            'callback_key' => 'weather_key_abc',
        ],
    ]);

    expect($hint->vertical)->toBe('weather')
        ->and($hint->callbackKey)->toBe('weather_key_abc');
});

it('handles missing hint gracefully', function (): void {
    $hint = RichResultHint::fromArray(['type' => 'rich']);

    expect($hint->vertical)->toBe('')
        ->and($hint->callbackKey)->toBe('');
});

it('handles empty array gracefully', function (): void {
    $hint = RichResultHint::fromArray([]);

    expect($hint->vertical)->toBe('')
        ->and($hint->callbackKey)->toBe('');
});

it('serialises to array', function (): void {
    $hint = new RichResultHint(vertical: 'weather', callbackKey: 'weather_key_abc');

    expect($hint->toArray())->toBe([
        'vertical'    => 'weather',
        'callbackKey' => 'weather_key_abc',
    ]);
});
