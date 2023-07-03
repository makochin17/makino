<?php
/**
 * 配車検索（共配便）画面 
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Stock\D1110;

class Controller_Stock_D1110 extends Controller_Hybrid {

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
    // 単位リスト
    private $unit_list = array();
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
        $cnf['header_title']                = '在庫情報検索';
        $cnf['page_id']                     = '[D1110]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '在庫情報検索';
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
        $paging_config = PagingConfig::getPagingConfig("UID1110", D1110::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list        = GenerateList::getDivisionList(true, D1110::$db);
        // 単位リスト取得
        $this->unit_list            = GenerateList::getUnitList(true, D1110::$db);
        // 登録者リスト取得
        $this->create_user_list     = GenerateList::getCreateUserList(true, D1110::$db);

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
        $error_msg = "";
        $validation = $this->validate_info();
        $errors     = $validation->error();
        
        // 入力値チェックのエラー判定
        if (!empty($errors)) {
            foreach($validation->error() as $key => $e) {
                if (preg_match('/stock_number/', $key)) {
                    $error_item = 'stock_number';
                } elseif (preg_match('/client_code/', $key)) {
                    $error_item = 'client_code';
                } elseif (preg_match('/storage_location/', $key)) {
                    $error_item = 'storage_location';
                } elseif (preg_match('/product_name/', $key)) {
                    $error_item = 'product_name';
                } elseif (preg_match('/maker_name/', $key)) {
                    $error_item = 'maker_name';
                } elseif (preg_match('/part_number/', $key)) {
                    $error_item = 'part_number';
                } elseif (preg_match('/model_number/', $key)) {
                    $error_item = 'model_number';
                }

                $item = D1110::getValidateItems();
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
        $item = D1110::getValidateItems();
        // 在庫番号チェック
		$validation->add('stock_number', $item['stock_number']['name'])
            ->add_rule('trim_max_lengths', $item['stock_number']['max_lengths'])
            ->add_rule('is_numeric');
        // 得意先チェック
		$validation->add('client_code', $item['client_code']['name'])
            ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 保管場所チェック
		$validation->add('storage_location', $item['storage_location']['name'])
            ->add_rule('trim_max_lengths', $item['storage_location']['max_lengths']);
        // 商品名チェック
		$validation->add('product_name', $item['product_name']['name'])
            ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
        // メーカー名チェック
        $validation->add('maker_name', $item['maker_name']['name'])
            ->add_rule('trim_max_lengths', $item['maker_name']['max_lengths']);
        // 品番チェック
        $validation->add('part_number', $item['part_number']['name'])
            ->add_rule('trim_max_lengths', $item['part_number']['max_lengths']);
        // 型番チェック
        $validation->add('model_number', $item['model_number']['name'])
            ->add_rule('trim_max_lengths', $item['model_number']['max_lengths']);
		$validation->run();
		return $validation;
    }
    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = D1110::getSearchClient($code, D1110::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        }

        return $error_msg;
    }

    // レコード削除処理
    private function delete_record() {

        $stock_number = Input::post('stock_number', '');

        try {
            DB::start_transaction(D1110::$db);

            // レコード存在チェック
            if (!$result = D1110::getStock($stock_number, D1110::$db)) {
                return Config::get('m_DW0036');
            }

            // レコード削除（論理）
            $error_msg = D1110::deleteRecord($stock_number, D1110::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(D1110::$db);
                return $error_msg;
            }

            DB::commit_transaction(D1110::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(D1110::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_DI0029')."');</script>";

        return null;
    }

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $search_flag    = true;
        $search_mode    = 1;
        $conditions     = D1110::getForms();

        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理

            //在庫データ削除
            $error_msg = $this->delete_record();

        }
        if ((!empty(Input::param('search')) || !empty(Input::param('search_today'))) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力値チェック
            $error_msg = $this->input_check();

            //検索モードを「本日分検索」に変更
            if (!empty(Input::param('search_today'))){
                $search_mode = 2;
            }
            $conditions['search_mode'] = $search_mode;

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d1110_list');
            Session::set('d1110_list', $conditions);

        } else {
            if ($cond = Session::get('d1110_list', array())) {

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
            Session::set('d1110_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = D1110::getSearch('count', $conditions, null, null, $search_mode, D1110::$db);

            // 検索上限チェック
            if (Config::get('d1110_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('d1110_limit'),Config::get('m_DW0015'));
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('d1110_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('d1110_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('d1110_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = D1110::getSearch('search', $conditions, $offset, $limit, $search_mode, D1110::$db);
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
                'unit_list'             => $this->unit_list,
                'create_user_list'      => $this->create_user_list,
                'user_authority'        => $this->user_authority,
                'list_data'             => $list_data,
                'list_count'            => $list_count,
                'offset'                => $offset,
                'error_message'         => $error_msg,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }
}
