@extends('layouts.admin')

@section('title', '배너 클릭률 통계 - ' . $banner->name)

@section('content')
    @php
        $header = ['float' => '홈 배너', 'local' => '미션 탭 배너', 'shop' => '샵 배너'];
        $link_type = ['mission' => '미션', 'event_mission' => '이벤트 미션', 'product' => '제품', 'notice' => '공지사항', 'url' => 'URL'];
    @endphp
    <h1>{{ $banner->name }}</h1>
    <div style="display: flex">
        <img src="{{ $banner->image }}" alt="" width="300px">
        <div class="board" style="display: flex; flex-direction: column; margin: 0">
            <h2>
                배너 기간 : {{ $banner->started_at ?? '-' }} ~ {{ $banner->ended_at ?? '-' }}
                {!! $banner->is_available ? '' : '<span style="color:red">(종료됨)</span>' !!}
            </h2>
            <h2>
                @if($banner->link_type === 'url')
                    링크 : {{ $link_type[$banner->link_type] }}
                    ( <a href="{{ $banner->link_url }}">{{ $banner->link_url }}</a> )
                @elseif($banner->link_type === 'notice')
                    링크 : <a href="{{ route('admin.notice.show', ['notice' => $banner->link_id]) }}">
                        {{ $link_type[$banner->link_type] }}
                    </a>
                @else
                    링크 : {{ $link_type[$banner->link_type] }}
                    ( {{ $banner->link_id }} )
                @endif
            </h2>
        </div>
    </div>

    <style>
        #table {
            border: 2px solid #000;
            border-right-width: 1px;
        }
        #table #head {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        #table p {
            padding: 5px 10px;
            border-right: 1px solid #000;
        }
        #table #head p {
            border-bottom: 1px solid #fff;
            border-right: 1px solid #fff;
        }
        #table > *:not(:last-child) p {
            border-bottom: 1px solid #000;
        }

        .flex-row {
            display: flex;
            flex-direction: row;
            width: 100%;
        }
        .flex-col {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
    </style>
    <h2>일별 통계</h2>
    <div id="table" class="flex-col">
        <div class="flex-row" id="head">
            <p class="flex-center" style="flex: 1">일자</p>
            <div class="flex-col" style="flex: 14">
                <p class="flex-center" style="flex: 1">클릭 수 / 노출 수 / 클릭률</p>

                <div class="flex-row" style="flex: 1">
                    <div class="flex-col" style="flex: 1">
                        <p class="flex-center" style="flex: 1">전체</p>
                    </div>
                    {{-- OS별 --}}
                    <div class="flex-col" style="flex: 3">
                        <p class="flex-center" style="flex: 1">OS별</p>
                        <div class="flex-row" style="flex: 1">
                            <p class="flex-center" style="flex: 1">안드로이드</p>
                            <p class="flex-center" style="flex: 1">IOS</p>
                            <p class="flex-center" style="flex: 1">그 외 OS</p>
                        </div>
                    </div>
                    {{-- 성별 --}}
                    <div class="flex-col" style="flex: 3">
                        <p class="flex-center" style="flex: 1">성별</p>
                        <div class="flex-row" style="flex: 1">
                            <p class="flex-center" style="flex: 1">남성</p>
                            <p class="flex-center" style="flex: 1">여성</p>
                            <p class="flex-center" style="flex: 1">성별 미상</p>
                        </div>
                    </div>
                    {{-- 연령별 --}}
                    <div class="flex-col" style="flex: 7">
                        <p class="flex-center" style="flex: 1">연령별</p>
                        <div class="flex-row" style="flex: 1">
                            <p class="flex-center" style="flex: 1">10대 이하</p>
                            <p class="flex-center" style="flex: 1">20대</p>
                            <p class="flex-center" style="flex: 1">30대</p>
                            <p class="flex-center" style="flex: 1">40대</p>
                            <p class="flex-center" style="flex: 1">50대</p>
                            <p class="flex-center" style="flex: 1">50대 초과</p>
                            <p class="flex-center" style="flex: 1">연령 미상</p>
                        </div>
                    </div>
                </div>
{{--                <div class="flex-row" style="flex: 1">--}}
{{--                    <p class="flex-center" style="flex: 1">전체</p>--}}
{{--                    <p class="flex-center" style="flex: 1">안드로이드</p>--}}
{{--                    <p class="flex-center" style="flex: 1">IOS</p>--}}
{{--                    <p class="flex-center" style="flex: 1">그 외 OS</p>--}}
{{--                    <p class="flex-center" style="flex: 1">성별(남성)</p>--}}
{{--                    <p class="flex-center" style="flex: 1">성별(여성)</p>--}}
{{--                    <p class="flex-center" style="flex: 1">성별(성별 미입력자)</p>--}}
{{--                    <p class="flex-center" style="flex: 1">연령별(00대)</p>--}}
{{--                    <p class="flex-center" style="flex: 1">연령별(연령 미입력자)</p>--}}
{{--                </div>--}}
            </div>
        </div>
        @forelse($data as $item)
            <div class="flex-row">
                <p class="flex-center" style="flex: 1"><span>{{ $item->date }}</span></p>
                <div class="flex-row" style="flex: 14">
                    <p class="flex-center" style="flex: 1">
                        {{ $item->clicks_count }} / {{ $item->views_count }}
                        <br><br>{{ round($item->clicks_count / max($item->views_count, 1) * 100, 1) }}%
                    </p>

                    {{-- OS별 통계 --}}
                    <p class="flex-center" style="flex: 1">
                        {{ $item->android_clicks_count }} / {{ $item->android_views_count }}
                        <br><br>{{ round($item->android_clicks_count / max($item->android_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->ios_clicks_count }} / {{ $item->ios_views_count }}
                        <br><br>{{ round($item->ios_clicks_count / max($item->ios_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->etc_clicks_count }} / {{ $item->etc_views_count }}
                        <br><br>{{ round($item->etc_clicks_count / max($item->etc_views_count, 1) * 100, 1) }}%
                    </p>

                    {{-- 성별 통계 --}}
                    {{-- 남성 --}}
                    <p class="flex-center" style="flex: 1">
                        {{ $item->male_clicks_count }} / {{ $item->male_views_count }}
                        <br><br>{{ round($item->male_clicks_count / max($item->male_views_count, 1) * 100, 1) }}%
                    </p>
                    {{-- 여성 --}}
                    <p class="flex-center" style="flex: 1">
                        {{ $item->female_clicks_count }} / {{ $item->female_views_count }}
                        <br><br>{{ round($item->female_clicks_count / max($item->female_views_count, 1) * 100, 1) }}%
                    </p>
                    {{-- 성별 미상 --}}
                    <p class="flex-center" style="flex: 1">
                        {{ $item->no_gender_clicks_count }} / {{ $item->no_gender_views_count }}
                        <br><br>{{ round($item->no_gender_clicks_count / max($item->no_gender_views_count, 1) * 100, 1) }}%
                    </p>

                    {{-- 연령 통계 --}}
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_10_clicks_count }} / {{ $item->age_10_views_count }}
                        <br><br>{{ round($item->age_10_clicks_count / max($item->age_10_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_20_clicks_count }} / {{ $item->age_20_views_count }}
                        <br><br>{{ round($item->age_20_clicks_count / max($item->age_20_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_30_clicks_count }} / {{ $item->age_30_views_count }}
                        <br><br>{{ round($item->age_30_clicks_count / max($item->age_30_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_40_clicks_count }} / {{ $item->age_40_views_count }}
                        <br><br>{{ round($item->age_40_clicks_count / max($item->age_40_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_50_clicks_count }} / {{ $item->age_50_views_count }}
                        <br><br>{{ round($item->age_50_clicks_count / max($item->age_50_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_others_clicks_count }} / {{ $item->age_others_views_count }}
                        <br><br>{{ round($item->age_others_clicks_count / max($item->age_others_views_count, 1) * 100, 1) }}%
                    </p>
                    <p class="flex-center" style="flex: 1">
                        {{ $item->age_unknown_clicks_count }} / {{ $item->age_unknown_views_count }}
                        <br><br>{{ round($item->age_unknown_clicks_count / max($item->age_unknown_views_count, 1) * 100, 1) }}%
                    </p>
                </div>
            </div>
        @empty
            <p class="flex-center" style="flex: 5">데이터가 없습니다.</p>
        @endforelse
    </div>
@endsection
