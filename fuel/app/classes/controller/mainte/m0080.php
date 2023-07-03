<?php
/**
 * 課マスタメンテナンス画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0080;

class Controller_Mainte_M0080 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 課リスト
    private $division_list = array();
    // 支社リスト
    private $branch_office_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '課マスタメンテナンス';
        $cnf['page_id'] 				    = '[M0080]';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'マスタメンテナンス業務';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController());
		$cnf['tree']['page_title'] 			= '課マスタメンテナンス';

		$head   							= View::forge($this->head);
		$header 							= View::forge($this->header);
		$tree   							= View::forge($this->tree);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
		$tree->tree							= $cnf['tree'];
        $tree->tree                         = '';
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

        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(false, M0080::$db);
        
        // 支社リスト取得
        $this->branch_office_list = GenerateList::getBranchOfficeList(false, M0080::$db);
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
		// 庸車先コードチェック
		$validation->add('carrier_code', '庸車先コード')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 5)
			->add_rule('is_numeric');
        // 課名チェック
        $validation->add('division_name', '課名')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 6);
        // 専用回線チェック
        $validation->add('private_line_number', '専用回線')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 15)
            ->add_rule('valid_strings', array('numeric', 'dashes'));
        // FAX番号チェック
        $validation->add('fax_number', 'FAX番号')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 15)
            ->add_rule('valid_strings', array('numeric', 'dashes'));
        // 携帯電話チェック
        $validation->add('mobile_phone_number', '携帯電話')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 15)
            ->add_rule('valid_strings', array('numeric', 'dashes'));
        // 担当者チェック
        $validation->add('person_in_charge', '担当者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 10);
		$validation->run();
		return $validation;
	}
    
    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;
        
		if (!empty(Input::param('division_code_select'))) {
            // 更新対象が選択された場合
            $result = M0080::getDivision(Input::param('division_code_select'), M0080::$db);
            if (count($result) > 0) {
                // レコード取得出来たら値をセット
                foreach ($result[0] as $key => $val) {
                    $conditions[$key] = $result[0][$key];
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = M0080::getSearchCarrier($code, M0080::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        }
        
        return $error_msg;
	}
    
    // 更新処理
    private function update_record($conditions) {
        
        try {
            DB::start_transaction(M0080::$db);
            
            // レコード存在チェック
            $result = M0080::getDivision($conditions['division_code'], M0080::$db);

            if (count($result) == 0) {
                return Config::get('m_MW0005');
            }

            //　レコード更新
            $result = M0080::updDivision($conditions, M0080::$db);
            if (!$result) {
                Log::error(Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                return Config::get('m_ME0004');
            }

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '課マスタ', M0080::$db);
            if (!$result) {
                Log::error(Config::get('m_CE0007'));
                return Config::get('m_CE0007');
            }
            
            DB::commit_transaction(M0080::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0080::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
        
        return null;
    }
    
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
        	'division_code',
            'branch_office_code',
        	'carrier_code',
        	'division_name',
        	'private_line_number',
        	'fax_number',
            'mobile_phone_number',
            'person_in_charge',
        ), '');
        
        if (!empty(Input::param('execution')) && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            // 入力値チェックのエラー判定
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_column = '';
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/carrier_code/', $key)) {
                        $error_column = '庸車先コード';
                    } elseif (preg_match('/division_name/', $key)) {
                        $error_column = '課名';
                    } elseif (preg_match('/private_line_number/', $key)) {
                       $error_column = '専用回線';
                    } elseif (preg_match('/fax_number/', $key)) {
                       $error_column = 'FAX番号';
                    } elseif (preg_match('/mobile_phone_number/', $key)) {
                        $error_column = '携帯電話';
                    } elseif (preg_match('/person_in_charge/', $key)) {
                       $error_column = '担当者';
                    }
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    }
                    break;
                }
            }
            
            if (empty($error_msg)) {
                // 更新処理
                $error_msg = $this->update_record($conditions);
                
                // 課リスト再取得
                $this->division_list = GenerateList::getDivisionList(false, M0080::$db);
            }
            
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0080_list');
            Session::set('m0080_list', $conditions);
            
        } else {
            if ($cond = Session::get('m0080_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('m0080_list');
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                
                foreach ($conditions as $key => $val) {
                    $conditions[$key] = Input::param($key, ''); // 検索項目
                }
                
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
            }

            //初期表示
            if (empty(Input::param('division_code'))) {
                reset($this->division_list);
                $division_code = key($this->division_list);
                
                $result = M0080::getDivision($division_code, M0080::$db);
                if (count($result) > 0) {
                    // レコード取得出来たら値をセット
                    foreach ($result[0] as $key => $val) {
                        $conditions[$key] = $result[0][$key];
                    }
                }
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                     => $conditions,
                'division_list'            => $this->division_list,
                'branch_office_list'       => $this->branch_office_list,
                'error_message'            => $error_msg,
            )
        );
        
    }
}
