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
            $msg = "[월드비전 6k EVENT] 찾아라! 제리캔 히어로🦸‍♀️🦸 지금 운동장에서 이벤트 내용을 확인하고 투표에 참여해보세요😉";
            $users = MissionStat::where('mission_id', 1701)->pluck('user_id')->toArray();
            // $users = User::pluck('id');
            $tmp = [];
            foreach ($users as $user) {
                $tmp[] = $user;
                if (count($tmp) >= 1000) {
                    PushController::gcm_notify($tmp, '써클인', $msg, '');
                    $tmp = [];
                }
            }
            PushController::gcm_notify($tmp, '써클인', $msg, '');
        })->cron('30 11 27 10 *');
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
