<?php
/**
 * 出庫処理画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Logistics\L0010;
use \Model\Logistics\L0012;

class Controller_Logistics_L0012 extends Controller_Hybrid {

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

    // ユーザ情報
    private $user_authority             = array();

    /**
    * 画面共通初期設定
    **/
    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'upddeliverydatetime':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '出庫処理';
        $cnf['page_id']                     = '[L0012]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '出庫処理';
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

        $ary_header_js = array(
            'logistics/l0012.js',
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

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIL0012", L0012::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

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
        $this->location_list                = GenerateList::getLocationList(true, L0012::$db);

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
        // // お客様番号チェック
        // $validation->add('customer_code', 'お客様番号')
        //     ->add_rule('is_numeric')
        // ;
        // // お客様名チェック
        // $validation->add('customer_name', 'お客様名')
        //     ->add_rule('is_numeric')
        // ;
        // // タイヤ種別チェック
        // $validation->add('customer_name_kana', 'お客様名かな')
        //     ->add_rule('is_numeric')
        // ;

        $validation->run();
        return $validation;
    }
    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0012::getSearchCustomer($code, L0012::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0012::getSearchCarByCode($code, L0012::$db)) {
                $conditions['car_id']   = $result['car_id'];
                if (Session::get('select_car_mode') == 'num') {
                    $conditions['car_code'] = $result['car_code'];
                } elseif (Session::get('select_car_mode') == 'name') {
                    $conditions['car_name'] = $result['car_name'];
                } else {
                    $conditions['car_code'] = $result['car_code'];
                    $conditions['car_name'] = $result['car_name'];
                }
            } else {
                $error_msg = Config::get('m_CAR011');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_location_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = L0012::getSearchCar($code, L0012::$db)) {
                $conditions['location_id']      = $result['storage_location_id'];
                $conditions['location_name']    = $result['storage_location_name'];
                $conditions['barcode_flg']      = $result['barcode_flg'];
            } else {
                $error_msg = Config::get('m_MI0025');
            }
            Session::delete('select_location_code');
        }

        return $error_msg;
    }

    // レコード更新処理
    private function update_record($conditions) {

        try {
            DB::start_transaction(L0012::$db);
            // 対象IDを配列化
            $ids            = (is_array(explode(',', $conditions['select_id']))) ? explode(',', $conditions['select_id']):array($conditions['select_id']);
            // 出庫日と出庫時間を自動設定
            $d                  = new DateTime();
            $date               = $d->format('Y-m-d H:i');
            $delivery_datetime  = date('Y-m-d H:i', L0012::ceilDatetime(strtotime($date)));
            $delivery           = explode(' ', $delivery_datetime);
            $delivery_date      = $delivery[0];
            $delivery_time      = $delivery[1];
            foreach ($ids as $logistics_id) {
                $conditions['logistics_id']     = $logistics_id;
                $conditions['delivery_date']    = $delivery_date;
                $conditions['delivery_time']    = $delivery_time;
                $conditions['delivery_flg']     = 'YES';
                // レコード存在チェック
                if (!$result = L0012::getLogisticsById($conditions['logistics_id'], L0012::$db)) {
                    return Config::get('m_DE0012');
                }

                // レコード更新
                $error_msg = L0012::update_record($conditions, L0012::$db);
                if (!is_null($error_msg)) {
                    DB::rollback_transaction(L0012::$db);
                    return $error_msg;
                }
            }

            DB::commit_transaction(L0012::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(L0012::$db);
            Log::error($e->getMessage());
            var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_DE0013')."');</script>";
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
        $list_error_msg     = null;
        $list_error_msg_sub = null;
        $search_flag        = true;
        $search_mode        = 1;
        $mode               = Input::param('mode', '');
        $all_logistics_ids  = Input::param('all_logistics_ids', '');
        $conditions         = L0012::getForms('search');

        if (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            Session::delete('l0012_list');
            \Response::redirect(\Uri::create('logistics/l0010'));
        } elseif ((!empty(Input::param('search')) || !empty(Input::param('search_today'))) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理
            $conditions = L0012::setForms('logistics', $conditions, Input::param());

            // 入力値チェック
            $validation = $this->validate_info();
            $errors = $validation->error();
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    // チェック項目はお客様番号のみのため固定
                    $error_msg = str_replace('XXXXX','customer_code',Config::get('m_CW0006'));
                }
            }

            $conditions['search_mode'] = $search_mode;

            /**
             * セッションに検索条件を設定
             */
            Session::delete('l0012_list');
            Session::set('l0012_list', $conditions);

        } elseif ($mode == 'deliveryfix' && Input::method() == 'POST' && Security::check_token()) {
            // 出庫確定ボタンが押下された場合の処理
            $conditions         = L0012::setForms('logistics', $conditions, Input::param());
            $list_error_msg     = $this->update_record($conditions);
            if (!empty($list_error_msg)) {
                $list_error_msg_sub = "※出庫対象を選択してください。";
            }

        } else {
            if ($cond = Session::get('l0012_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                $search_mode = $conditions['search_mode'];

                if (!empty(Input::param('select_record'))) {
                    // 検索項目の検索画面からコードが連携された場合の処理
                    $conditions = L0012::setForms('logistics', $conditions, Input::param());

                    // 連携されたコードによる情報取得＆値セット
                    $error_msg      = $this->set_info($conditions);
                    $search_flag    = false;
                }

            } else {
                $search_flag = false;
            }

            //初期表示もエクスポートに備えて条件保存する
            Session::set('l0012_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = L0012::getSearch('count', $conditions, null, null, $search_mode, L0012::$db);
            // 検索上限チェック
            if (Config::get('l0012_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('l0012_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('l0012_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('l0012_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('l0012_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            if ($list_data              = L0012::getSearch('search', $conditions, $offset, $limit, $search_mode, L0012::$db)) {
                foreach($list_data as $key => $val) {
                    $tmp[] = $val['logistics_id'];
                }
                $all_logistics_ids      = implode(',', $tmp);
            }
        } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg) && !empty($search_flag)) {
            $error_msg = Config::get('m_CI0003');
        }

        //明細部のレコード件数取得
        $list_count = 0;
        if (is_countable($list_data)){
            $list_count = count($list_data);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                      => \Uri::create(\Uri::create('logistics/l0010')),
                'update_url'                    => \Uri::create('logistics/l0012/upddeliverydatetime'),

                'total'                         => $total,
                'all_logistics_ids'             => $all_logistics_ids,
                'data'                          => $conditions,

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
                // 保管場所リスト(プルダウン用)
                'location_combo_list'           => array('' => '-') + L0012::getLocationList('logistics', $this->location_list, L0012::$db),

                'list_data'                     => $list_data,
                'list_count'                    => $list_count,
                'offset'                        => $offset,
                'error_message'                 => $error_msg,
                'error_message_sub'             => $error_msg_sub,
                'list_error_message'            => $list_error_msg,
                'list_error_message_sub'        => $list_error_msg_sub,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }

    /**
    * 出庫情報更新(出庫日＆出庫時間)
    **/
    public function action_upddeliverydatetime() {

        $res            = 0;
        $conditions     = L0012::getForms('set');
        $conditions     = L0012::setForms('logistics', $conditions, Input::param());

        if (!empty($conditions['logistics_id'])) {

            try {
                DB::start_transaction(L0012::$db);
                // レコード存在チェック
                if (!$result = L0012::getLogisticsById($conditions['logistics_id'], L0012::$db)) {
                    throw new Exception("", 1);
                }

                // レコード更新
                $error_msg = L0012::update_record($conditions, L0012::$db);
                if (!is_null($error_msg)) {
                    DB::rollback_transaction(L0012::$db);
                    throw new Exception("", 1);
                }

                DB::commit_transaction(L0012::$db);
                $res = 1;

            } catch (Exception $e) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(L0012::$db);
            }
        }

        $this->response->set_header('Access-Control-Allow-Origin', '*');
        return $this->response($res);
    }

}
