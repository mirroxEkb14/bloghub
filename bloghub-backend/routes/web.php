<?php

use App\Support\AdminTimezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/docs/swagger', function () {
    return view('docs.swagger');
})->name('docs.swagger');

Route::get('/docs/openapi.json', function () {
    $path = public_path('api-docs/openapi.json');
    abort_if(!is_file($path), 404, 'OpenAPI spec not found');

    return response()->file($path, [
        'Content-Type' => 'application/json',
    ]);
})->name('docs.openapi');

Route::get('/', function () {
    return view('welcome');
});

Route::post('/admin/timezone', function (Request $request) {
    $tz = $request->header('X-Timezone') ?: $request->input('timezone');

    if (is_string($tz) && AdminTimezone::isValid($tz)) {
        session([AdminTimezone::ADMIN_TZ_SESSION_KEY => $tz]);
        session()->save();
    }

    return response()->noContent();
})->middleware(['web', 'auth'])->name('admin.timezone');
