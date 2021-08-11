<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FeedController extends Controller
{
    public function index(): array
    {
        //
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request): array
    {
        /*$user_id = token()->uid;

        $content = $request->get('content');
        $files = $request->file('files');

        foreach ($files as $file) {
            if (str_starts_with($file->getMimeType(), 'image/')) {
                upload_image($file, "Image/SNS/");
            } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                continue;
            } else {
                continue;
            }
        }*/
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

    public function destroy($id): array
    {
        //

    }

    public function feed_upload(Request $request): array
    {
        $ftp_server = 'cyld20183.speedgabia.com'; //호스팅 서버 주소
        $ftp_user_name = 'cyld20183';     //아이디
        $ftp_user_pass = 'teamcyld2018!';     //암호
        $port = '21';
        $uid = token()->uid;
        $content = $request->get('content'); // 피드내용
        $today = date("Y-m-d H:i:s");

        try {
            DB::beginTransaction();
            $data = User::where('id', $uid)->first();
            if (isset($data)) {
                $user_data = [];
                $insertFeed = DB::insert('insert into feeds(user_id, content, created_at, updated_at) values(? , ?, ?, ? );'
                    , array($uid, $content, $today, $today));
                DB::commit();

            } else {
                DB::rollBack();
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

        // 34_1628474131.jpg uny715.mp4

        $max_id = Feed::where('user_id', $uid)
            ->select([DB::raw('max(feeds.id) as maxid ')])
            ->get('maxid ');
        $feed_id = $max_id[0]->maxid;

        foreach ($_FILES['files']['name'] as $f => $name) {
            // $file_name ='video.mp4';
            // $file_type = "video/mp4";
            // $file_name ='34_1628474131.jpg';
            // $file_type = "image/jpg";
            // $file_tmp_name = 'C:\Users\snipe\Downloads\34_1628474131.jpg';//; $_FILES['files']['tmp_name'][$f]; // 임시디렉토리에 저장된 파일
            // $file_tmp_name_thumb = 'C:\Users\snipe\Downloads\34_1628474131.jpg';//$_FILES['files']['tmp_name'][$f];

            $file_name = $_FILES['files']['name'][$f];
            $file_type = $_FILES['files']['type'][$f];
            $file_tmp_name = $_FILES['files']['tmp_name'][$f]; // 임시디렉토리에 저장된 파일
            $file_tmp_name_thumb = $_FILES['files']['tmp_name'][$f];

            $fileType = explode('/', $file_type);

            if ($fileType[0] == "image") {
                $file_tmp_name = compress($file_tmp_name, $file_tmp_name, 60);
                $file_tmp_name_thumb = compress($file_tmp_name_thumb, $file_tmp_name_thumb, 60);

                if ($fileType[1] == 'heic' || $fileType[1] == 'HEIC') {

                } else {
                    image_to_square($file_tmp_name, $file_tmp_name, 640); // 이미지 커팅
                } // }
            }

            $circlinExt = explode('.', $file_name);
            if ($fileType[1] == 'quicktime' && $circlinExt[0] == 'video') {
                $fileType[1] = 'mov';
            }
            if ($fileType[1] == 'quicktime' && $circlinExt[0] == 'image') {
                $fileType[0] = 'image';
            }

            $conn_id = ftp_connect($ftp_server, $port);
            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); //성공 시 TRUE를, 실패 시 FALSE를 반환합니다. If login fails, PHP will also throw a warning.
            $uploaddir = '/Image/SNS/';
            $uploaddirNew = "/Image/SNS/" . $uid . "/";
            $serverfile = $uploaddirNew . $uid . "_" . $f . "_" . strtotime($today) . "." . $fileType[1]; //업로드 될 폴더 와 파일명
            $dbProfile = "https://" . $ftp_server . $serverfile;

            $serverfile2 = $uploaddirNew . $uid . "_thumb" . $f . "_" . strtotime($today) . "." . $fileType[1]; //업로드 될 폴더 와 파일명
            $dbProfile2 = "https://" . $ftp_server . $serverfile2;
            $testFileType = $fileType[0] . '/' . $fileType[1];

            ftp_pasv($conn_id, true);
            if (ftp_nlist($conn_id, $uploaddirNew) == false) {
                ftp_mkdir($conn_id, $uploaddirNew);
            }


            if (ftp_put($conn_id, $serverfile, $file_tmp_name, FTP_BINARY)) {
                if ($fileType[1] == 'heic' || $fileType[1] == 'HEIC') {

                } else {
                    if ($fileType[0] == 'video') {
                        uploadVideoResizing($uid, $ftp_server, $ftp_user_name, $ftp_user_pass, $dbProfile, $dbProfile2, $feed_id);
                    }
                }
                //  ftp_put($conn_id, $serverfile2, $file_tmp_name_thumb, FTP_BINARY);
                if ($fileType[1] == 'octet-stream') {
                    $fileType[0] = 'image';
                }

                try {
                    DB::beginTransaction();

                    $data = User::where('id', $uid)->first();
                    if (isset($data)) {
                        $user_data = [];
                        $image_files = DB::insert('insert into feed_images(  feed_id, type, image, created_at, updated_at) values(?, ? , ?, ?, ? );'
                            , array($feed_id, $fileType[0], $dbProfile, $today, $today));

                        DB::commit();

                    } else {
                        DB::rollBack();
                        return success([
                            'result' => false,
                            'reason' => 'not enough data',
                        ]);
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    return exceped($e);
                }

            }
        }

        //return array($feed_id);
        return success([
            'result' => true,
        ]);
    }
}
