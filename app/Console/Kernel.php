<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

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
        $schedule->call(function () {
            $users = User::inRandomOrder()->pluck('id');

            $i = 0;
            $data = [];

            \App\Models\SortUser::truncate();
            foreach ($users as $j => $user) {
                $data[] = [
                    'created_at' => DB::raw("now()"), 'updated_at' => DB::raw("now()"),
                    'user_id' => $user, 'order' => $i++,
                ];
                if ($j % 1000 === 0) {
                    \App\Models\SortUser::insert($data);
                    $data = [];
                }
            }
            \App\Models\SortUser::insert($data);
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
