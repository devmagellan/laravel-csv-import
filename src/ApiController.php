<?php

namespace Imediasun\Widgets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use \League\Csv\Reader as CsvReader;
use Mockery\Exception;
use Illuminate\Support\Facades\Queue;
use \Imediasun\Widgets\Jobs\PutCsvToDbSingle;
use \Imediasun\Widgets\Jobs\SendErrorMessage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ApiController extends Controller
{

    protected $source;
    protected $paramFields;
    protected $validators;
    protected $table;

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
        $csv = CsvReader::createFromStream($this->source);
        $csv->setHeaderOffset(0); //set the CSV header
        $csv->setDelimiter(';');
        $csv->getHeader();  //returns the CSV header record
        try{$csv->getRecords();

            $res_fields=$this->paramFields;
            foreach($csv->getHeader() as $k=>$column){
                $header[$k]=strtolower($column);
            }

            foreach ($csv as $key=>$record) {$fill[]=$record;}

            foreach($res_fields as $key=>$input_field){
                if(in_array(strtolower($key),$header)){
                    $res_column[$key]=$input_field;
                }
                else{
                    $res_column[$key]=null;
                }

            }
            $header_validator=$this->validateHeader($res_column);
            //validation of header
            if(!$header_validator)
            {
                $log = ['date' => date("Y-m-d H:i:s"),
                    'error' => 'One ore Few columns in header of csv didnt math input names in configureFields function'];

                $orderLog = new Logger('files');
                $orderLog->pushHandler(new StreamHandler(storage_path('logs/csv_import_exceptions.log')), Logger::INFO);
                $orderLog->info('CsvImportLog', $log);
                Queue::push(new SendErrorMessage(mb_convert_encoding(trim($log['error']), 'UTF-8', mb_detect_encoding(trim($log['error']), 'UTF-8, ISO-8859-1', true))));
                dd('Error:look to email and log file for details',$log['error']);
            }
            $res_header=$csv->getHeader();
            foreach($fill as $key=>$value){
                foreach($value as $k=>$res){
                    if(in_array(strtolower($k),$this->paramFields)){
                        $res_fill[$key][strtolower($k)]=$res;
                    }
                }

            }
            $res_validator=$this->sortArrayByArray($this->validators, $res_fill[0]);
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

            $log = ['date' => date("Y-m-d H:i:s"),
                'error' => $e->getMessage()];

            $orderLog = new Logger('files');
            $orderLog->pushHandler(new StreamHandler(storage_path('logs/csv_import_exceptions.log')), Logger::INFO);
            $orderLog->info('CsvImportLog', $log);
            Queue::push(new SendErrorMessage(mb_convert_encoding(trim($e->getMessage()), 'UTF-8', mb_detect_encoding(trim($e->getMessage()), 'UTF-8, ISO-8859-1', true))));
            dump('Error:look to email and log file for details',$e->getMessage());
        }

    }
    protected function sortArrayByArray(array $array, array $orderArray) {
        $new_arr = array();
        foreach ($orderArray as $key => $val){
            $new_arr +=
                [
                    strtolower($key) => $array[strtolower($key)]
                ];
        }
        return $new_arr;
    }


    protected function validateHeader($array){
        foreach($array as $k=>$v){
            foreach($this->validators as $key=>$value){
                if($v==null && (strpos($value, 'required') !== false) ){return false;}
                else{$rules[$key]=false;}
            }
        }
        return true;
    }


}