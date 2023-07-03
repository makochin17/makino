<?php
/**
 * 請求情報検索（共配便）画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Bill\B1010;

class Controller_Bill_B1010 extends Controller_Hybrid {

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
        $cnf['header_title']                = '請求情報検索（共配便）';
        $cnf['page_id']                     = '[B1010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '請求情報検索（共配便）';
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
        $paging_config = PagingConfig::getPagingConfig("UIB1010", B1010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list        = GenerateList::getDivisionList(true, B1010::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list    = GenerateList::getSalesStatusList(true);
        // 商品リスト取得
        $this->product_list         = GenerateList::getProductList(true, B1010::$db);
        // 車種リスト取得
        $this->car_model_list       = GenerateList::getCarModelList(true, B1010::$db);
        // 配送区分リスト取得
        $this->delivery_list        = GenerateList::getShareDeliveryCategoryList(true);
        // 請求区分リスト取得
        $this->dispatch_list        = GenerateList::getDispatchCategoryList(true);
        // 地区リスト取得
        $this->area_list            = GenerateList::getAreaList(true, B1010::$db);
        // 単位リスト取得
        $this->unit_list            = GenerateList::getUnitList(true, B1010::$db);
        // 登録者リスト取得
        $this->create_user_list     = GenerateList::getCreateUserList(true, B1010::$db);

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

    // 入力チェック
    private function input_check() {
        $error_msg  = "";
        $validation = $this->validate_info();
        $errors     = $validation->error();

        // 入力値チェックのエラー判定
        if (!empty($errors)) {
            foreach($validation->error() as $key => $e) {
                if (preg_match('/bill_number/', $key)) {
                    $error_item = 'bill_number';
                } elseif (preg_match('/destination/', $key)) {
                    $error_item = 'destination';
                } elseif (preg_match('/client_code/', $key)) {
                    $error_item = 'client_code';
                } elseif (preg_match('/carrier_code/', $key)) {
                    $error_item = 'carrier_code';
                } elseif (preg_match('/product_name/', $key)) {
                    $error_item = 'product_name';
                } elseif (preg_match('/car_code/', $key)) {
                    $error_item = 'car_code';
                } elseif (preg_match('/driver_name/', $key)) {
                    $error_item = 'driver_name';
                } elseif (preg_match('/destination_date_from/', $key)) {
                    $error_item = 'destination_date_from';
                } elseif (preg_match('/destination_date_to/', $key)) {
                    $error_item = 'destination_date_to';
                }

                $item = B1010::getValidateItems();
                $error_column = $item[$error_item]['name'];
                $column_length = $item[$error_item]['max_lengths'];

                if ($validation->error()[$key]->rule == 'required') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    $error_msg = str_replace('xxxxx',$column_length,$error_msg);
                } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                }
                break;
            }
        }

        return $error_msg;
    }

    // バリデーションチェック
    private function validate_info() {

        $validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        $item = B1010::getValidateItems();
        // 請求番号チェック
        $validation->add('bill_number', $item['bill_number']['name'])
            ->add_rule('trim_max_lengths', $item['bill_number']['max_lengths'])
            ->add_rule('is_numeric');
        // 運行先チェック
        $validation->add('destination', $item['destination']['name'])
            ->add_rule('trim_max_lengths', $item['destination']['max_lengths']);
        // 得意先チェック
        $validation->add('client_code', $item['client_code']['name'])
            ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 庸車先チェック
        $validation->add('carrier_code', $item['carrier_code']['name'])
            ->add_rule('trim_max_lengths', $item['carrier_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 商品名チェック
        $validation->add('product_name', $item['product_name']['name'])
            ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
        // 車両番号チェック
        $validation->add('car_code', $item['car_code']['name'])
                ->add_rule('trim_max_lengths', $item['car_code']['max_lengths'])
                ->add_rule('is_numeric');
        // 運転手チェック
        $validation->add('driver_name', $item['driver_name']['name'])
            ->add_rule('trim_max_lengths', $item['driver_name']['max_lengths']);
        // 運行日Fromチェック
        $validation->add('destination_date_from', $item['destination_date_from']['name'])
            ->add_rule('valid_date_format');
        // 運行日Toチェック
        $validation->add('destination_date_to', $item['destination_date_to']['name'])
            ->add_rule('valid_date_format');
        $validation->run();
        return $validation;
    }

    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = B1010::getSearchClient($code, B1010::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = B1010::getSearchCarrier($code, B1010::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = B1010::getSearchCar($code, B1010::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                // $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = B1010::getSearchMember($code, B1010::$db)) {
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

        $bill_number        = Input::post('bill_number', '');

        try {
            DB::start_transaction(B1010::$db);

            // レコード存在チェック
            if (!$result = B1010::getBillShare($bill_number, B1010::$db)) {
                return Config::get('m_BW0002');
            }

            // レコード削除（論理）
            $error_msg = B1010::deleteRecord($bill_number, B1010::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(B1010::$db);
                return $error_msg;
            }

            DB::commit_transaction(B1010::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(B1010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_BI0007')."');</script>";

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
            $upd_list[] = array('bill_number' => Input::post('bill_number_'.$i, 0), 'dispatch_number' => Input::post('dispatch_number_'.$i, 0), 'sales_status' => Input::post('sales_status_'.$i, 1));
        }

        try {
            DB::start_transaction(B1010::$db);

            // 売上ステータス更新
            $error_msg = B1010::updateRecord($upd_list, B1010::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(B1010::$db);
                return $error_msg;
            }

            DB::commit_transaction(B1010::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(B1010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_BI0006')."');</script>";

        return null;
    }

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg          = null;
        $error_msg_sub      = null;
        $search_flag        = true;
        $search_mode        = 1;
        $conditions         = B1010::getForms();

        if (false !== Input::param('init', false)) {
            // 初期表示
            Session::delete('b1010_list');
        }
        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理

            //請求、分載データ削除
            $error_msg = $this->delete_record();

        } elseif (Input::post('processing_division', '') == '4' && Security::check_token()) {
            // 更新ボタンが押下された場合の処理

            //売上ステータス更新処理
            $error_msg = $this->update_record();

        }

        if ((!empty(Input::param('search')) || !empty(Input::param('search_today'))) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理
            $conditions = B1010::setForms($conditions, Input::param());

            // 入力値チェック
            $error_msg = $this->input_check();
            // 日付相関チェック（運行日）
            if (!empty($conditions['destination_date_from']) && !empty($conditions['destination_date_to'])) {
                if ($conditions['destination_date_from'] > $conditions['destination_date_to']) {
                    $error_msg = str_replace('XXXXX','運行日',Config::get('m_CW0007'));
                }
            }

            //検索モードを「本日分検索」に変更
            if (!empty(Input::param('search_today'))){
                $search_mode = 2;
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('b1010_list');
            Session::set('b1010_list', $conditions);

        } else {
            if ($cond = Session::get('b1010_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }

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
            Session::set('b1010_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {

            $total = B1010::getSearch('count', $conditions, null, null, $search_mode, B1010::$db);

            // 検索上限チェック
            if (Config::get('b1010_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('b1010_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('b1010_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('b1010_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('b1010_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = B1010::getSearch('search', $conditions, $offset, $limit, $search_mode, B1010::$db);
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
