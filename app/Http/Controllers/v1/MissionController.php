<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    /**
     * 카테고리 별 미션 목록
     */
    public function index(): array
    {
        //
    }

    public function create(): array
    {
        //
    }

    public function store(Request $request): array
    {
        //
    }

    /**
     * 미션 상세
     */
    public function show($id): array
    {
        return success([
            'result' => true,
            'mission' => Mission::where('missions.id', $id)
                ->join('users', 'users.id', 'missions.user_id')
                ->select(['users.nickname', 'users.profile_image', 'missions.title', 'missions.description',
                    'missions.image_url'])
                ->first(),
        ]);
    }

    public function edit($id): array
    {
        //
    }

    public function update(Request $request, $id): array
    {
        //
    }

    public function destroy($id): array
    {
        //
    }
}
