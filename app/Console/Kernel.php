<?php

namespace App\Console;

use App\Http\Controllers\v1_1\PushController;
use App\Http\Controllers\v1_1\ScheduleController;
use App\Models\MissionStat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 유저 추천 랜덤정렬
        $schedule->call([ScheduleController::class, 'sort_users'])->hourly()->name('유저 추천 랜덤정렬');

        // 전일 팔로워 피드 전체 수 기록
        $schedule->call([ScheduleController::class, 'yesterday_feeds_count'])->dailyAt('00:00')->name('전일 팔로워 피드 전체 수 기록');

        // 기간 끝난 미션들 종료처리
        $schedule->call([ScheduleController::class, 'mission_over'])->dailyAt('00:00')->name('기간 끝난 미션들 종료처리');

        // 미션 인증 알림
        $schedule->call([ScheduleController::class, 'mission_expire_warning_am'])->dailyAt('08:00')->name('1차 미션 인증 알림');
        $schedule->call([ScheduleController::class, 'mission_expire_warning_pm'])->dailyAt('20:00')->name('2차 미션 인증 알림');

        $schedule->command('telescope:prune --hours=72')->daily();

        $schedule->call(function () {
            $msg = "[월드비전 6K 하이킹2 D-1] 내일부터 시작되는 아프리카 아이들을 위한 하이킹, 모두 준비 되셨나요? 😎";
            $users = MissionStat::where('mission_id', 1701)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1671);
        })->cron('30 19 01 10 *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
