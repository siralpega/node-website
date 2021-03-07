<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

use Illuminate\Support\Facades\Log;

class FirebaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function get(){
        $factory = (new Factory())->withDatabaseUri(config('urls.database'));
        $database = $factory->createDatabase();
        $reference = $database->getReference('data');
        $value = $reference->getValue();
        return response()->json($value);
        
    }

    public function getIndex($start, $end){
        $factory = (new Factory())->withDatabaseUri(config('urls.database'));
        $database = $factory->createDatabase();
        $value = $database->getReference('data')
        ->orderByKey()
        ->startAt($start)
        ->endAt($end)
        ->getValue();

        return $value;
    }

    public function updateDatabase(){
        //Get data from api
        $api_response = Http::get(config('urls.api'));
        //Place in database
        $this->postToDatabase($api_response);
    }

    private function postToDatabase(Response $response){
        $factory = (new Factory())->withDatabaseUri(config('urls.database'));
        $database = $factory->createDatabase();
        $reference = $database->getReference('data');
        $time =  date("Y-n-j H:i:s");
        $array = json_decode($response, true);
       
        for($i = 0; $i < count($array); $i++){
            $data = $array[$i];
            $updates = [
                'id' => $data['id'],
                'up_since' => $data['up_since'],
            ];
            $reference = $database->getReference('data/'.$i);
            $reference->update($updates);
            $updates = [
                'allocated_ram' => $data['allocated_ram'],
                'allocated_disk' => $data['allocated_disk'],
                'free_ram' => $data['free_ram'],
                'free_disk' => $data['free_disk'],
                'timestamp' => Database::SERVER_TIMESTAMP
            ];
            $reference = $database->getReference('data/'.$i.'/timestamps'.'/'.$time);
            $reference->update($updates);
        }    
    } 

    public function cleanDatabase(){
        Log::info('Cleaning up database. Removing data points older than 24 hours, except for every data point on the hour');
        $factory = (new Factory())->withDatabaseUri(config('urls.database'));
        $database = $factory->createDatabase();
        $reference = $database->getReference('data');
        $time_now = round(microtime(true) * 1000); //date("Y-n-j H:i:s")
        $cutoff = $time_now - 24 * 60 * 60 * 1000;
        foreach ($reference->getValue() as $num => $index) {
            $timestamp_reference = $database->getReference('data/'.$num.'/timestamps');
            foreach ($timestamp_reference->getValue() as $timestamp => $ts) {
                $fb_timestamp = $timestamp_reference->getChild($timestamp.'/timestamp')->getValue();
                $result = $fb_timestamp - $cutoff;
                if($result < 0){
                    //For every hour past 24 hours, only keep one set of data (on the hour)
                    $date = strtotime($timestamp);
                    $actual_date = date('i', $date);
                    if($actual_date != 0)
                        $timestamp_reference->getChild($timestamp)->remove();
                }
            }
        }
    }   
}