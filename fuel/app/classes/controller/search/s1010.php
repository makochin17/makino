<?php
/**
 * 配車検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Search\S1010;

class Controller_Search_S1010 extends Controller_Hybrid {

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
    // 配送区分リスト
    private $delivery_category_list     = array();
    // 配車区分リスト
    private $dispatch_category_list     = array();
    // 地区リスト
    private $area_list                  = array();
    // 単位リスト
    private $unit_list                  = array();
    // 車種リスト
    private $carmodel_list              = array();
    // 登録者リスト
    private $create_user_list           = array();

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
        $paging_config = PagingConfig::getPagingConfig("UIS1010", S1010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, S1010::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 配送区分
        $this->delivery_category_list   = GenerateList::getShareDeliveryCategoryList(true);
        // 配車区分
        $this->dispatch_category_list   = GenerateList::getDispatchCategoryList(true);
        // 地区リスト取得
        $this->area_list                = GenerateList::getAreaList(true, S1010::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(true, S1010::$db);
        // 車種リスト
        $this->carmodel_list            = GenerateList::getCarModelList(true, S1010::$db);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(true, S1010::$db);
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
            $result = S1010::getSearchClient($code, S1010::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
            
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            $result = S1010::getSearchCarrier($code, S1010::$db);
            if (count($result) > 0) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
            
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            $result = S1010::getSearchCar($code, S1010::$db);
            if (count($result) > 0) {
                $conditions['car_code'] = $result[0]['car_code'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
            
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            $result = S1010::getSearchMember($code, S1010::$db);
            if (count($result) > 0) {
                $conditions['driver_name'] = $result[0]['driver_name'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
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
                if (preg_match('/dispatch_number/', $key)) {
                    $error_item = 'dispatch_number';
                } elseif (preg_match('/course/', $key)) {
                    $error_item = 'course';
                } elseif (preg_match('/delivery_place/', $key)) {
                    $error_item = 'delivery_place';
                } elseif (preg_match('/pickup_place/', $key)) {
                    $error_item = 'pickup_place';
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
                } elseif (preg_match('/from_delivery_date/', $key)) {
                    $error_item = 'from_delivery_date';
                } elseif (preg_match('/to_delivery_date/', $key)) {
                    $error_item = 'to_delivery_date';
                } elseif (preg_match('/from_pickup_date/', $key)) {
                    $error_item = 'from_pickup_date';
                } elseif (preg_match('/to_pickup_date/', $key)) {
                    $error_item = 'to_pickup_date';
                }

                $item = S1010::getValidateItems();
                $error_column = $item[$error_item]['name'];
                $column_length = $item[$error_item]['max_lengths'];

                if ($validation->error()[$key]->rule == 'required' && $error_item == 'car_code') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0031'));
                } elseif ($validation->error()[$key]->rule == 'required') {
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
        $item = S1010::getValidateItems();
		// 配車番号チェック
		$validation->add('dispatch_number', $item['dispatch_number']['name'])
            ->add_rule('trim_max_lengths', $item['dispatch_number']['max_lengths'])
            ->add_rule('is_numeric');
        // コースチェック
		$validation->add('course', $item['course']['name'])
            ->add_rule('trim_max_lengths', $item['course']['max_lengths']);
        // 納品先チェック
		$validation->add('delivery_place', $item['delivery_place']['name'])
            ->add_rule('trim_max_lengths', $item['delivery_place']['max_lengths']);
        // 引取先チェック
		$validation->add('pickup_place', $item['pickup_place']['name'])
            ->add_rule('trim_max_lengths', $item['pickup_place']['max_lengths']);
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
        // 納品日Fromチェック
		$validation->add('from_delivery_date', $item['from_delivery_date']['name'])
            ->add_rule('valid_date_format');
        // 納品日Toチェック
		$validation->add('to_delivery_date', $item['to_delivery_date']['name'])
            ->add_rule('valid_date_format');
        // 引取日Fromチェック
		$validation->add('from_pickup_date', $item['from_pickup_date']['name'])
            ->add_rule('valid_date_format');
        // 引取日Toチェック
		$validation->add('to_pickup_date', $item['to_pickup_date']['name'])
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
        $error_msg      = null;
        $error_msg_sub  = null;
        $date_error_msg1    = null;
        $date_error_msg2    = null;
        $search_flag      = true;
        $conditions 	= S1010::getForms();
        
        if (!empty(Input::param('cancel')) && Security::check_token()) {
            // キャンセルボタンが押下された場合の処理
            
            Session::set('select_cancel', true);
            Session::delete('s1010_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";

            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('select_dispatch_number')) && Security::check_token()) {
            // 選択ボタンが押下された場合の処理
            
            Session::set('select_dispatch_code', Input::param('select_dispatch_number'));
            Session::delete('s1010_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";
            // echo "<script type='text/javascript'>window.location.href='" . Uri::create('test/popuptest') ."';</script>";
        } elseif (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理
            $conditions = S1010::setForms($conditions, Input::param());

            // 入力値チェック
            $error_msg = $this->input_check();
            // 日付相関チェック（納品日）
            if (!empty($conditions['from_delivery_date']) && !empty($conditions['to_delivery_date'])) {
                if ($conditions['from_delivery_date'] > $conditions['to_delivery_date']) {
                    $error_msg = str_replace('XXXXX','納品日',Config::get('m_CW0007'));
                }
            }
            // 日付相関チェック（引取日）
            if (!empty($conditions['from_pickup_date']) && !empty($conditions['to_pickup_date'])) {
                if ($conditions['from_pickup_date'] > $conditions['to_pickup_date']) {
                    $error_msg = str_replace('XXXXX','引取日',Config::get('m_CW0007'));
                }
            }
                        
            /**
             * セッションに検索条件を設定
             */
            Session::delete('s1010_list');
            Session::set('s1010_list', $conditions);
            
        } else {
            if ($cond = Session::get('s1010_list', array())) {
                
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
            Session::set('s1010_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = S1010::getSearchCount($conditions, S1010::$db);
            
            // 検索上限チェック
            if (Config::get('s1010_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('s1010_limit'),Config::get('m_DW0015'));
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
            $list_data                  = S1010::getSearch($conditions, $offset, $limit, S1010::$db);
        } elseif (!empty(Input::param('search')) && Security::check_token() && empty($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                  => $total,
                'data'                   => $conditions,
                'division_list'          => $this->division_list,
                'sales_status_list'      => $this->sales_status_list,
                'delivery_category_list' => $this->delivery_category_list,
                'dispatch_category_list' => $this->dispatch_category_list,
                'area_list'              => $this->area_list,
                'unit_list'              => $this->unit_list,
                'carmodel_list'          => $this->carmodel_list,
                'create_user_list'       => $this->create_user_list,
                'list_data'              => $list_data,
                'offset'                 => $offset,
                'error_message'          => $error_msg,
                'error_message_sub'      => $error_msg_sub,
                'date_error_message1'    => $date_error_msg1,
                'date_error_message2'    => $date_error_msg2,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
        
    }
}
