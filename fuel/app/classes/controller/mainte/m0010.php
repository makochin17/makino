<?php
/**
 * 商品検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;

class Controller_Mainte_M0010 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 画面モード
    private $mode       = null;

    // Uploadクラスの設定
    private $upload_config = array(
        'randomize'     => true,
        'ext_whitelist' => array('xls', 'xlsx', 'tsv', 'csv'),
    );

    // 処理区分リスト
    private $processing_division_list   = array();
    // 課リスト
    private $division_list              = array();
    // 役職リスト
    private $position_list              = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '社員マスタメンテナンス';
        $cnf['page_id']                     = '[M0010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '社員マスタメンテナンス';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '社員マスタメンテナンス';

        if ($this->mode == 'reset') {
            $header                         = View::forge('header_logout');
        } else {
            $header                         = View::forge($this->header);
        }
        $head                               = View::forge($this->head);
        $tree                               = View::forge($this->tree);
        $sidemenu                           = View::forge($this->sidemenu);
        $footer                             = View::forge($this->footer);
        $head->title                        = $cnf['system_title'];
        $header->header_title               = $cnf['header_title'];
        $header->page_id                    = $cnf['page_id'];
        $tree->tree                         = $cnf['tree'];
        $tree->tree                         = '';
        $sidemenu->login_user_name          = $auth_data['full_name'];
        $sidemenu->copyright                = $cnf['copyright'];

        // テンプレートに定義するCSS・JS
        $ary_jquery_ui_css = array(
            ''
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'modal/dialog.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 処理区分リスト取得
        $this->processing_division_list = GenerateList::getProcessingDivisionList();
        // 課リスト
        $this->division_list            = GenerateList::getDivisionList(false, GenerateList::$db);
        // 役職リスト
        $this->position_list            = GenerateList::getPositionList(false, GenerateList::$db);
    }

	public function before() {
		parent::before();
		// ログインチェック
		if(!Auth::check()) {
			Response::redirect(\Uri::base(false));
		}

		// 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');;

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}

	private function validate_info($kind = 'del') {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 社員コードチェック
        $validation->add('member_code', '社員コード')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes'))
        ;
        if ($kind != 'del') {
            // 課チェック
            $validation->add('division_code', '課')
                ->add_rule('required')
            ;
            // 役職チェック
            $validation->add('position_code', '役職')
                ->add_rule('required')
            ;
            // 氏名チェック
            $validation->add('full_name', '氏名')
                ->add_rule('required')
            ;
            // ふりがなチェック
            $validation->add('name_furigana', 'ふりがな')
                ->add_rule('required')
            ;
            // 車両チェック
            $validation->add('car_code', '車両')
                // ->add_rule('required')
                ->add_rule('valid_strings', array('alpha', 'numeric'))
            ;
            // ドライバー名チェック
            $validation->add('driver_name', 'ドライバー名')
                // ->add_rule('required')
            ;
            // 電話番号チェック
            $validation->add('phone_number', '電話番号')
                ->add_rule('required')
                ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes'))
            ;
            // ユーザ名チェック
            $validation->add('user_id', 'ユーザ名')
                // ->add_rule('required')
                ->add_rule('min_length', 6)
                ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes', 'dots', 'commas', 'punctuation'))
            ;
            // ユーザ権限チェック
            $validation->add('user_authority', 'ユーザ権限')
                // ->add_rule('required')
            ;
        }
		$validation->run();
		return $validation;
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_car_code')) {
            // 検索にてレコード選択された場合
            $result = M0010::getCar($code, M0010::$db);
            if (count($result) > 0) {
                // レコード取得出来たら値をセット
                foreach ($result[0] as $key => $val) {
                    if ($key == 'car_code') {
                        $conditions['car_code'] = (!empty($val)) ? sprintf('%04d', $val):'';
                    }
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_car_code');
        }
        if ($code = Session::get('select_member_code')) {
            // 検索にてレコード選択された場合
            $result = M0010::getMember($code, M0010::$db);
            if (count($result) > 0) {
                // レコード取得出来たら値をセット
                foreach ($result[0] as $key => $val) {
                    // $conditions[$key] = $result[0][$key];
                    $conditions[$key] = $val;
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_member_code');
        }

        return $error_msg;
    }

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(M0010::$db);

            $error_msg = M0010::create_record($conditions, M0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

            if (!empty(M0010::$password)) {
                $end_msg = str_replace('XXXXX', M0010::$password, Config::get('m_MI0013'));
            } else {
                $end_msg = Config::get('m_MI0005');
            }
            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        try {
            DB::start_transaction(M0010::$db);

            $error_msg = M0010::update_record($conditions, M0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            
            if (!empty(M0010::$password)) {
                $end_msg = str_replace('XXXXX', M0010::$password, Config::get('m_MI0016'));
            } else {
                $end_msg = Config::get('m_MI0006');
            }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        return null;
    }

    // 削除処理
    private function delete_record($conditions) {

        try {
            DB::start_transaction(M0010::$db);

            $error_msg = M0010::delete_record($conditions, M0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        return null;
    }

    // ロックアウト解除処理
    private function unlock($conditions) {

        try {
            DB::start_transaction(M0010::$db);

            // レコード存在チェック
            $result = M0010::getMember($conditions['member_code'], M0010::$db);

            if (count($result) == 0) {
                return Config::get('m_MW0005');
            }

            // ユーザロックアウト解除
            $result = M0010::unlockMember($conditions['member_code'], M0010::$db);
            if (!$result) {
                Log::error(Config::get('m_ME0004')."[member_code:".$conditions['member_code']."]");
                return Config::get('m_ME0004');
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0009', Config::get('m_MI0009'), $conditions['user_id'], M0010::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            // return $e->getMessage();
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0009')."');</script>";
        return null;
    }

    // パスワード初期化処理
    private function passinitialize($conditions) {

        try {
            DB::start_transaction(M0010::$db);

            // レコード存在チェック
            $result = M0010::getMember($conditions['member_code'], M0010::$db);

            if (count($result) == 0) {
                return Config::get('m_MW0005');
            }

            // Authログインユーザ削除
            if (!AuthConfig::DeleteLoginUser($conditions['user_id'])) {
                return str_replace('XXXXX',$conditions['user_id'],Config::get('m_ME0002'));
            }

            // システム設定取得
            $password = M0010::getPasswordDefault(M0010::$db);

            // Authログインユーザ登録
            if (!AuthConfig::CreateLoginUser($conditions['user_id'], $password, $conditions)) {
                return str_replace('XXXXX',$conditions['user_id'],Config::get('m_ME0001'));
            }

            // パスワード初期化
            $result = M0010::initializePassword($conditions['member_code'], M0010::$db);
            if (!$result) {
                Log::error(Config::get('m_ME0004')."[member_code:".$conditions['member_code']."]");
                return Config::get('m_ME0004');
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0011', str_replace('初期化後パスワードは「XXXXX」です','',Config::get('m_MI0011')), $conditions['user_id'], M0010::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            // return $e->getMessage();
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".str_replace('XXXXX',$password,Config::get('m_MI0011'))."');</script>";
        return null;
    }

    // CSV読み込み
    private function csvGet($conditions) {

        // タイムアウトを一時的に解除
        ini_set('max_execution_time', 0);
        // 最大メモリー数を増幅
        ini_set('memory_limit', '2048M');

        // ファイルアップロード
        \Upload::process($this->upload_config);
        if (\Upload::is_valid()) {
            $files = \Upload::get_files();
            if (isset($files[0]) && $files[0]['file'] != '') {

                $excel_data    = Data::import($files[0]['file']);
                $excel_type    = $excel_data['excel_type'];
                $header        = $excel_data['header'];
                $body          = $excel_data['data'];
                $data          = array();
                if (empty($header)) {
                    return str_replace('XXXXX','見出し不備',Config::get('m_MW0001'));
                } else {
                    if (!empty($body)) {
                        foreach ($body as $val) {
                            $data[]    = array_combine($header, $val);
                        }
                    }
                    if (!empty($data)) {
                        return M0010::import($data, M0010::$db);
                    } else {
                        return str_replace('XXXXX','レコードなし',Config::get('m_MW0001'));
                    }
                }
            }
        } else {
            return str_replace('XXXXX','ファイル拡張子誤り',Config::get('m_MW0001'));
        }

        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
        	'member_code',
            'division_code',
            'position_code',
            'car_code',
            'full_name',
            'name_furigana',
            'driver_name',
            'phone_number',
            'user_id',
            'user_authority',
            'password_limit',
            'password_error_count',
            'lock_status',
            'start_date',
            'end_date',
            'processing_division',
        ), '');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('m0010_list');
        } elseif (!empty(Input::param('excel'))) {
            // エクセル出力ボタンが押下された場合の処理
            M0010::createTsv(M0010::$db);
            
        } elseif (!empty(Input::param('csv_download')) && Input::method() == 'POST' && Security::check_token()) {
            // CSVフォーマットボタンが押下された場合の処理
            \Response::redirect(\Uri::create('file/filedownload?type=m0010'));

        } elseif (!empty(Input::param('csv_capture')) && Input::method() == 'POST' && Security::check_token()) {

            $error_msg = $this->csvGet($conditions);
        } elseif (!empty(Input::param('passinitialize')) && Input::method() == 'POST' && Security::check_token()) {
            // パスワード初期化ボタンが押下された場合の処理
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            $error_msg = $this->passinitialize($conditions);

        } elseif (!empty(Input::param('unlock')) && Input::method() == 'POST' && Security::check_token()) {
            // ロックアウト解除ボタンが押下された場合の処理
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            $error_msg = $this->unlock($conditions);

        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力必須項目チェック
            $validation = $this->validate_info($conditions['processing_division']);
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'member_code':
                            $error_column = '社員コード';
                            break;
                        case 'division_code':
                            $error_column = '課';
                            break;
                        case 'position_code':
                            $error_column = '役職';
                            break;
                        case 'car_code':
                            $error_column = '車両';
                            break;
                        case 'full_name':
                            $error_column = '氏名';
                            break;
                        case 'name_furigana':
                            $error_column = 'ふりがな';
                            break;
                        case 'driver_name':
                            $error_column = 'ドライバー名';
                            break;
                        case 'phone_number':
                            $error_column = '電話番号';
                            break;
                        case 'user_id':
                            $error_column = 'ユーザ名';
                            break;
                        case 'user_authority':
                            $error_column = 'ユーザ権限';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings' || $validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } elseif ($validation->error()[$key]->rule == 'min_length') {
                        $error_msg = str_replace('xxxxx','6',str_replace('XXXXX',$error_column,Config::get('m_CW0016')));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }

            if (empty($error_msg)) {
                switch ($conditions['processing_division']){
                    case '1':
                        // 登録処理
                        $error_msg = $this->create_record($conditions);
                        break;
                    case '2':
                        // 更新処理
                        $error_msg = $this->update_record($conditions);
                        break;
                    case '3':
                        // 削除処理
                        $error_msg = $this->delete_record($conditions);
                        break;
                }
            }
                
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0010_list');
            Session::set('m0010_list', $conditions);
        } else {
            if ($cond = Session::get('m0010_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('m0010_list');
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                foreach ($conditions as $key => $val) {
                    $conditions[$key] = Input::param($key, ''); // 検索項目
                }
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
            }

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'             => $error_msg,
                'data'                      => $conditions,
                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'position_list'             => $this->position_list,
                'user_permission'           => M0010::permission(),
            )
        );
    }

}
