<?php

namespace Imediasun\Widgets\Jobs;

use Illuminate\Support\Facades\Mail;
class SendErrorMessage
{

    public $error;
    public function __construct($error)
    {
        $this->error=$error;
    }

    public function handle(){
    $sender['sub']='Error in Csv Import Received';
    $sender['recipient']=config('widgets.csv_import_recepient');
    $sender['sender']=config('widgets.csv_import_sender');
    $sender['template']='Widgets::error';
    $sender['exception']=$this->error;
        Mail::send(new \Imediasun\Widgets\Mail\OrderSend($sender));
        if( count(Mail::failures()) > 0 ) {
            echo "There was one or more failures. They were: <br />";
        } else {
            $result =true;
        }
    }







}
