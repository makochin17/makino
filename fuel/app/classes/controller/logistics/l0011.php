<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Logistics\L0010;
use \Model\Logistics\L0011;

class Controller_Logistics_L0011 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // ページネーション
    private $pagenation_config = array(
        'uri_segment' 	=> 'p',
    	'num_links' 	=> 2,
    	'per_page' 		=> 50,
    	'name' 			=> 'default',
    	'show_first' 	=> true,
    	'show_last' 	=> true,
    );

    // タイヤ種別リスト
    private $tire_kubun_list            = array();
    // 入庫フラグリスト
    private $receipt_flg_list           = array();
    // 出庫フラグリスト
    private $delivery_flg_list          = array();
    // 出庫指示フラグリスト
    private $delivery_schedule_flg_list = array();
    // 完了フラグリスト
    private $complete_flg_list          = array();
    // 保管場所リスト
    private $location_list              = array();
    // YES/NOリスト
    private $yes_no_list                = array();

    // ユーザ情報
    private $user_authority             = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'chlogisticsid':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '入庫登録';
        $cnf['page_id']                     = '[L0011]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '入庫登録';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

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
            // 'jquery_ui/jquery.ui.core.css',
            // 'jquery_ui/jquery.ui.datepicker.css',
            // 'jquery_ui/jquery.ui.theme.css',
            'common/jquery.jqplot.css',
            'common/jqModal.css'
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'common/style.css',
            'common/modal.css',
            'logistics/l0011.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'logistics/l0011.js',
            'logistics/l0011_form.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // タイヤ種別リスト
        $this->tire_kubun_list              = GenerateList::getTireKubunList(true);
        // 入庫フラグリスト
        $this->receipt_flg_list             = GenerateList::getReceiptFlgList(true);
        // 出庫フラグリスト
        $this->delivery_flg_list            = GenerateList::getDeliveryFlgList(true);
        // 出庫指示フラグリスト
        $this->delivery_schedule_flg_list   = GenerateList::getDeliveryScheduleFlgList(true);
        // 完了フラグリスト
        $this->complete_flg_list            = GenerateList::getCompleteFlgList(true);
        // 保管場所リスト
        $this->location_list                = GenerateList::getLocationList(true, L0010::$db);
        // YES/NOリスト
        $this->yes_no_list                  = GenerateList::getYesnoFlgList(false);

        // ユーザ権限取得
        $this->user_authority               = $auth_data['user_authority'];
	}

	public function before() {
		parent::before();
        // ログインチェック
        if(!Auth::check()) {
            Response::redirect(\Uri::base(false));
        }

        // 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0010::getSearchCustomer($code, L0010::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0010::getSearchCarByCode($code, L0010::$db)) {
                $conditions['car_id']           = $result['car_id'];
                if (Session::get('select_car_mode') == 'num') {
                    $conditions['car_code']     = $result['car_code'];
                } elseif (Session::get('select_car_mode') == 'name') {
                    $conditions['car_name']     = $result['car_name'];
                } else {
                    $conditions['car_code']     = $result['car_code'];
                    $conditions['car_name']     = $result['car_name'];
                }
                $conditions['customer_code']    = $result['customer_code'];
                $conditions['customer_name']    = $result['customer_name'];
                $conditions['owner_name']       = $result['owner_name'];
                $conditions['consumer_name']    = $result['consumer_name'];
            } else {
                $error_msg = Config::get('m_CAR011');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_location_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0011::getLocation($code, L0010::$db)) {
                $conditions['location_id']      = $result['location_id'];
            } else {
                $error_msg = Config::get('m_MI0025');
            }
            Session::delete('select_location_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        $validation = Validation::forge('valid_logistics');
        $validation->add_callable('myvalidation');

        // 入庫日チェック
        $validation->add('receipt_date', '入庫日')
            ->add_rule('required')
            ->add_rule('valid_date_format')
        ;
        // 入庫時間チェック
        $validation->add('receipt_time', '入庫時間')
            ->add_rule('required')
        ;
        if (!empty($conditions['logistics_id'])) {
            // // 出庫指示日チェック
            // $validation->add('delivery_schedule_date', '出庫予定日（出庫指示日）')
            //     ->add_rule('required')
            //     ->add_rule('valid_date_format')
            // ;
            // // 出庫指示時間チェック
            // $validation->add('delivery_schedule_time', '出庫予定時間（出庫指示時間）')
            //     ->add_rule('required')
            // ;
        }
        // タイヤ種別チェック
        $validation->add('tire_type', 'タイヤ種別')
            ->add_rule('required_select')
        ;
        // 登録番号/車両番号チェック
        $validation->add('car_code', '登録番号')
            ->add_rule('required')
        ;
        // お客様番号チェック
        $validation->add('customer_code', 'お客様番号')
            ->add_rule('required')
        ;
        // お客様名チェック
        $validation->add('customer_name', 'お客様')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 所有者名チェック
        $validation->add('owner_name', '所有者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 使用者名チェック
        $validation->add('consumer_name', '使用者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 車種名チェック
        $validation->add('car_name', '車種')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 保管場所チェック
        $validation->add('location_id', '保管場所')
            ->add_rule('required_select')
        ;

        // タイヤメーカーチェック
        if (!empty($conditions['tire_maker'])) {
            $validation->add('tire_maker', 'タイヤメーカー')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // タイヤ商品名チェック
        if (!empty($conditions['tire_product_name'])) {
            $validation->add('tire_product_name', 'タイヤ商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // タイヤサイズチェック
        if (!empty($conditions['tire_size'])) {
            $validation->add('tire_size', 'タイヤサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // タイヤパターンチェック
        if (!empty($conditions['tire_pattern'])) {
            $validation->add('tire_pattern', 'タイヤパターン')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // タイヤ製造年チェック
        if (!empty($conditions['tire_made_date'])) {
            $validation->add('tire_made_date', 'タイヤ製造年')
                ->add_rule('trim_max_lengths', 4)
            ;
        }
        // タイヤ残溝数１チェック
        if (!empty($conditions['tire_remaining_groove1'])) {
            $validation->add('tire_remaining_groove1', 'タイヤ残溝数１')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // タイヤ残溝数２チェック
        if (!empty($conditions['tire_remaining_groove2'])) {
            $validation->add('tire_remaining_groove2', 'タイヤ残溝数２')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // タイヤ残溝数３チェック
        if (!empty($conditions['tire_remaining_groove3'])) {
            $validation->add('tire_remaining_groove3', 'タイヤ残溝数３')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // タイヤ残溝数４チェック
        if (!empty($conditions['tire_remaining_groove4'])) {
            $validation->add('tire_remaining_groove4', 'タイヤ残溝数４')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // タイヤパンク、傷チェック
        if (!empty($conditions['tire_punk'])) {
            $validation->add('tire_punk', 'タイヤパンク')
                ->add_rule('trim_max_lengths', 250)
            ;
        }

        $validation->run();

        return $validation;
    }

    // 登録処理
    private function create_record($conditions, &$logistics_id) {

        $error_msg = null;
        try {
            DB::start_transaction(L0011::$db);

            $error_msg = L0011::create_record($conditions, $logistics_id, L0011::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(L0011::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(L0011::$db);
            Log::error($e->getMessage());
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_RE0004')."');</script>";
        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(L0011::$db);

            $error_msg = L0011::update_record($conditions, L0011::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(L0011::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(L0011::$db);
            Log::error($e->getMessage());
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_RE0005')."');</script>";
        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $cnt                = 0;
        $error_msg          = null;
        $init_flag          = false;
        $redirect_flag      = false;
        $data_location_list = array();
        $conditions         = L0011::getForms('logistics');
        $select_record      = Input::param('select_record', '');
        $logistics_id       = Input::param('logistics_id', '');
        $mode               = Input::param('mode', '');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('l0011_list');
        } elseif (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            Session::delete('l0011_list');
            \Response::redirect(\Uri::create('logistics/l0010'));
        } elseif (preg_match('/receipt/', $mode) && Input::method() == 'POST' && Security::check_token()) {
            // 一覧画面から遷移
            Session::delete('l0011_list');
            if (empty($logistics_id)) {
                // IDがない場合は空の状態で表示
                \Response::redirect(\Uri::create('logistics/l0011'));
            }
            // 入出庫テーブルよりデータを取得
            $conditions = L0011::setForms('logistics', $conditions, L0011::getLogisticsById($mode, $logistics_id, L0011::$db));
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = L0011::setForms('logistics', $conditions, Input::param());

            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_column = '';
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/receipt_date/', $key)) {
                        $error_column = '入庫日';
                    } elseif (preg_match('/receipt_time/', $key)) {
                        $error_column = '入庫時間';
                    // } elseif (preg_match('/delivery_schedule_date/', $key)) {
                    //     $error_column = '出庫予定日（出庫指示日）';
                    // } elseif (preg_match('/delivery_schedule_time/', $key)) {
                    //     $error_column = '出庫予定時間（出庫指示時間）';
                    } elseif (preg_match('/tire_type/', $key)) {
                        $error_column = 'タイヤ種別';
                    } elseif (preg_match('/car_code/', $key)) {
                        $error_column = '登録番号';
                    } elseif (preg_match('/customer_code/', $key)) {
                        $error_column = 'お客様番号';
                    } elseif (preg_match('/customer_name/', $key)) {
                        $error_column = 'お客様名';
                    } elseif (preg_match('/owner_name/', $key)) {
                        $error_column = '所有者';
                    } elseif (preg_match('/consumer_name/', $key)) {
                        $error_column = '使用者';
                    } elseif (preg_match('/car_name/', $key)) {
                        $error_column = '車種';
                    } elseif (preg_match('/location_id/', $key)) {
                        $error_column = '保管場所';
                    } elseif (preg_match('/tire_maker/', $key)) {
                        $error_column = 'タイヤメーカー';
                    } elseif (preg_match('/tire_product_name/', $key)) {
                        $error_column = 'タイヤ商品';
                    } elseif (preg_match('/tire_size/', $key)) {
                        $error_column = 'タイヤサイズ';
                    } elseif (preg_match('/tire_pattern/', $key)) {
                        $error_column = 'タイヤタイヤパターン';
                    } elseif (preg_match('/tire_made_date/', $key)) {
                        $error_column = 'タイヤ製造年';
                    } elseif (preg_match('/tire_remaining_groove1/', $key)) {
                        $error_column = 'タイヤ残溝数１';
                    } elseif (preg_match('/tire_remaining_groove2/', $key)) {
                        $error_column = 'タイヤ残溝数２';
                    } elseif (preg_match('/tire_remaining_groove3/', $key)) {
                        $error_column = 'タイヤ残溝数３';
                    } elseif (preg_match('/tire_remaining_groove4/', $key)) {
                        $error_column = 'タイヤ残溝数４';
                    } elseif (preg_match('/tire_punk/', $key)) {
                        $error_column = 'タイヤパンク';
                    }
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(L0011::$db);

                    switch ($conditions['mode']){
                        case '1':
                            // 登録処理
                            $error_msg = $this->create_record($conditions, $logistics_id);
                            if (!empty($logistics_id)) {
                                $conditions['logistics_id'] = $logistics_id;
                            }
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

                    if (empty($error_msg)) {
                        DB::commit_transaction(L0011::$db);
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    // $conditions = L0011::getForms();
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(L0011::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('l0011_list');
            // Session::set('l0011_list', $conditions);
        } else {
            $conditions = L0011::setForms('logistics', $conditions, Input::param());
            if (!empty($logistics_id) && preg_match('/receipt/', $mode)) {
                $conditions = L0011::setForms('logistics', $conditions, L0011::getLogisticsById($mode, $logistics_id, L0011::$db));
            }

            if ($cond = Session::get('l0011_list', array())) {
                $conditions = $cond;
                Session::delete('l0011_list');
            }
            if (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $conditions = L0011::setForms('logistics', $conditions, Input::param());
                $error_msg = $this->set_info($conditions);
            }
            Session::set('l0011_list', $conditions);
        }

        // 保管場所データを登録している場合は保管場所情報をプルダウンに追加
        if ($list = L0011::getLogisticsById(null, $logistics_id, L0011::$db)) {
            if (isset($this->location_list[$list['location_id']])) {
                $data_location_list = array($list['location_id'] => $this->location_list[$list['location_id']]);
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                      => \Uri::create(\Uri::create('logistics/l0010')),
                'upload_url'                    => \Uri::create('file/fileupload'),
                'check_url'                     => \Uri::create('logistics/l0011/chlogisticsid'),
                'current_url'                   => \Uri::create('logistics/l0011'),

                'data'                          => $conditions,
                'docroot'                       => DOCROOT,
                'logistics_id'                  => $logistics_id,

                // 社員情報
                'userinfo'                      => AuthConfig::getAuthConfig('all'),
                // タイヤ種別リスト
                'tire_kubun_list'               => $this->tire_kubun_list,
                // 入庫フラグリスト
                'receipt_flg_list'              => $this->receipt_flg_list,
                // 出庫フラグリスト
                'delivery_flg_list'             => $this->delivery_flg_list,
                // 出庫指示フラグリスト
                'delivery_schedule_flg_list'    => $this->delivery_schedule_flg_list,
                // 完了フラグリスト
                'complete_flg_list'             => $this->complete_flg_list,
                // 保管場所リスト
                'location_list'                 => $this->location_list,
                // 保管場所未使用リスト(プルダウン用)
                'location_combo_list'           => array('' => '-') + $data_location_list + L0010::getLocationList('non', $this->location_list, L0010::$db),
                // YES/NOリスト
                'yes_no_list'                   => $this->yes_no_list,

                'error_message'                 => $error_msg,
                'redirect_flag'                 => $redirect_flag
            )
        );

    }

    /**
    * 入出庫IDチェック
    **/
    public function action_chlogisticsid() {

        $res            = 1;
        $logistics_id   = Input::param('logistics_id', '');

        if (!L0011::getLogisticsById(null, $logistics_id, L0011::$db)) {
            $res    = 0;
        }

        $this->response->set_header('Access-Control-Allow-Origin', '*');
        return $this->response($res);
    }

}
