<?php
/**
 * 配車検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Search\S1030;

class Controller_Search_S1030 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base_popup';
    private $head     	= 'head';

    // ページネーション
    private $pagenation_config = array(
        'uri_segment' 	=> 'p',
    	'num_links' 	=> 2,
    	'per_page' 		=> 50,
    	'name' 			=> 'default',
    	'show_first' 	=> true,
    	'show_last' 	=> true,
    );

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

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '保管料情報検索';

		$head                               = View::forge($this->head);
		$head->title                        = $cnf['header_title'];

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
		$this->template->head = $head;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIS1030", S1030::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, S1030::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 保管料区分
        $this->storage_fee_list         = GenerateList::getStorageFeeCategoryList(true, S1030::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(true, S1030::$db);
        // 端数処理リスト取得
        $this->rounding_list            = GenerateList::getRoundingList(true);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(true, S1030::$db);
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
		$this->initViewForge($auth_data);
	}

    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

		if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            $result = S1030::getSearchClient($code, S1030::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');

        }

        return $error_msg;
	}

    // 入力チェック
	private function input_check() {
        $error_msg = "";
        $validation = $this->validate_info();
        $errors     = $validation->error();

        // 入力値チェックのエラー判定
        if (!empty($errors)) {
            foreach($validation->error() as $key => $e) {
                if (preg_match('/storage_fee_number/', $key)) {
                    $error_item = 'storage_fee_number';
                } elseif (preg_match('/client_code/', $key)) {
                    $error_item = 'client_code';
                } elseif (preg_match('/storage_location/', $key)) {
                    $error_item = 'storage_location';
                } elseif (preg_match('/product_name/', $key)) {
                    $error_item = 'product_name';
                } elseif (preg_match('/maker_name/', $key)) {
                    $error_item = 'maker_name';
                } elseif (preg_match('/from_closing_date/', $key)) {
                    $error_item = 'from_closing_date';
                } elseif (preg_match('/to_closing_date/', $key)) {
                    $error_item = 'to_closing_date';
                }

                $item = S1030::getValidateItems();
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
        $item = S1030::getValidateItems();
        // 保管料番号チェック
        $validation->add('storage_fee_number', $item['storage_fee_number']['name'])
            ->add_rule('trim_max_lengths', $item['storage_fee_number']['max_lengths'])
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
        // 締日Fromチェック
        $validation->add('from_closing_date', $item['from_closing_date']['name'])
            ->add_rule('valid_date_format');
        // 締日Toチェック
        $validation->add('to_closing_date', $item['to_closing_date']['name'])
            ->add_rule('valid_date_format');
		$validation->run();
		return $validation;
	}

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg          = null;
        $error_msg_sub      = null;
        $date_error_msg1    = null;
        $date_error_msg2    = null;
        $search_flag        = true;
        $conditions         = S1030::getForms();

        if (!empty(Input::param('cancel')) && Security::check_token()) {
            // キャンセルボタンが押下された場合の処理

            Session::set('select_cancel', true);
            Session::delete('s1030_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";

            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('select_storage_fee_number')) && Security::check_token()) {
            // 選択ボタンが押下された場合の処理

            Session::set('select_storage_fee_number', Input::param('select_storage_fee_number'));
            Session::delete('s1030_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";
            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理
            $conditions = S1030::setForms($conditions, Input::param());

            // 入力値チェック
            $error_msg = $this->input_check();
            // 日付相関チェック（締日）
            if (!empty($conditions['from_closing_date']) && !empty($conditions['to_closing_date'])) {
                if ($conditions['from_closing_date'] > $conditions['to_closing_date']) {
                    $error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('s1030_list');
            Session::set('s1030_list', $conditions);

        } else {
            if ($cond = Session::get('s1030_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }

                if (!empty(Input::param('select_record'))) {
                    // 検索項目の検索画面からコードが連携された場合の処理

                    foreach ($conditions as $key => $val) {
                        $conditions[$key] = Input::param($key, ''); // 検索項目
                    }

                    // 連携されたコードによる情報取得＆値セット
                    $error_msg = $this->set_info($conditions);
                    $search_flag = false;
                }

            } else {
                $search_flag = false;
            }

            //初期表示もエクスポートに備えて条件保存する
            Session::set('s1030_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = S1030::getSearchCount($conditions, S1030::$db);

            // 検索上限チェック
            if (Config::get('s1030_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('s1030_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }
        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = S1030::getSearch($conditions, $offset, $limit, S1030::$db);
        } elseif (!empty(Input::param('search')) && Security::check_token() && empty($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                     => $total,

                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'sales_status_list'         => $this->sales_status_list,
                'storage_fee_list'          => $this->storage_fee_list,
                'unit_list'                 => $this->unit_list,
                'rounding_list'             => $this->rounding_list,
                'create_user_list'          => $this->create_user_list,

                'list_data'                 => $list_data,
                'offset'                    => $offset,
                'error_message'             => $error_msg,
                'error_message_sub'         => $error_msg_sub,
                'date_error_message1'       => $date_error_msg1,
                'date_error_message2'       => $date_error_msg2,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());

    }
}
