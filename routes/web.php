<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/media/profile/{directory}/{filename}', function (string $directory, string $filename) {
    abort_unless(in_array($directory, ['patients', 'cadres'], true), 404);

    $path = "{$directory}/{$filename}";
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path), [
        'Cache-Control' => 'private, max-age=3600',
    ]);
})->where('filename', '[^/]+');

Route::fallback(function () {
    return response()->file(public_path('index.html'));
});
