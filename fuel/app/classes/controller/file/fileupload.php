<?php
use \Model\Init;
use \Model\Excel\Data;

class Controller_File_Fileupload extends Controller_Rest {

    // Uploadクラスの設定
    private $upload_config = array(
        'randomize'     => true,
        'overwrite'     => true,
        'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx')
    );

    public function action_index() {

        if(Input::method()=='POST'){
            // タイムアウトを一時的に解除
            ini_set('max_execution_time', 0);
            // 最大メモリー数を増幅
            ini_set('memory_limit', '2048M');
            // POSTサイズを増幅
            ini_set('post_max_size', '512M');
            // アップロードファイルサイズを増幅
            ini_set('upload_max_filesize', '1024M');

            // 初期設定
            $car_id             = Input::post('car_id', '');
            $id                 = Input::post('file_id', '');
            $folder             = Input::post('folder', '');

            // フォルダ設定
            if (!empty($car_id)) {
                if(!file_exists(DOCROOT.$folder.'/'.$car_id)) {
                    \File::create_dir(DOCROOT.$folder, $car_id, 0775);
                }
                $folder = $folder.'/'.$car_id;
            }
            $config             = $this->upload_config;
            $config['path']     = DOCROOT.$folder;

            // var_dump($car_id);
            // var_dump($folder);
            // var_dump($id);
            // var_dump($config);
            // exit;

            // 既に存在しているファイルを削除
            if ($handle = opendir($config['path'])) {
                /* ディレクトリをループする際の正しい方法です */
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    if (preg_match('/'.$id.'.*$/', $file)) {
                        if(file_exists($config['path'].'/'.$file)) {
                            if (!empty($car_id)) {
                                if(!file_exists($config['path'].'/old')) {
                                    \File::create_dir($config['path'], 'old', 0775);
                                }
                                // 会員保管ディレクトリに移動する
                                if (rename($config['path'].'/'.$file, $config['path'].'/old/'.date('Ymd').'_'.$file)) {
                                    // 移動が成功したら表示される
                                    // var_dump('移動しました');
                                } else {
                                    // 移動に失敗したら表示される
                                    // var_dump('移動できない');
                                }

                            } else {
                                File::delete($config['path'].'/'.$file);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            // ファイルアップロード
            \Upload::process($config);
            if (\Upload::is_valid()) {
                Upload::save(0);
                $files = \Upload::get_files();
                if (isset($files[0]) && $files[0]['file'] != '') {
                    $img_path       = $files[0]["saved_to"].$files[0]["name"];
                    $saved_to_path  = $files[0]["saved_to"];
                    $extension      = strtolower($files[0]["extension"]);
                    File::rename($files[0]["saved_to"].$files[0]["saved_as"], $saved_to_path.$id.'.'.$extension);
               }
            }
        }
        // \Response::redirect(\Uri::base(false).$this->category_page);
        return $this->response(false);

    }

    public function action_multiple() {

        if(Input::method()=='POST'){
            // タイムアウトを一時的に解除
            ini_set('max_execution_time', 0);
            // 最大メモリー数を増幅
            ini_set('memory_limit', '2048M');
            // POSTサイズを増幅
            ini_set('post_max_size', '512M');
            // アップロードファイルサイズを増幅
            ini_set('upload_max_filesize', '1024M');

            $key_id             = Input::post('key_id', '');
            $id                 = Input::post('file_id', '');
            $folder             = Input::post('folder', '');
            // フォルダ設定
            if (!empty($key_id)) {
                if (empty($id)) {
                    $id = $key_id;
                }
                if(!file_exists(DOCROOT.$folder.'/'.$key_id)) {
                    \File::create_dir(DOCROOT.$folder, $key_id, 0775);
                }
                $folder = $folder.'/'.$key_id;
            }
            // 初期設定
            $config             = $this->upload_config;
            $config['path']     = DOCROOT.$folder;

            // var_dump($id);
            // var_dump($filename);
            // var_dump($config);
            // exit;

            // 既に存在しているファイルを削除
            if ($handle = opendir($config['path'])) {
                /* ディレクトリをループする際の正しい方法です */
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..' || $file == 'old') {
                        continue;
                    }
                    if(file_exists($config['path'].'/'.$file)) {
                        if (!empty($key_id)) {
                            if(!file_exists($config['path'].'/old')) {
                                \File::create_dir($config['path'], 'old', 0775);
                            }
                            // 研修保管ディレクトリに移動する
                            if (rename($config['path'].'/'.$file, $config['path'].'/old/'.date('Ymd').'_'.$file)) {
                                // 移動が成功したら表示される
                                // var_dump('移動しました');
                            } else {
                                // 移動に失敗したら表示される
                                // var_dump('移動できない');
                            }

                        } else {
                            File::delete($config['path'].'/'.$file);
                        }
                    }
                }
                closedir($handle);
            }
            // ファイルアップロード
            \Upload::process($config);
            if (\Upload::is_valid()) {
                Upload::save();
                $files = \Upload::get_files();
                foreach ($files as $key => $val) {
                    if (isset($val['saved_to'])) {
                        $saved_to_path = $val['saved_to'];
                        File::rename($val["saved_to"].$val["saved_as"], $saved_to_path.'/'.$val["name"]);
                    }
                }
            }
        }
        // \Response::redirect(\Uri::base(false).$this->category_page);
        return $this->response(false);

    }

}
