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

        $schedule->call(function () {
            $msg = "[❍△❒] 이번 게임은 보물찾기 입니다. 
'힌트1' 장소에서 12시부터 단 10분간 오렌지마스크를 지급할 예정입니다.🦑";
            $users = MissionStat::where('mission_id', 1671)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1671);
            $users = MissionStat::where('mission_id', 1675)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1675);
        })->cron('0 11 25 9 *');
        $schedule->call(function () {
            $msg = "[❍△❒] 이번 게임은 보물찾기 입니다. 
'힌트1' 장소에서 14시부터 단 10분간 오렌지마스크를 지급할 예정입니다.🦑";
            $users = MissionStat::where('mission_id', 1672)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1672);
            $users = MissionStat::where('mission_id', 1676)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1676);
        })->cron('0 13 25 9 *');
        $schedule->call(function () {
            $msg = "[❍△❒] 이번 게임은 보물찾기 입니다. 
'힌트1' 장소에서 16시부터 단 10분간 오렌지마스크를 지급할 예정입니다.🦑";
            $users = MissionStat::where('mission_id', 1673)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1673);
            $users = MissionStat::where('mission_id', 1677)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1677);
        })->cron('0 15 25 9 *');
        $schedule->call(function () {
            $msg = "[❍△❒] 이번 게임은 보물찾기 입니다. 
'힌트1' 장소에서 18시부터 단 10분간 오렌지마스크를 지급할 예정입니다.🦑";
            $users = MissionStat::where('mission_id', 1674)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1674);
            $users = MissionStat::where('mission_id', 1678)->pluck('user_id')->toArray();
            PushController::gcm_notify($users, '써클인', $msg, '', 'mission',1678);
        })->cron('0 17 25 9 *');

        $schedule->command('telescope:prune --hours=72')->daily();
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
