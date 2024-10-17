<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use GuzzleHttp\Client;
use DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // * * * * * cd /var/www/html/mjlsi/Code/BackEnd && php artisan schedule:run >> /var/www/html/mjlsi/Code/BackEnd/reminderOutput.txt 2>&1
      

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/reminder-job');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/signature-next-participan-job');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/task-expired');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->dailyAt('10:00');

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/organization-expired-date');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->dailyAt('10:00');

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/documents-reviews/end-date');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/circular-decisions/start-date');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();
       
        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/documents/start-date');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();

        $schedule->call(function () {
            $body=[];
            $headers=[];
            $user=DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer '.$token;
            $body['headers']=$headers;
            $url=url('/api/v1/circular-decisions/completed');
            $client = new Client();
            $client->request('GET', $url , $body);
        })->everyMinute();

        $schedule->call(function () {
            $body = [];
            $headers = [];
            $user = DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer ' . $token;
            $body['headers'] = $headers;
            $url = url('/api/v1/admin/committees/change-committee-status-job');
            $client = new Client();
            $client->request('GET', $url, $body);
        })->dailyAt('10:00');

        //    $schedule->call('App\Http\Controllers\MeetingController@getMeetingRemindersForEmail')
        //     ->everyMinute();

        $schedule->call(function () {
            $body = [];
            $headers = [];
            $user = DB::table('users')->selectRaw('users.*,id as user_id')->limit(1)->get();
            $customClaims = ['user_id' => $user[0]->user_id];
            $token = JWTAuth::fromUser($user[0], $customClaims);
            $headers['Authorization'] = 'Bearer ' . $token;
            $body['headers'] = $headers;
            $url = url('/api/v1/admin/committees/notify-head-members-committee-job');
            $client = new Client();
            $client->request('GET', $url, $body);
        })->dailyAt('10:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
