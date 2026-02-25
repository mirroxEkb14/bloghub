<?php

use App\Support\AdminTimezone;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('docs')->group(function () {
    Scramble::registerUiRoute('swagger')->name('docs.swagger');
    Scramble::registerJsonSpecificationRoute('swagger.json')->name('docs.openapi');
});

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
