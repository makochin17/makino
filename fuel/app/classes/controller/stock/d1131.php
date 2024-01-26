<?php
/**
 * 保管料情報登録画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Stock\D1131;

class Controller_Stock_D1131 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 課リスト
    private $division_list              = array();
    // 売上ステータスリスト
    private $sales_status_list          = array();
    // 保管料区分リスト
    private $storage_fee_list           = array();
    // 単位リスト
    private $unit_list                  = array();
    // 端数処理リスト
    private $rounding_list              = array();
    // 登録者リスト
    private $create_user_list           = array();
    // ユーザ情報
    private $user_authority             = null;

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
        $cnf['header_title']                = '保管料情報登録';
        $cnf['page_id']                     = '[D1131]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '保管料情報登録';
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
            'stock/d1131.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, D1131::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 保管料区分
        $this->storage_fee_list         = GenerateList::getStorageFeeCategoryList(false, D1131::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(false, D1131::$db);
        // 端数処理リスト取得
        $this->rounding_list            = GenerateList::getRoundingList(false);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(true, D1131::$db);
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
            if ($result = D1131::getSearchClient($code, D1131::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code_list = Session::get('select_storage_fee_number')) {
            // 配車履歴の検索にてレコード選択された場合
            $storage_fee_number_list = explode(",", $code_list);
            $storage_fee_number_count = 0;
            if (is_countable($storage_fee_number_list)){
                $storage_fee_number_count = count($storage_fee_number_list);
            }
            for($i = 0; $i < $storage_fee_number_count; $i++){
                if ($result = D1131::getStorageFee($storage_fee_number_list[$i], D1131::$db)) {
                    $list_no = $i;
                    $conditions['storage_fee_number']                       = $result['storage_fee_number'];
                    $conditions['division_code']                            = $result['division_code'];
                    $conditions['list'][$list_no]['client_code']            = $result['client_code'];
                    $conditions['list'][$list_no]['client_name']            = $result['client_name'];
                    $conditions['list'][$list_no]['closing_date']           = $result['closing_date'];
                    $conditions['list'][$list_no]['storage_fee_code']       = $result['storage_fee_code'];
                    $conditions['list'][$list_no]['storage_fee']            = number_format($result['storage_fee']);
                    $conditions['list'][$list_no]['unit_price']             = number_format($result['unit_price'], 2);
                    $conditions['list'][$list_no]['volume']                 = $result['volume'];
                    $conditions['list'][$list_no]['unit_code']              = $result['unit_code'];
                    $conditions['list'][$list_no]['rounding_code']          = $result['rounding_code'];
                    $conditions['list'][$list_no]['storage_location']       = $result['storage_location'];
                    $conditions['list'][$list_no]['product_name']           = $result['product_name'];
                    $conditions['list'][$list_no]['maker_name']             = $result['maker_name'];
                    $conditions['list'][$list_no]['remarks']                = $result['remarks'];
                    $conditions['list'][$list_no]['sales_status']           = $result['sales_status'];
                } else {
                    $error_msg = Config::get('m_DW0037');
                }
            }
            Session::delete('select_storage_fee_number');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        foreach ($conditions['list'] as $key => $val) {
            // ２レコード目以降で処理区分が更新または削除の場合はスルー
            if ($key > 0 && $conditions['processing_division'] != 1) {
                continue;
            }
            // バリデーション対象チェック
            // ２レコード目以降で指定項目が全て未入力の場合はスルー
            if (!D1131::chkStorageFeeDataNull($val) && $key > 0) {
                continue;
            }

            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');
            $item = D1131::getValidateItems();
            // 保管料番号チェック
            $validation->add('list['.$key.'][storage_fee_number]', $item['storage_fee_number']['name'])
                ->add_rule('trim_max_lengths', $item['storage_fee_number']['max_lengths'])
                ->add_rule('is_numeric');
            // 締日チェック
            $validation->add('list['.$key.'][closing_date]', $item['closing_date']['name'])
                ->add_rule('required')
                ->add_rule('valid_date_format');
            // 得意先チェック
            $validation->add('list['.$key.'][client_code]', $item['client_code']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
                ->add_rule('is_numeric');
            // 保管場所チェック
            $validation->add('list['.$key.'][storage_location]', $item['storage_location']['name'])
                ->add_rule('trim_max_lengths', $item['storage_location']['max_lengths']);
            // 単価チェック
            $validation->add('list['.$key.'][unit_price]', $item['unit_price']['name'])
                ->add_rule('trim_max_lengths_int', $item['unit_price']['max_lengths'])
                ->add_rule('is_numeric_decimal', 2, true);
            // 数量チェック
            $validation->add('list['.$key.'][volume]', $item['volume']['name'])
                ->add_rule('trim_max_lengths_int', $item['volume']['max_lengths'])
                ->add_rule('is_numeric_decimal', 6, true);
            // 保管料チェック
            $total_fee = ((float)str_replace(',', '', $val['unit_price']) * (float)str_replace(',', '', $val['volume']));
            switch ($val['rounding_code']) {
                case '1':   // 四捨五入
                    $total_fee = round($total_fee);
                    break;
                case '2':   // 切り上げ
                    $total_fee = ceil($total_fee);
                    break;
                case '3':   // 切り捨て
                    $total_fee = floor($total_fee);
                    break;
            }
            $validation->add('list['.$key.'][storage_fee]', $item['storage_fee']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['storage_fee']['max_lengths'])
                ->add_rule('amount_check', $total_fee)
                ->add_rule('is_numeric_decimal', 2, true);
            // 商品名チェック
            $validation->add('list['.$key.'][product_name]', $item['product_name']['name'])
                ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
            // メーカー名チェック
            $validation->add('list['.$key.'][maker_name]', $item['maker_name']['name'])
                ->add_rule('trim_max_lengths', $item['maker_name']['max_lengths']);
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
        $error_msg = D1131::create_record($conditions, D1131::$db);
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
        $conditions         = D1131::getForms('storagefee');
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $select_cancel      = Session::get('select_cancel');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d1131_list');
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = D1131::setForms('storagefee', $conditions, Input::param());

            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_column = '';

                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/storage_fee_number/', $key)) {
                        $error_item     = 'storage_fee_number';
                        $error_column   = '保管料番号';
                    } elseif (preg_match('/closing_date/', $key)) {
                        $error_item     = 'closing_date';
                        $error_column   = '締日';
                    } elseif (preg_match('/client_code/', $key)) {
                        $error_item     = 'client_code';
                        $error_column   = '得意先No';
                    } elseif (preg_match('/storage_location/', $key)) {
                        $error_item     = 'storage_location';
                        $error_column   = '保管場所';
                    } elseif (preg_match('/storage_fee/', $key)) {
                        $error_item     = 'storage_fee';
                        $error_column   = '保管料';
                    } elseif (preg_match('/unit_price/', $key)) {
                        $error_item     = 'unit_price';
                        $error_column   = '単価';
                    } elseif (preg_match('/volume/', $key)) {
                        $error_item     = 'volume';
                        $error_column   = '数量';
                    } elseif (preg_match('/product_name/', $key)) {
                        $error_item     = 'product_name';
                        $error_column   = '商品名';
                    } elseif (preg_match('/maker_name/', $key)) {
                        $error_item     = 'maker_name';
                        $error_column   = 'メーカー名';
                    } elseif (preg_match('/remarks/', $key)) {
                        $error_item     = 'remarks';
                        $error_column   = '備考';
                    }

                    $item           = D1131::getValidateItems();
                    $column_length  = $item[$error_item]['max_lengths'];

                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths' || $validation->error()[$key]->rule == 'trim_max_lengths_int') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                        $error_msg = str_replace('xxxxx',$column_length,$error_msg);
                    } elseif ($validation->error()[$key]->rule == 'amount_check') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0034'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 保管料区分「定額」のレコード重複チェック
                foreach ($conditions['list'] as $key => $val) {

                    // ２レコード目以降で指定項目が全て未入力の場合はスルー
                    if (!D1131::chkStorageFeeDataNull($val) && $key > 0) {
                        continue;
                    }
                    // 保管料区分が定額のみチェック
                    if ($val['storage_fee_code'] == '1') {
                        if ($result = D1131::checkStorageFee(null, $val['closing_date'], $val['client_code'], D1131::$db)) {
                            $error_msg = Config::get('m_DW0035');
                        }
                    }
                }
            }

            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(D1131::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 指定項目が全て未入力の場合はスルー
                        if (!D1131::chkStorageFeeDataNull($val)) {
                            continue;
                        }

                        $val['division_code'] = $conditions['division_code'];
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
                        DB::commit_transaction(D1131::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0041')."');</script>";
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
                    $conditions = D1131::getForms();
                    Session::delete('d1131_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(D1131::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d1131_list');
            Session::set('d1131_list', $conditions);
        } else {
            $conditions = D1131::setForms('storagefee', $conditions, Input::param());
            if ($cond = Session::get('d1131_list', array())) {
                $conditions = $cond;
                Session::delete('d1131_list');
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);
            }
            Session::delete('select_client_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('d1131_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('stock/d1130')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'sales_status_list'         => $this->sales_status_list,
                'storage_fee_list'          => $this->storage_fee_list,
                'unit_list'                 => $this->unit_list,
                'rounding_list'             => $this->rounding_list,
                'create_user_list'          => $this->create_user_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

}
