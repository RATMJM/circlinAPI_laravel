<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $email
 * @property string|null $email_verified_at 이메일 인증 일시
 * @property string $password
 * @property string|null $nickname
 * @property string|null $family_name
 * @property string|null $given_name
 * @property string|null $name
 * @property string|null $gender
 * @property string|null $phone
 * @property string|null $phone_verified_at 휴대폰 인증 일시
 * @property int $point
 * @property string|null $profile_image 프로필 사진
 * @property string|null $greeting 인사말
 * @property string|null $background_image 배경 커버 이미지
 * @property string|null $area_code
 * @property string|null $area_updated_at 지역 변경 일자
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $remember_token
 * @property string|null $device_type 접속한 기기 종류(android/iphone)
 * @property string|null $socket_id 채팅방 (nodejs) socket id
 * @property string|null $device_token 푸시 전송 토큰
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property string|null $refresh_token_expire_in
 * @property string|null $last_login_at 마지막 로그인 시점
 * @property string|null $last_login_ip 마지막 로그인 IP
 * @property string|null $current_version 마지막 접속 시점 버전
 * @property int $agree1 서비스 이용약관 동의
 * @property int $agree2 개인정보 수집 및 이용약관 동의
 * @property int $agree3 위치정보 이용약관 동의
 * @property int $agree4 이메일 마케팅 동의
 * @property int $agree5 SMS 마케팅 동의
 * @property int $agree_push 푸시알림 동의
 * @property int $agree_push_mission 미션알림 동의
 * @property int $agree_ad 광고수신 동의
 * @property string|null $invite_code 초대코드
 * @property int|null $recommend_user_id
 * @property string|null $recommend_updated_at
 * @property float $lat 위도
 * @property float $lng 경도
 * @property-read \App\Models\DeleteUser|null $delete_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserFavoriteCategory[] $favorite_categories
 * @property-read int|null $favorite_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followings
 * @property-read int|null $followings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionStat[] $mission_stats
 * @property-read int|null $mission_stats_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\UserStat|null $stat
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgree1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgree2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgree3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgree4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgree5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgreeAd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgreePush($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgreePushMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAreaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAreaUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGivenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGreeting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfileImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRecommendUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRecommendUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRefreshTokenExpireIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSocketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthday' => 'date:Ymd',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function stat()
    {
        return $this->hasOne('App\Models\UserStat');
    }

    public function favorite_categories()
    {
        return $this->hasMany('App\Models\UserFavoriteCategory');
    }

    public function followings()
    {
        return $this->hasMany('App\Models\Follow');
    }

    public function followers()
    {
        return $this->hasMany('App\Models\Follow', 'target_id');
    }

    public function mission_stats()
    {
        return $this->hasMany('App\Models\MissionStat');
    }

    public function delete_user()
    {
        return $this->hasOne(DeleteUser::class);
    }

    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }
}
