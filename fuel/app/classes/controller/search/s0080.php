<?php
/**
 * 配車検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Search\S0080;

class Controller_Search_S0080 extends Controller_Hybrid {

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

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '配車検索';

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
        $paging_config = PagingConfig::getPagingConfig("UIS0080", S0080::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(true, S0080::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list = GenerateList::getSalesStatusList(true);
        // 商品リスト取得
        $this->product_list = GenerateList::getProductList(true, S0080::$db);
        // 車種リスト取得
        $this->car_model_list = GenerateList::getCarModelList(true, S0080::$db);
        // 配送区分リスト取得
        $this->delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        // 登録者リスト取得
        $this->create_user_list = GenerateList::getCreateUserList(true, S0080::$db);
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
            $result = S0080::getSearchClient($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
            
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            $result = S0080::getSearchCarrier($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
            
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            $result = S0080::getSearchProduct($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['product'] = $result[0]['product_code'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
            
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            $result = S0080::getSearchCar($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['car_number'] = $result[0]['car_code'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
            
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            $result = S0080::getSearchMember($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['driver_name'] = $result[0]['driver_name'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        }
        
        return $error_msg;
	}
    
    public function action_index() {
        
        Config::load('message');
        Config::load('searchlimit');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $error_msg_sub  = null;
        $search_flag      = true;
        $conditions 	= array_fill_keys(array(
        	'dispatch_number',
        	'division',
        	'sales_status',
        	'stack_date_from',
        	'stack_date_to',
        	'drop_date_from',
        	'drop_date_to',
        	'client_name',
        	'carrier_name',
            'client_code',
        	'carrier_code',
        	'product',
        	'car_model',
        	'car_number',
        	'driver_name',
        	'delivery_category',
            'create_user'
        ), '');
        
        if (!empty(Input::param('cancel')) && Security::check_token()) {
            // キャンセルボタンが押下された場合の処理
            
            Session::set('select_cancel', true);
            Session::delete('s0080_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";

            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('select_dispatch_number')) && Security::check_token()) {
            // 選択ボタンが押下された場合の処理
            
            Session::set('select_dispatch_code', Input::param('select_dispatch_number'));
            Session::delete('s0080_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";
            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('search')) && Security::check_token()) {
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
            if (!empty($conditions['stack_date_from']) && !empty($conditions['stack_date_to'])) {
                if (strtotime($conditions['stack_date_from']) > strtotime($conditions['stack_date_to'])) {
                    $error_msg = str_replace('XXXXX','積日',Config::get('m_CW0007'));
                }
            }
            if (!empty($conditions['drop_date_from']) && !empty($conditions['drop_date_to'])) {
                if (strtotime($conditions['drop_date_from']) > strtotime($conditions['drop_date_to'])) {
                    $error_msg = str_replace('XXXXX','降日',Config::get('m_CW0007'));
                }
            }
                        
            /**
             * セッションに検索条件を設定
             */
            Session::delete('s0080_list');
            Session::set('s0080_list', $conditions);
            
        } else {
            if ($cond = Session::get('s0080_list', array())) {
                
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
            Session::set('s0080_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = S0080::getSearchCount($conditions, S0080::$db);
            
            // 検索上限チェック
            if (Config::get('s0080_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('s0080_limit'),Config::get('m_DW0015'));
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
            $list_data                  = S0080::getSearch($conditions, $offset, $limit, S0080::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                  => $total,
                'data'                   => $conditions,
                'division_list'          => $this->division_list,
                'sales_status_list'      => $this->sales_status_list,
                'product_list'           => $this->product_list,
                'car_model_list'         => $this->car_model_list,
                'delivery_category_list' => $this->delivery_category_list,
                'create_user_list'       => $this->create_user_list,
                'list_data'              => $list_data,
                'offset'                 => $offset,
                'error_message'          => $error_msg,
                'error_message_sub'      => $error_msg_sub,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
        
    }
}
