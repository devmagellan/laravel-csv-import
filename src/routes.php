<?php
Route::get('/api/import_csv', function () {
    return view('welcome');
});

Route::post('/api/action', '\Imediasun\Widgets\ApiController@processFromForm');