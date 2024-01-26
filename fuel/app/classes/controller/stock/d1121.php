<?php
/**
 * 入出庫情報登録画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Stock\D1121;
use \Model\Allocation\D1011;

class Controller_Stock_D1121 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 課リスト
    private $division_list          = array();
    // 単位リスト
    private $unit_list              = array();
    // 売上ステータスリスト
    private $sales_status_list      = array();
    // 入出庫区分リスト
    private $stock_change_list      = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'detail':
            case 'carrying':
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
        $cnf['header_title']                = '入出庫情報登録';
        $cnf['page_id']                     = '[D1121]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '入出庫情報登録';
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
            'common/modal.css'
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'stock/d1121.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;


        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, D1121::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(false, D1121::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 入出庫区分
        $this->stock_change_list        = GenerateList::getStockChangeCategoryList(false, D1121::$db);

        // ユーザ権限取得
        $this->user_authority           = $auth_data['user_authority'];

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
    private function set_info(&$conditions, $list_no) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = D1121::getSearchClient($code, D1121::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code_list = Session::get('select_stock_change_code')) {
            // 入出庫履歴の検索にてレコード選択された場合
            $stock_change_code_list = explode(",", $code_list);
            $stock_change_code_count = 0;
            if (is_countable($stock_change_code_list)){
                $stock_change_code_count = count($stock_change_code_list);
            }
            for($i = 0; $i < $stock_change_code_count; $i++){
                if ($result = D1121::getStockChange($stock_change_code_list[$i], D1121::$db)) {
                    $list_no = $i;
                    $conditions['list'][$list_no]['sales_status']        = $result['sales_status'];
                    $conditions['list'][$list_no]['destination_date']    = $result['destination_date'];
                    $conditions['list'][$list_no]['stock_change_code']   = $result['stock_change_code'];
                    $conditions['list'][$list_no]['destination']         = $result['destination'];
                    $conditions['list'][$list_no]['volume']              = $result['volume'];
                    $conditions['list'][$list_no]['fee']                 = $result['fee'];
                    $conditions['list'][$list_no]['remarks']             = $result['remarks'];
                } else {
                    $error_msg = Config::get('m_DW0032');
                }
            }
            Session::delete('select_stock_change_code');
        } elseif ($code_list = Session::get('select_dispatch_code')) {
            // 配車履歴の検索にてレコード選択された場合
            $dispatch_code_list = explode(",", $code_list);
            $dispatch_code_count = 0;
            if (is_countable($dispatch_code_list)){
                $dispatch_code_count = count($dispatch_code_list);
            }
            for($i = 0; $i < $dispatch_code_count; $i++){
                if ($result = D1011::getDispatchShare($dispatch_code_list[$i], D1121::$db)) {
                    $list_no = $i;
                    $conditions['list'][$list_no]['sales_status']           = $result['sales_status'];
                    $conditions['list'][$list_no]['volume']                 = $result['volume'];
                    
                    switch ($result['delivery_code']){
                            case '01':
                                // 配送
                                $conditions['list'][$list_no]['destination_date']    = $result['delivery_date'];
                                $conditions['list'][$list_no]['stock_change_code']   = '2';
                                $conditions['list'][$list_no]['destination']         = $result['delivery_place'];
                                break;
                            case '02':
                                // 引取
                                $conditions['list'][$list_no]['destination_date']    = $result['pickup_date'];
                                $conditions['list'][$list_no]['stock_change_code']   = '1';
                                $conditions['list'][$list_no]['destination']         = $result['pickup_place'];
                                break;
                            case '03':
                                // 返品
                                if (!empty($result['delivery_date'])) {
                                    $conditions['list'][$list_no]['destination_date']    = $result['delivery_date'];
                                    $conditions['list'][$list_no]['stock_change_code']   = '2';
                                    $conditions['list'][$list_no]['destination']         = $result['delivery_place'];
                                } elseif (!empty($result['pickup_date'])) {
                                    $conditions['list'][$list_no]['destination_date']    = $result['pickup_date'];
                                    $conditions['list'][$list_no]['stock_change_code']   = '1';
                                    $conditions['list'][$list_no]['destination']         = $result['pickup_place'];
                                }
                                break;
                        }
                } else {
                    $error_msg = Config::get('m_DW0001');
                }
            }
            Session::delete('select_dispatch_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;
        $item = D1121::getValidateItems();
        
        // 入力チェック
        foreach ($conditions['list'] as $key => $val) {
            // ２レコード目以降で処理区分が更新または削除の場合はスルー
            if ($key > 0 && $conditions['processing_division'] != 1) {
                continue;
            }
            // バリデーション対象チェック
            // ２レコード目以降で指定項目が全て未入力の場合はスルー
            if (!D1121::chkStockChangeDataNull($val) && $key > 0) {
                continue;
            }
            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');

            // 日付チェック
            $validation->add('list['.$key.'][destination_date]', $item['destination_date']['name'])
                ->add_rule('required')
                ->add_rule('valid_date_format');
            // 運行先チェック
            $validation->add('list['.$key.'][destination]', $item['destination']['name'])
                ->add_rule('trim_max_lengths', $item['destination']['max_lengths']);
            // 料金チェック
            $validation->add('list['.$key.'][fee]', $item['fee']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['fee']['max_lengths'])
                ->add_rule('is_numeric');
            // 数量チェック
            $validation->add('list['.$key.'][volume]', $item['volume']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['volume']['max_lengths'] + 3)
                ->add_rule('is_numeric_decimal', 6, true);
            // 備考チェック
            $validation->add('list['.$key.'][remarks]', $item['remarks']['name'])
                ->add_rule('trim_max_lengths', $item['remarks']['max_lengths']);
            $validation->run();
        }
        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg = null;

        // レコード登録
        $error_msg = D1121::create_record($conditions, D1121::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

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
        $conditions         = D1121::getForms('stock_change');
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $select_cancel      = Session::get('select_cancel');
        $stock_number       = Input::param('stock_number');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d1121_list');
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = D1121::setForms('stock_change', $conditions, Input::param());
            
            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_item = '';
                $item = D1121::getValidateItems();
                
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/destination_date/', $key)) {
                        $error_item = 'destination_date';
                    } elseif (preg_match('/destination/', $key)) {
                        $error_item = 'destination';
                    } elseif (preg_match('/volume/', $key)) {
                        $error_item = 'volume';
                    } elseif (preg_match('/fee/', $key)) {
                        $error_item = 'fee';
                    } elseif (preg_match('/remarks/', $key)) {
                        $error_item = 'remarks';
                    }
                    $error_column = $item[$error_item]['name'];
                    $column_length = $item[$error_item]['max_lengths'];
                    
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                        $error_msg = str_replace('xxxxx',$column_length,$error_msg);
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(D1121::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 指定項目が全て未入力の場合はスルー
                        if (!D1121::chkStockChangeDataNull($val)) {
                            continue;
                        }

                        $val['stock_number'] = $conditions['stock_number'];
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                $error_msg = $this->create_record($val);
                                break;
                            case '2':
                                break;
                            case '3':
                                break;
                        }
                    }
                    if (empty($error_msg)) {
                        DB::commit_transaction(D1121::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0031')."');</script>";
                                break;
                            case '2':
                                break;
                            case '3':
                                break;
                        }
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = D1121::getForms();
                    Session::delete('d1121_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(D1121::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d1121_list');
            Session::set('d1121_list', $conditions);
        } else {
            $conditions = D1121::setForms('stock_change', $conditions, Input::param());
            if ($cond = Session::get('d1121_list', array())) {
                $conditions = $cond;
                Session::delete('d1121_list');
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);
            }
            Session::delete('select_client_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('d1121_list', $conditions);
        }
        //在庫データ取得
        $stock_data = D1121::getSearchStock($stock_number, D1121::$db);

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('stock/d1120')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'stock_number'              => $stock_number,
                'stock'                     => $stock_data,
                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'unit_list'                 => $this->unit_list,
                'sales_status_list'         => $this->sales_status_list,
                'stock_change_list'         => $this->stock_change_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

}
