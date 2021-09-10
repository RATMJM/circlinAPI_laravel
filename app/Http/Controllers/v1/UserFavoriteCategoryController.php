<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\UserFavoriteCategory;
use Exception;
use Illuminate\Http\Request;

class UserFavoriteCategoryController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'categories' => MissionCategory::whereHas('favorite_category', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->pluck('id'),
        ]);
    }

    public function create(): array
    {
        //
    }

    public function store(Request $request): array
    {
        try {
            $user_id = token()->uid;
            $intro = $request->get('intro');
            $category_id = $request->get('category_id');

            if (is_null($category_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            if (UserFavoriteCategory::where(['user_id' => $user_id, 'mission_category_id' => $category_id])->exists()) {
                return success(['result' => false, 'reason' => 'already following']);
            } else {
                $data = UserFavoriteCategory::create(['user_id' => $user_id, 'mission_category_id' => $category_id]);

                if ($intro) {
                    foreach (Mission::where(['mission_category_id' => $category_id, 'is_tutorial' => true])->pluck('id') as $mission) {
                        (new BookmarkController())->store($request, $mission);
                    }
                }

                if ($data) {
                    return success(['result' => true]);
                } else {
                    return success(['result' => false]);
                }
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public function show($id): array
    {
        //
    }

    public function edit($id): array
    {
        //
    }

    public function update(Request $request, $id): array
    {
        //
    }

    public function destroy(Request $request, $id): array
    {
        try {
            $user_id = token()->uid;
            $intro = $request->get('intro');

            if (is_null($id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $data = UserFavoriteCategory::where(['user_id' => $user_id, 'mission_category_id' => $id])->first();

            if ($intro) {
                foreach (Mission::where(['mission_category_id' => $id, 'is_tutorial' => true])->pluck('id') as $mission) {
                    (new BookmarkController())->destroy($mission);
                }
            }

            if ($data) {
                $result = $data->delete();
                return success(['result' => $result > 0]);
            } else {
                return success(['result' => false, 'reason' => 'not favorite']);
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }
}
