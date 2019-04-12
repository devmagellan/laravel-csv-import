<?php

namespace App\Widgets;

use Imediasun\Widgets\Contract\ContractWidget;

class TestWidget implements ContractWidget{

    public function execute(){

        return view('Widgets::test');

    }
}