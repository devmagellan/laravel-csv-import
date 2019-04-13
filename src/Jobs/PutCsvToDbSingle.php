<?php

namespace Imediasun\Widgets\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
class PutCsvToDbSingle
{

    public $entity;
    public function __construct($entity)
    {
        $this->entity=$entity;
    }

    public function handle(){
        DB::table('customers')->insert($this->entity);
        $sender['sub']='New Csv Import Received';
        $sender['recipient']=config('widgets.csv_import_recepient');
        $sender['sender']=config('widgets.csv_import_sender');
        $sender['template']='Widgets::template';
        $sender['exception']=false;
        Mail::send(new \Imediasun\Widgets\Mail\OrderSend($sender));
        if( count(Mail::failures()) > 0 ) {
            echo "There was one or more failures. They were: <br />";
        } else {
            $result =true;
        }
    }







}
