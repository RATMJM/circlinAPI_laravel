<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PopularPlaceController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $category_id = $request->get('category_id');
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);

        $data = Place::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->join('missions', function ($query) {
                $query->on('missions.place_id', 'places.id')->whereNull('missions.deleted_at');
            })
            ->select([
                'places.id', 'places.address', 'places.title', 'places.description',
                'places.image', 'places.url',
                DB::raw("COUNT(distinct missions.id) as missions_count"),
            ])
            ->groupBy('places.id')
            ->orderBy('missions_count', 'desc')
            ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($data)) {
            function missions($place_id, $category_id = null)
            {
                return Mission::where('place_id', $place_id)
                    ->when($category_id, function ($query, $category_id) {
                        $query->where('missions.mission_category_id', $category_id);
                    })
                    ->join('users', 'users.id', 'missions.user_id')
                    ->select([
                        'missions.place_id', 'missions.id', 'missions.title', 'missions.description', 'missions.thumbnail_image',
                        'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                    ])
                    ->take(4);
            }

            $query = null;
            foreach ($data as $i => $item) {
                if ($query) {
                    $query = $query->union(missions($item->id, $category_id));
                } else {
                    $query = missions($item->id, $category_id);
                }
            }
            $query = $query->get();
            $keys = $data->pluck('id')->toArray();
            foreach ($query->groupBy('place_id') as $i => $item) {
                $data[array_search($i, $keys)]->missions = $item;
            }
        }

        return success([
            'result' => true,
            'places' => $data,
        ]);
    }
}
