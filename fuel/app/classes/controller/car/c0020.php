<?php
/**
 * 車両一覧画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Car\C0020;

class Controller_Car_C0020 extends Controller_Hybrid {

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

    // 会社マスタリスト
    private $company_list       = array();
    // タイヤ種別リスト
    private $tire_kind_list     = array();

    // ユーザ情報
    private $user_authority     = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '車両一覧';
        $cnf['page_id']                     = '[C0020]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '車両一覧';
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
            'car/c0020.js',
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
        $paging_config = PagingConfig::getPagingConfig("UIC0020", C0020::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

        // 会社マスタリスト
        $this->company_list             = GenerateList::getCompanyList(true, C0020::$db);
        // タイヤ種別リスト
        $this->tire_kind_list           = GenerateList::getTireKindList(true, C0020::$db);

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
        //  Response::redirect(\Uri::create('top'));
        //}
        $this->initViewForge($auth_data);
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
            if ($result = C0020::getSearchCustomer($code, C0020::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = C0020::getSearchCarByCode($code, C0020::$db)) {
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
        }

        return $error_msg;
    }

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');
        // 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $error_msg_sub  = null;
        $search_flag    = true;
        $search_mode    = 1;
        $customer_code  = $auth_data['customer_code'];
        $conditions     = array_fill_keys(array(
            'customer_code',
            'customer_name',
            'car_code',
            'class_flg',
            'warning_flg',
            'caution_flg',
            'search_mode'
        ), '');

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
                    $error_msg = str_replace('XXXXX','customer_code',Config::get('m_CW0006'));
                }
            }

            $conditions['search_mode'] = $search_mode;

            /**
             * セッションに検索条件を設定
             */
            Session::delete('c0020_list');
            Session::set('c0020_list', $conditions);

        } else {
            if ($cond = Session::get('c0020_list', array())) {

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

            $conditions['customer_code'] = $customer_code;
            //初期表示もエクスポートに備えて条件保存する
            Session::set('c0020_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = C0020::getSearch('count', $conditions, null, null, $search_mode, C0020::$db);
            // 検索上限チェック
            if (Config::get('c0020_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('c0020_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('c0020_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('c0020_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('c0020_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = C0020::getSearch('search', $conditions, $offset, $limit, $search_mode, C0020::$db);
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
                'company_list'          => $this->company_list,
                'tire_kind_list'        => $this->tire_kind_list,
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
