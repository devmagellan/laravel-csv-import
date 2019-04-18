<?php

namespace Imediasun\Widgets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Mockery\Exception;
use Illuminate\Support\Facades\Queue;
use \Imediasun\Widgets\Jobs\PutCsvToDbSingle;

use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{

    protected $source;
    protected $paramFields;
    protected $validators;
    protected $table;
    protected $headerError='One ore Few columns in header of csv didnt math input names in configureFields function';

    public function processFromForm(){

        $this->setSource();
        $this->process();
    }

    public function setDestination($table){
        $this->table=$table;
    }
    public function setSource($value=null)
    {
        if($file =Input::file('csv')){
            $path = $file->storeAs('csv', \Str::random(40) . '.' . $file->getClientOriginalExtension());
            $stream = fopen(storage_path('app/'.$path), 'r+');
            $this->source=$stream;
        }
        else{
            $stream = fopen($value, 'r+');
            $this->source=$stream;
        }

    }

    public function configureFields($array){
        foreach($array as $key=>$value){
            $this->paramFields[$key]=$value['field'];
            $this->validators[strtolower($key)]=$value['validators'];

        }
    }


    public function process(){
        //Get Csv
        $csv=WidgetHelper::getCsv($this->source);
        try{
            //input mapping
            foreach($this->paramFields as $key=>$input_field){
                if(in_array(strtolower($key),$csv['header'])){
                    $res_column[$key]=$input_field;
                }
                else{
                    $res_column[$key]=null;
                }

            }
            //validation of header
            if(!WidgetHelper::validateHeader($res_column,$this->validators))
            {
                WidgetHelper::notification($this->headerError);
                dd('Error:look to email and log file for details',$this->headerError);
            }
            //get mapping data
            foreach($csv['fill'] as $key=>$value){
                foreach($value as $k=>$res){
                    if(in_array(strtolower($k),$this->paramFields)){
                        $res_fill[$key][strtolower($k)]=$res;
                    }
                }

            }
            //sort validators
            $res_validator=WidgetHelper::sortArrayByArray($this->validators, $res_fill[0]);
            //Validation of data
            foreach($res_fill as $key=>$value){
                $validator = Validator::make($value, $res_validator);
                if ($validator->fails())
                {
                    dump('not valid');
                }
                else{
                    $result[]=$value;
                }
            }

            Queue::push(new PutCsvToDbSingle($result));
            dump('data loaded successfully');

            return true;


        }
        catch(\Exception $e){
            WidgetHelper::notification($e->getMessage());
            dump('Error:look to email and log file for details',$e->getMessage());
        }

    }



}