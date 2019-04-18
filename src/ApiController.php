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

class ApiController extends Controller
{
    protected $source;
    protected $paramFields;
    
    public function processFromForm()
    {
        $this->setSource();
        $this->process();
    }

    public function setSource($value = null)
    {
        if ($file = Input::file('csv')) {
            $path = $file->storeAs('csv', \Str::random(40) . '.' . $file->getClientOriginalExtension());
            $stream = fopen(storage_path('app/' . $path), 'r+');
            $this->source = $stream;
        } else {
            $stream = fopen($value, 'r+');
            $this->source = $stream;
        }
    }


    public function configureFields($value = null)
    {
        //Check for NULL
        if (is_null($value)) {
            $this->logWriter('configure_fields_exceptions.log', 'ConfigureFieldsLog', 'ERROR: configureFields is NULL');
            return false;
        }

        //Check for ARRAY
        if (!is_array($value)) {
            $this->logWriter('configure_fields_exceptions.log', 'ConfigureFieldsLog', 'ERROR: configureFields is not ARRAY');
            return false;
        }

        //Set parameters
        $this->paramFields=$value;
        
    }

    public function process()
    {
        $csv = CsvReader::createFromStream($this->source);
        $csv->setHeaderOffset(0); //set the CSV header
        $csv->setDelimiter(';');
        $csv->getHeader();  //returns the CSV header record
        try {
            $csv->getRecords();
            $diff = array('created_at', 'updated_at', 'id');
            $fields = DB::getSchemaBuilder()->getColumnListing('customers');
            $res_fields = array_diff($fields, $diff);
            $res_header = explode(';', $csv->getHeader()[0]);
            foreach ($csv as $key => $record) {
                $fill[] = $record;
            }
            foreach ($fill as $key => $value) {
                foreach ($value as $k => $res) {
                    if (in_array(strtolower($k), $res_fields)) {
                        $res_fill[$key][$k] = $res;
                    }
                }
            }
            Queue::push(new PutCsvToDbSingle($res_fill));
            dump('data load successfully');
            return true;
        } catch (\Exception $e) {
            $this->logWriter('csv_import_exceptions.log', 'CsvImportLog', $e->getMessage());
        }
    }


    protected function logWriter($file, $info, $message)
    {
        try {
            $log = ['date' => date("Y-m-d H:i:s"),
                'error' => $message];
            $orderLog = new Logger('files');
            $orderLog->pushHandler(new StreamHandler(storage_path('logs/' . $file)), Logger::INFO);
            $orderLog->info($info, $log);
            Queue::push(new SendErrorMessage(mb_convert_encoding(trim($message), 'UTF-8', mb_detect_encoding(trim($message), 'UTF-8, ISO-8859-1', true))));
            dump('Error:look to email and log file for details', $message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
