<?php
/**
 * 月極その他情報検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Allocation\D0031;

class Controller_Allocation_D0031 extends Controller_Hybrid {

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
    // 売上区分リスト
    private $sales_category_list = array();
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
        $cnf['header_title']                = '月極その他情報検索';
        $cnf['page_id']                     = '[D0031]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '月極その他情報検索';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

        $head                               = View::forge($this->head);
        $tree                               = View::forge($this->tree);
        $header 							= View::forge($this->header);
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
        $paging_config = PagingConfig::getPagingConfig("UID0031", D0031::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(true, D0031::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list = GenerateList::getSalesStatusList(true);
        // 売上区分リスト取得
        $this->sales_category_list = GenerateList::getSalesCategoryList(true, D0031::$db);
        // 車種リスト取得
        $this->car_model_list = GenerateList::getCarModelList(true, D0031::$db);
        // 配送区分リスト取得
        $this->delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        // 登録者リスト取得
        $this->create_user_list = GenerateList::getCreateUserList(true, D0031::$db);
        
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
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}

	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
		// 月極その他番号チェック
		$validation->add('sales_correction_number', '月極その他番号')
			->add_rule('is_numeric');
		$validation->run();
		return $validation;
	}
    // 検索条件から呼び出した各種検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;
        
		if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            $result = D0031::getSearchClient($code, D0031::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
            
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            $result = D0031::getSearchCarrier($code, D0031::$db);
            if (count($result) > 0) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
            
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            $result = D0031::getSearchCar($code, D0031::$db);
            if (count($result) > 0) {
                $conditions['car_code'] = $result[0]['car_code'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
            
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            $result = D0031::getSearchMember($code, D0031::$db);
            if (count($result) > 0) {
                $conditions['driver_name'] = $result[0]['driver_name'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        }
        
        return $error_msg;
	}
    
    // 売上ステータス更新処理
    private function update_record() {
        
        //売上ステータスリスト作成
        $upd_list_count = Input::post('list_count', 0);
        $upd_list = array();
        for ($i = 1; $i <= $upd_list_count; $i++) {
            if ($this->user_authority != '1' && Input::post('old_sales_status_'.$i, 1) == '2')continue;
            if (Input::post('old_sales_status_'.$i, 1) == Input::post('sales_status_'.$i, 1))continue;
            $upd_list[] = array('sales_correction_number' => Input::post('sales_correction_number_'.$i, 0), 'sales_status' => Input::post('sales_status_'.$i, 1));
        }
        
        try {
            DB::start_transaction(D0031::$db);
            
            // 売上ステータス更新
            $error_msg = D0031::updateRecord($upd_list, D0031::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(D0031::$db);
                return $error_msg;
            }
            
            DB::commit_transaction(D0031::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(D0031::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_DI0013')."');</script>";
        
        return null;
    }
    
    // レコード削除処理
    private function delete_record() {
        
        $sales_correction_number = Input::post('sales_correction_number', '');
        
        try {
            DB::start_transaction(D0031::$db);
            
            // レコード存在チェック
            if (!$result = D0031::getSalesCorrection($sales_correction_number, D0031::$db)) {
                return Config::get('m_DW0011');
            }

            // レコード削除（論理）
            $error_msg = D0031::deleteRecord($sales_correction_number, D0031::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(D0031::$db);
                return $error_msg;
            }
            
            DB::commit_transaction(D0031::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(D0031::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_DI0014')."');</script>";
        
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
        $search_flag      = true;
        $search_mode      = 1;
        $conditions 	= array_fill_keys(array(
        	'sales_correction_number',
        	'division',
            'sales_status',
        	'sales_date_from',
        	'sales_date_to',
        	'sales_category',
        	'client_name',
        	'carrier_name',
            'client_code',
        	'carrier_code',
        	'car_model',
        	'car_code',
        	'driver_name',
        	'delivery_category',
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
                    // チェック項目は月極その他番号のみのため固定
                    $error_msg = str_replace('XXXXX','月極その他番号',Config::get('m_CW0006'));
				}
			}
            
            // 入力項目相関チェック
            if (!empty($conditions['sales_date_from']) && !empty($conditions['sales_date_to'])) {
                if (strtotime($conditions['sales_date_from']) > strtotime($conditions['sales_date_to'])) {
                    $error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
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
            Session::delete('d0031_list');
            Session::set('d0031_list', $conditions);
            
        } else {
            if ($cond = Session::get('d0031_list', array())) {
                
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
                    $error_msg = $this->set_info($conditions);
                    $search_flag = false;
                }

            } else {
                $search_flag = false;
            }
            
            //初期表示もエクスポートに備えて条件保存する
            Session::set('d0031_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = D0031::getSearchCount($conditions, D0031::$db, $search_mode);
            
            // 検索上限チェック
            if (Config::get('d0031_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('d0031_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }
        
        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('d0031_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);
            
            //ページネーションのページ数をセッションに保存
            Session::set('d0031_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('d0031_page', Input::get('p'));
        }
        
        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        
        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = D0031::getSearch($conditions, $offset, $limit, D0031::$db, $search_mode);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }
        
        //明細部のレコード件数取得
        $list_count = 0;
        if (is_countable($list_data)){
            $list_count = count($list_data);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                  => $total,
                'data'                   => $conditions,
                'userinfo'               => AuthConfig::getAuthConfig('all'),
                'division_list'          => $this->division_list,
                'sales_status_list'      => $this->sales_status_list,
                'sales_category_list'    => $this->sales_category_list,
                'car_model_list'         => $this->car_model_list,
                'delivery_category_list' => $this->delivery_category_list,
                'create_user_list'       => $this->create_user_list,
                'user_authority'         => $this->user_authority,
                'list_data'              => $list_data,
                'list_count'             => $list_count,
                'offset'                 => $offset,
                'error_message'          => $error_msg,
                'error_message_sub'      => $error_msg_sub,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
        
    }
}
