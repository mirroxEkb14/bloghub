<?php

use App\Support\AdminTimezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
