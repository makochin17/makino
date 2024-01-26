<?php
/**
 * 配車検索（共配便）画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Allocation\D1010;

class Controller_Allocation_D1010 extends Controller_Hybrid {

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

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '配車検索（共配便）';
        $cnf['page_id']                     = '[D1010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '配車検索（共配便）';
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
        $paging_config = PagingConfig::getPagingConfig("UID1010", D1010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 10;

        // 課リスト取得
        $this->division_list        = GenerateList::getDivisionList(true, D1010::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list    = GenerateList::getSalesStatusList(true);
        // 商品リスト取得
        $this->product_list         = GenerateList::getProductList(true, D1010::$db);
        // 車種リスト取得
        $this->car_model_list       = GenerateList::getCarModelList(true, D1010::$db);
        // 配送区分リスト取得
        $this->delivery_list        = GenerateList::getShareDeliveryCategoryList(true);
        // 配車区分リスト取得
        $this->dispatch_list        = GenerateList::getDispatchCategoryList(true);
        // 地区リスト取得
        $this->area_list            = GenerateList::getAreaList(true, D1010::$db);
        // 単位リスト取得
        $this->unit_list            = GenerateList::getUnitList(true, D1010::$db);
        // 登録者リスト取得
        $this->create_user_list     = GenerateList::getCreateUserList(true, D1010::$db);

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
        $this->initViewForge($auth_data);
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
            if ($result = D1010::getSearchClient($code, D1010::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D1010::getSearchCarrier($code, D1010::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D1010::getSearchCar($code, D1010::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                // $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D1010::getSearchMember($code, D1010::$db)) {
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
            DB::start_transaction(D1010::$db);

            // レコード存在チェック
            if (!$result = D1010::getDispatchShare($dispatch_number, D1010::$db)) {
                return Config::get('m_DW0001');
            }

            // レコード削除（論理）
            $error_msg = D1010::deleteRecord($dispatch_number, D1010::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(D1010::$db);
                return $error_msg;
            }

            DB::commit_transaction(D1010::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(D1010::$db);
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
            DB::start_transaction(D1010::$db);

            // 売上ステータス更新
            $error_msg = D1010::updateRecord($upd_list, D1010::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(D1010::$db);
                return $error_msg;
            }

            DB::commit_transaction(D1010::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(D1010::$db);
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

        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理

            //配車、分載データ削除
            $error_msg = $this->delete_record();

        } elseif (Input::post('processing_division', '') == '4' && Security::check_token()) {
            // 更新ボタンが押下された場合の処理

            //売上ステータス更新処理
            $error_msg = $this->update_record();

        }

        if ((!empty(Input::param('search')) || !empty(Input::param('search_today'))) && Security::check_token()) {
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
            Session::delete('d0011_list');
            Session::set('d0011_list', $conditions);

        } else {
            if ($cond = Session::get('d0011_list', array())) {

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
            Session::set('d0011_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = D1010::getSearch('count', $conditions, null, null, $search_mode, D1010::$db);

            // 検索上限チェック
            if (Config::get('d1010_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('d1010_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('d1010_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('d1010_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('d1010_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = D1010::getSearch('search', $conditions, $offset, $limit, $search_mode, D1010::$db);
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
                'total'                 => $total,
                'data'                  => $conditions,
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
}
