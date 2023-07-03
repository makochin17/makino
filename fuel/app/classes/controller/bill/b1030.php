<?php
/**
 * 請求情報雛形出力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Bill\B1030;

class Controller_Bill_B1030 extends Controller_Hybrid {

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
        'uri_segment'   => 'p',
        'num_links'     => 2,
        'per_page'      => 50,
        'name'          => 'default',
        'show_first'    => true,
        'show_last'     => true,
    );

    // 課リスト
    private $division_list = array();
    // 売上ステータスリスト
    private $sales_status_list = array();
    // 商品リスト
    private $product_list = array();
    // 車種リスト
    private $car_model_list = array();
    // 配送区分リスト
    private $delivery_category_list = array();
    // 登録者リスト
    private $create_user_list = array();

    // ユーザ情報
    private $user_authority = null;

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'check':
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
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '請求情報雛形出力';
        $cnf['page_id']                     = '[B1030]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '請求情報雛形出力';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

        $head                               = View::forge($this->head);
        $tree                               = View::forge($this->tree);
        $header                             = View::forge($this->header);
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
            'font-awesome/css/font-awesome.min.css'
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIB1030", B1030::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list        = GenerateList::getDivisionList(true, B1030::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list    = GenerateList::getSalesStatusList(true);
        // 商品リスト取得
        $this->product_list         = GenerateList::getProductList(true, B1030::$db);
        // 車種リスト取得
        $this->car_model_list       = GenerateList::getCarModelList(true, B1030::$db);
        // 配送区分リスト取得
        $this->delivery_list        = GenerateList::getShareDeliveryCategoryList(true);
        // 配車区分リスト取得
        $this->dispatch_list        = GenerateList::getDispatchCategoryList(true);
        // 地区リスト取得
        $this->area_list            = GenerateList::getAreaList(true, B1030::$db);
        // 単位リスト取得
        $this->unit_list            = GenerateList::getUnitList(true, B1030::$db);
        // 登録者リスト取得
        $this->create_user_list     = GenerateList::getCreateUserList(true, B1030::$db);

        // ユーザ権限取得
        $this->user_authority = $auth_data['user_authority'];
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
        //  Response::redirect(\Uri::create('top'));
        //}
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
    }

    private function validate_info() {

        // 入力チェック
        $validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 配車番号チェック
        $validation->add('dispatch_number', '配車番号')
            ->add_rule('is_numeric');
        $validation->run();
        return $validation;
    }
    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = B1030::getSearchClient($code, B1030::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = B1030::getSearchCarrier($code, B1030::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = B1030::getSearchCar($code, B1030::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                // $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = B1030::getSearchMember($code, B1030::$db)) {
                $conditions['driver_name']    = $result[0]['driver_name'];
                $conditions['member_code']    = $result[0]['member_code'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        }

        return $error_msg;
    }

    // レコード削除処理
    private function delete_record() {

        $dispatch_number = Input::post('dispatch_number', '');

        try {
            DB::start_transaction(B1030::$db);

            // レコード存在チェック
            if (!$result = B1030::getDispatchShare($dispatch_number, B1030::$db)) {
                return Config::get('m_DW0001');
            }

            // レコード削除（論理）
            $error_msg = B1030::deleteRecord($dispatch_number, B1030::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(B1030::$db);
                return $error_msg;
            }

            DB::commit_transaction(B1030::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(B1030::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_DI0023')."');</script>";

        return null;
    }

    // 売上ステータス更新処理
    private function update_record() {

        //売上ステータスリスト作成
        $upd_list_count = Input::post('list_count', 0);
        $upd_list = array();
        for ($i = 1; $i <= $upd_list_count; $i++) {
            if ($this->user_authority != '1' && Input::post('old_sales_status_'.$i, 1) == '2')continue;
            if (Input::post('old_sales_status_'.$i, 1) == Input::post('sales_status_'.$i, 1))continue;
            $upd_list[] = array('dispatch_number' => Input::post('dispatch_number_'.$i, 0), 'sales_status' => Input::post('sales_status_'.$i, 1));
        }

        try {
            DB::start_transaction(B1030::$db);

            // 売上ステータス更新
            $error_msg = B1030::updateRecord($upd_list, B1030::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(B1030::$db);
                return $error_msg;
            }

            DB::commit_transaction(B1030::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(B1030::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_DI0010')."');</script>";

        return null;
    }

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $error_msg_sub  = null;
        $search_flag    = true;
        $search_mode    = 1;
        $conditions     = array_fill_keys(array(
            'dispatch_number',
            'division',
            'delivery_code',
            'dispatch_code',
            'area_code',
            'course',
            'delivery_date_from',
            'delivery_date_to',
            'pickup_date_from',
            'pickup_date_to',
            'delivery_place',
            'pickup_place',
            'client_code',
            'carrier_code',
            'product_name',
            'car_model_code',
            'car_code',
            'driver_name',
            'create_user',
            'search_mode'
        ), '');

        if (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力値チェック
            $validation = $this->validate_info();
            $errors = $validation->error();
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    // チェック項目は配車番号のみのため固定
                    $error_msg = str_replace('XXXXX','配車番号',Config::get('m_CW0006'));
                }
            }

            // 入力項目相関チェック
            if (!empty($conditions['delivery_date_from']) && !empty($conditions['delivery_date_to'])) {
                if (strtotime($conditions['delivery_date_from']) > strtotime($conditions['delivery_date_to'])) {
                    $error_msg = str_replace('XXXXX','納品日',Config::get('m_CW0007'));
                }
            }
            if (!empty($conditions['pickup_date_from']) && !empty($conditions['pickup_date_to'])) {
                if (strtotime($conditions['pickup_date_from']) > strtotime($conditions['pickup_date_to'])) {
                    $error_msg = str_replace('XXXXX','引取日',Config::get('m_CW0007'));
                }
            }

            //検索モードを「本日分検索」に変更
            if (!empty(Input::param('search_today'))){
                $search_mode = 2;
            }
            $conditions['search_mode'] = $search_mode;

            /**
             * セッションに検索条件を設定
             */
            Session::delete('b1030_select_check');      // 選択セッションをクリア
            Session::delete('b1030_list');
            Session::set('b1030_list', $conditions);

        } elseif (!empty(Input::param('select_out')) && Security::check_token()) {
            // 選択したものを出力
            $dispatch_numbers   = array();

            if ($rec_select = Session::get('b1030_select_check', array())) {
                foreach ($rec_select as $key => $dispatch_number) {
                    $dispatch_numbers[] = $dispatch_number;
                }
            }
            if (!empty($dispatch_numbers)) {
                // 雛形ファイル出力
                $error_msg = B1030::dlExcelFile(1, $dispatch_numbers, B1030::$db);
            } else {
                $error_msg = Config::get('m_BW0012');
            }
        } elseif (!empty(Input::param('search_out')) && Security::check_token()) {
            // 検索したものを出力
            $dispatch_numbers = array();
            $dispatch_numbers = Session::get('b1030_all_check', array());
            if (!empty($dispatch_numbers)) {
                // 雛形ファイル出力
                $error_msg = B1030::dlExcelFile(2, $dispatch_numbers, B1030::$db);
            } else {
                $error_msg = Config::get('m_BW0018');
            }
        } elseif (!empty(Input::param('org_out')) && Security::check_token()) {
            // 空の雛形を出力
            // 雛形ファイル出力
            $error_msg = B1030::dlExcelFile(0, null, B1030::$db);
        } else {
            if ($cond = Session::get('b1030_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                $search_mode = $conditions['search_mode'];

                if (!empty(Input::param('select_record'))) {
                    // 検索項目の検索画面からコードが連携された場合の処理

                    foreach ($conditions as $key => $val) {
                        $conditions[$key] = Input::param($key, ''); // 検索項目
                    }

                    // 連携されたコードによる情報取得＆値セット
                    $error_msg      = $this->set_info($conditions);
                    $search_flag    = false;
                }

            } else {
                $search_flag = false;
            }

            //初期表示もエクスポートに備えて条件保存する
            Session::set('b1030_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = B1030::getSearch('count', $conditions, null, null, $search_mode, B1030::$db);

            // 検索上限チェック
            if (Config::get('b1030_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('b1030_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('b1030_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('b1030_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('b1030_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = B1030::getSearch('search', $conditions, $offset, $limit, $search_mode, B1030::$db);
            // 取得した配車番号をセッションに格納
            $number_data                = B1030::getSearch('select', $conditions, null, null, $search_mode, B1030::$db);
            foreach ($number_data as $key => $val) {
                $tmp[] = $val['dispatch_number'];
            }
            array_unique($tmp);
            //チェックボックスの情報をセッションに保存
            Session::delete('b1030_all_check');
            Session::set('b1030_all_check', $tmp);
        } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }
        //明細部のレコード件数取得
        $list_count = 0;
        if (is_countable($list_data)){
            $list_count = count($list_data);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'current_url'           => \Uri::create(\Uri::create(AccessControl::getActiveController())),
                'list_url'              => \Uri::create(\Uri::create('bill/b1010?init')),
                'check_url'             => \Uri::create(\Uri::create(AccessControl::getActiveController().'/check')),

                'total'                 => $total,
                'data'                  => $conditions,
                'rec_select'            => Session::get('b1030_select_check', array()),

                'userinfo'              => AuthConfig::getAuthConfig('all'),
                'division_list'         => $this->division_list,
                'sales_status_list'     => $this->sales_status_list,
                'product_list'          => $this->product_list,
                'car_model_list'        => $this->car_model_list,
                'delivery_list'         => $this->delivery_list,
                'dispatch_list'         => $this->dispatch_list,
                'area_list'             => $this->area_list,
                'unit_list'             => $this->unit_list,
                'create_user_list'      => $this->create_user_list,
                'user_authority'        => $this->user_authority,

                'list_data'             => $list_data,
                'list_count'            => $list_count,
                'offset'                => $offset,
                'error_message'         => $error_msg,
                'error_message_sub'     => $error_msg_sub,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }

    // 画面チェックボックス操作での処理
    public function action_check() {

        $res                = false;
        $type               = Input::param('type', '');
        $dispatch_number    = Input::param('dispatch_number', '');

        if ($type == '1') {
            // チェックした時
            if ($check_number = Session::get('b1030_select_check', array())) {
                $tmp[$dispatch_number] = $dispatch_number;
                $data = $check_number + $tmp;
            } else {
                $data[$dispatch_number] = $dispatch_number;
            }

            //チェックボックスの情報をセッションに保存
            Session::delete('b1030_select_check');
            Session::set('b1030_select_check', $data);
            $res = true;
        } else {
            // チェックを外した時
            if ($check_number = Session::get('b1030_select_check', array())) {
                foreach ($check_number as $key => $val) {
                    if (!isset($data[$val])) {
                        $data[$val] = $val;
                    }
                }
                unset($data[$dispatch_number]);
            }

            //チェックボックスの情報をセッションに保存
            Session::delete('b1030_select_check');
            Session::set('b1030_select_check', $data);
            $res = true;
        }

        return $this->response($res);
    }

}
