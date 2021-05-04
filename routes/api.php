<?php
use App\Http\Controllers\Api\Auth\ApiSignatrueGuard;

Route::prefix('api')->group(function () {
    Route::post('/signatrueGuard',[ApiSignatrueGuard::class,'login']);
    Route::middleware('auth.signatrue')->post('/userInfo',[ApiSignatrueGuard::class,'userInfo']);
});

