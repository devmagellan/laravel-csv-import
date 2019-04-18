<?php

namespace Imediasun\Widgets;

use \League\Csv\Reader as CsvReader;
use \Imediasun\Widgets\Jobs\SendErrorMessage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class WidgetHelper
{


    public static function sortArrayByArray(array $array, array $orderArray) {
        $new_arr = array();
        foreach ($orderArray as $key => $val){
            $new_arr +=
                [
                    strtolower($key) => $array[strtolower($key)]
                ];
        }
        return $new_arr;
    }


    public static function validateHeader($array,$validators){
        foreach($array as $k=>$v){
            foreach($validators as $key=>$value){
                if($v==null && (strpos($value, 'required') !== false) ){return false;}
                else{$rules[$key]=false;}
            }
        }
        return true;
    }

    public static function getCsv($source){
        $csv = CsvReader::createFromStream($source);
        $csv->setHeaderOffset(0); //set the CSV header
        $csv->setDelimiter(';');
        foreach($csv->getHeader() as $k=>$column){
            $result['header'][$k]=strtolower($column);
        }
        foreach ($csv as $key=>$record) {$result['fill'][]=$record;}
        return $result;
    }



    public static function notification($message){
        $log = ['date' => date("Y-m-d H:i:s"),
            'error' => $message];
        $orderLog = new Logger('files');
        $orderLog->pushHandler(new StreamHandler(storage_path('logs/csv_import_exceptions.log')), Logger::INFO);
        $orderLog->info('CsvImportLog', $log);
        Queue::push(new SendErrorMessage(mb_convert_encoding(trim($log['error']), 'UTF-8', mb_detect_encoding(trim($log['error']), 'UTF-8, ISO-8859-1', true))));
    }


}