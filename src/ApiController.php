<?php

namespace Imediasun\Widgets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use \League\Csv\Reader as CsvReader;

class ApiController extends Controller
{
    public function store(Request $request){
        $file = Input::file('csv');
        $path = $file->storeAs('csv', \Str::random(40) . '.' . $file->getClientOriginalExtension());
        dump($path);
        dump($request->file('csv'));

        $csv = CsvReader::createFromPath($path, 'r');
        dump($csv->getHeader());
    }
}