<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionGround
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string|null $intro_video 소개 페이지 상단 동영상
 * @property string|null $logo_image 상단 로고
 * @property string|null $code_type 코드 타입
 * @property string|null $code_title 입장코드 라벨
 * @property string|null $code 입장코드 (있으면 비교, 없으면 입력 받기만)
 * @property string|null $code_placeholder 입장코드 입력란 placeholder
 * @property string|null $code_image 입장코드 룸 상단 이미지
 * @property string|null $goal_distance_title 참가하기 전 목표 거리 타이틀
 * @property string $goal_distance_type 성공 조건 (goal/min)
 * @property array|null $goal_distances 참가하기 전 설정할 목표 거리 (km)
 * @property string|null $goal_distance_text 참가하기 전 설정할 목표 거리 접미사
 * @property string|null $distance_placeholder
 * @property string|null $background_image 운동장 전체 fixed 배경 이미지
 * @property string $ground_title 운동장 탭 타이틀
 * @property string $record_title 내기록 탭 타이틀
 * @property string $cert_title 인증서 탭 타이틀
 * @property string|null $feeds_title 피드 탭 타이틀 (null 일 경우 노출 X)
 * @property string $rank_title 랭킹 탭 타이틀
 * @property int $ground_is_calendar 운동장 탭 캘린더 형태 여부
 * @property string|null $ground_background_image 운동장 탭 배경이미지
 * @property string $ground_progress_type 운동장 탭 진행상황 타입 (feed/distance)
 * @property int $ground_progress_max 운동장 탭 진행상황 최대치
 * @property string|null $ground_progress_background_image 운동장 탭 진행상황 배경이미지
 * @property string|null $ground_progress_image 운동장 탭 진행상황 차오르는 이미지
 * @property string|null $ground_progress_title 운동장 탭 진행상황 타이틀
 * @property string $ground_progress_text 운동장 탭 진행상황 텍스트
 * @property string $ground_box_users_count_text 운동장 탭 참가중인 유저 수 텍스트
 * @property string $ground_box_users_count_title 운동장 탭 참가중인 유저 수 타이틀
 * @property string $ground_box_summary_text 운동장 탭 피드 수 텍스트
 * @property string $ground_box_summary_title 운동장 탭 피드 수 타이틀
 * @property string|null $ground_banner_image
 * @property string|null $ground_banner_type
 * @property string|null $ground_banner_link
 * @property string $ground_users_type 운동장 탭 유저 목록 타입
 * @property string $ground_users_title 운동장 탭 유저 목록 타이틀
 * @property string $ground_users_text 운동장 탭 유저 목록 비었을 때 텍스트
 * @property int $record_progress_is_show 내기록 탭 진행상황 노출 여부
 * @property string|null $record_background_image 내기록 탭 배경이미지
 * @property int $record_progress_image_count 내기록 탭 진행상황 뱃지 개수
 * @property array|null $record_progress_images 내기록 탭 진행상황 이미지
 * @property string $record_progress_type 내기록 탭 진행상황 타입
 * @property string|null $record_progress_title 내기록 탭 진행상황 타이틀
 * @property string|null $record_progress_text 내기록 탭 진행상황 텍스트
 * @property string|null $record_progress_description 내기록 탭 진행상황 텍스트 옆 설명
 * @property int $record_box_is_show 내기록 탭 박스 노출 여부
 * @property string|null $record_box_left_title 내기록 탭 박스 왼쪽 타이틀
 * @property string|null $record_box_left_text 내기록 탭 박스 왼쪽 텍스트
 * @property string|null $record_box_center_title 내기록 탭 박스 가운데 타이틀
 * @property string|null $record_box_center_text 내기록 탭 박스 가운데 텍스트
 * @property string|null $record_box_right_title 내기록 탭 박스 오른쪽 타이틀
 * @property string|null $record_box_right_text 내기록 탭 박스 오른쪽 텍스트
 * @property string|null $record_box_description 내기록 탭 박스 하단 설명
 * @property string $cert_subtitle 인증서 탭 인증서 타이틀
 * @property string|null $cert_description 인증서 탭 내용
 * @property array|null $cert_background_image 인증서 탭 인증서 배경 이미지
 * @property array|null $cert_custom_cert
 * @property array|null $cert_details 인증서 탭 인증서 상세내용
 * @property array|null $cert_images 인증서 탭 하단 이미지
 * @property string|null $cert_disabled_text 인증서 탭 비활성화 상태 멘트
 * @property int $cert_enabled_feeds_count 인증서 탭 인증서 활성화될 피드 수
 * @property string|null $rank_subtitle 랭킹 탭 부제목
 * @property string|null $rank_value_text 랭킹 탭 피드 수 포맷
 * @property string $feeds_filter_title 전체 피드 탭 필터 타이틀
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionCalendarVideo[] $calendar_videos
 * @property-read int|null $calendar_videos_count
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertCustomCert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertDisabledText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertEnabledFeedsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCertTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCodeImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCodePlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCodeTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCodeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereDistancePlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereFeedsFilterTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereFeedsTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGoalDistanceText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGoalDistanceTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGoalDistanceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGoalDistances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBannerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBannerLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBoxSummaryText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBoxSummaryTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBoxUsersCountText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundBoxUsersCountTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundIsCalendar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundProgressType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundUsersText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundUsersTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereGroundUsersType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereIntroVideo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereLogoImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRankSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRankTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRankValueText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxCenterText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxCenterTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxLeftText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxLeftTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxRightText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordBoxRightTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressImageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordProgressType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereRecordTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGround whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionGround extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'goal_distances' => 'array',
        'record_progress_images' => 'array',
        'cert_background_image' => 'array',
        'cert_custom_cert' => 'array',
        'cert_details' => 'array',
        'cert_images' => 'array',
    ];

    public function calendar_videos()
    {
        return $this->hasMany(MissionCalendarVideo::class, 'mission_id', 'mission_id');
    }
}
