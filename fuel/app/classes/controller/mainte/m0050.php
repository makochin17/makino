<?php
/**
 * 車両マスタメンテナンス画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0050;

class Controller_Mainte_M0050 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 処理区分リスト
    private $processing_division_list = array();
    // 車種リスト
    private $car_model_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '車両マスタメンテナンス';
        $cnf['page_id'] 				    = '[M0050]';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'マスタメンテナンス業務';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController());
		$cnf['tree']['page_title'] 			= '車両マスタメンテナンス';

		$head   							= View::forge($this->head);
		$header 							= View::forge($this->header);
		$tree   							= View::forge($this->tree);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
		$tree->tree							= $cnf['tree'];
		$sidemenu->login_user_name          = AuthConfig::getAuthConfig('name');
		$sidemenu->copyright				= $cnf['copyright'];

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
        $this->template->header 	= $header;
		$this->template->tree 		= $tree;
		$this->template->sidemenu 	= $sidemenu;
		$this->template->footer 	= $footer;

        // 処理区分リスト取得
        $this->processing_division_list = GenerateList::getProcessingDivisionList();
        
        // 車種リスト取得
        $this->car_model_list = GenerateList::getCarModelList(false, M0050::$db);
	}

	public function before() {
		parent::before();
		// ログインチェック
		if(!Auth::check()) {
			Response::redirect(\Uri::base(false));
		}

		// 担当者情報を設定
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
		// 車両コードチェック
		$validation->add('car_code', '車両コード')
			->add_rule('is_numeric');
		$validation->run();
		return $validation;
	}
    
    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;
        
		if ($code = Session::get('select_car_code')) {
            // 検索にてレコード選択された場合
            $result = M0050::getCar($code, M0050::$db);
            if (count($result) > 0) {
                // レコード取得出来たら値をセット
                foreach ($result[0] as $key => $val) {
                    $conditions[$key] = $result[0][$key];
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_car_code');
        }
        
        return $error_msg;
	}
    
    // 登録処理
    private function create_record($conditions) {
        
        try {
            DB::start_transaction(M0050::$db);
            
            // レコード存在チェック
            $result = M0050::getCar($conditions['car_code'], M0050::$db);

            if (count($result) == 1) {
                return Config::get('m_MW0004');
            }

            // レコード登録
            $result = M0050::addCar($conditions, M0050::$db);
            if (!$result) {
                Log::error(Config::get('m_ME0003')."[".print_r($conditions,true)."]");
                return Config::get('m_ME0003');
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0005', Config::get('m_MI0005'), '車両マスタ', M0050::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }
            
            DB::commit_transaction(M0050::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0050::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0005')."');</script>";
        
        return null;
    }
    
    // 更新処理
    private function update_record($conditions) {
        
        try {
            DB::start_transaction(M0050::$db);
            
            // レコード存在チェック
            $result = M0050::getCar($conditions['car_code'], M0050::$db);

            if (count($result) == 0) {
                return Config::get('m_MW0005');
            }

            $start_date = $result[0]['start_date'];

            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($start_date) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = M0050::delCar($conditions['car_code'], M0050::$db);
                if (!$result) {
                    Log::error(Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                    return Config::get('m_ME0004');
                }
                // レコード登録
                $result = M0050::addCar($conditions, M0050::$db);
                if (!$result) {
                    Log::error(Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                    return Config::get('m_ME0004');
                }
            } else {
                //　レコード更新
                $result = M0050::updCar($conditions, M0050::$db);
                if (!$result) {
                    Log::error(Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                    return Config::get('m_ME0004');
                }
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '車両マスタ', M0050::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }
            
            DB::commit_transaction(M0050::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0050::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
        
        return null;
    }
    
    // 削除処理
    private function delete_record($code) {
        
        try {
            DB::start_transaction(M0050::$db);
            
            // レコード存在チェック
            $result = M0050::getCar($code, M0050::$db);

            if (count($result) == 0) {
                return Config::get('m_MW0005');
            }

            // レコード削除（論理）
            $result = M0050::delCar($code, M0050::$db);
            if (!$result) {
                Log::error(Config::get('m_ME0005')."[car_code:".$code."]");
                return Config::get('m_ME0005');
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '車両マスタ', M0050::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }
            
            DB::commit_transaction(M0050::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0050::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        
        return null;
    }
    
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
        	'processing_division',
        	'car_code',
        	'car_model_code',
        	'car_name',
        	'car_number',
        ), '');
        
        if (!empty(Input::param('input_clear')) && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            
            Session::delete('m0050_list');
        } elseif (!empty(Input::param('excel'))) {
            // エクセル出力ボタンが押下された場合の処理
            M0050::createTsv(M0050::$db);
            
        } elseif (!empty(Input::param('csv_capture')) && Security::check_token()) {
            // CSV取込ボタンが押下された場合の処理
            
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
        } elseif (!empty(Input::param('execution')) && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            // 入力必須項目チェック
            $error_column = '';
            $error_column .= (empty($conditions['car_code'])) ? '車両コード、' : '' ;
            if ($conditions['processing_division'] != '3'){
                $error_column .= (empty($conditions['car_model_code'])) ? '車種、' : '' ;
                $error_column .= (empty($conditions['car_name'])) ? '車両名、' : '' ;
                $error_column .= (empty($conditions['car_number'])) ? '車両番号、' : '' ;
            }
            $error_column = rtrim($error_column, '、');
            
            if (!empty($error_column)) {
                $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
            } else {
                // 入力値チェック
                $validation = $this->validate_info();
                $errors = $validation->error();
            }
            
            // 入力値チェックのエラー判定
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e)
				{
                    // チェック項目は車両コードのみのため固定
                    $error_msg = str_replace('XXXXX','車両コード',Config::get('m_CW0006'));
                    break;
				}
			}
            
            if (empty($error_msg)) {
                switch ($conditions['processing_division']){
                    case '1':
                        // 登録処理
                        $error_msg = $this->create_record($conditions);
                        break;
                    case '2':
                        // 更新処理
                        $error_msg = $this->update_record($conditions);
                        break;
                    case '3':
                        // 削除処理
                        $error_msg = $this->delete_record($conditions['car_code']);
                        break;
                }
            }
            
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0050_list');
            Session::set('m0050_list', $conditions);
            
        } else {
            if ($cond = Session::get('m0050_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('m0050_list');
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                
                foreach ($conditions as $key => $val) {
                    $conditions[$key] = Input::param($key, ''); // 検索項目
                }
                
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
            }

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                     => $conditions,
                'processing_division_list' => $this->processing_division_list,
                'car_model_list'           => $this->car_model_list,
                'error_message'            => $error_msg,
            )
        );
        
    }
}
