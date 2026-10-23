<?php

declare(strict_types=1);

// Amjad's rule: no brown in any UI. Filament draws badges and modal icons as translucent fills in dark mode, and a
// translucent amber, orange or red fill on a dark background reads as brown. Warm colours stay text, lines and borders.

it('keeps warm colours off the plugin UI', function (): void {
    $source = collect(glob(__DIR__.'/../../{src,stubs}/{,*/,*/*/}*.{php,stub}', GLOB_BRACE))
        ->map(fn (string $file): string => (string) file_get_contents($file))
        ->implode("\n");

    expect($source)
        ->not->toMatch("/->color\\('(warning|danger|primary)'\\)/")
        ->not->toMatch("/modalIconColor\\('(warning|danger|primary)'\\)/")
        ->not->toMatch('/\b(brown|tan|maroon|sienna|chocolate)\b/i')
        ->not->toMatch('/(amber|orange|yellow)-(7|8|9)00|red-900|stone-/');
});
