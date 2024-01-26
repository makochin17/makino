<?php
/**
 * 通知データメンテナンス画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\System\M0070;

class Controller_System_M0070 extends Controller_Hybrid {

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
    // 課リスト
    private $division_list = array();
    // 役職リスト
    private $position_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '通知データメンテナンス';
        $cnf['page_id'] 				    = '[M0070]';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'マスタメンテナンス業務';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController());
		$cnf['tree']['page_title'] 			= '通知データメンテナンス';

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
        
        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(true, M0070::$db);
        
        // 役職リスト取得
        $this->position_list = GenerateList::getPositionList(true, M0070::$db);
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

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;
        
		if ($code = Session::get('select_notice_code')) {
            // 検索にてレコード選択された場合
            $result = M0070::getNotice($code, M0070::$db);
            if (count($result) > 0) {
                // レコード取得出来たら値をセット
                foreach ($result[0] as $key => $val) {
                    $conditions[$key] = $result[0][$key];
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_notice_code');
        }
        
        return $error_msg;
	}
    
    // 登録処理
    private function create_record($conditions) {
        // レコード登録
        $result = M0070::addNotice($conditions, M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_ME0003')."[".print_r($conditions,true)."]");
            return Config::get('m_ME0003');
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0005', Config::get('m_MI0005'), '通知データ', M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0005')."');</script>";
        
        return null;
    }
    
    // 更新処理
    private function update_record($conditions) {
        // レコード存在チェック
        $result = M0070::getNotice($conditions['notice_number'], M0070::$db);
        
        if (count($result) == 0) {
            return Config::get('m_MW0005');
        }
        
        //　レコード更新
        $result = M0070::updNotice($conditions, M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_ME0004')."[".print_r($conditions,true)."]");
            return Config::get('m_ME0004');
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '通知データ', M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
        
        return null;
    }
    
    // 削除処理
    private function delete_record($code) {
        // レコード存在チェック
        $result = M0070::getNotice($code, M0070::$db);
        
        if (count($result) == 0) {
            return Config::get('m_MW0005');
        }
        
        // レコード削除（物理）
        $result = M0070::delNotice($code, M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_ME0005')."[notice_number:".$code."]");
            return Config::get('m_ME0005');
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '通知データ', M0070::$db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
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
        	'notice_number',
        	'division',
        	'position',
            'notice_date',
            'notice_title',
            'notice_message',
            'notice_start',
            'notice_end',
        ), '');
        
        if (!empty(Input::param('input_clear')) && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            
            Session::delete('m0070_list');
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
            
            // 入力項目チェック
            $error_column = '';
            if ($conditions['processing_division'] != '1'){
                $error_column .= (empty($conditions['notice_number'])) ? '通知番号、' : '' ;
            }
            if ($conditions['processing_division'] != '3' && empty($error_column)){
                $error_column .= (empty($conditions['division'])) ? '課、' : '' ;
                $error_column .= (empty($conditions['position'])) ? '役職、' : '' ;
                $error_column .= (empty($conditions['notice_date'])) ? '通知日付、' : '' ;
                $error_column .= (empty($conditions['notice_message'])) ? '通知メッセージ、' : '' ;
                $error_column .= (empty($conditions['notice_start'])) ? '通知開始日、' : '' ;
                $error_column .= (empty($conditions['notice_end'])) ? '通知終了日、' : '' ;
            }
            $error_column = rtrim($error_column, '、');
            
            if (!empty($error_column)) {
                if ($error_column == '通知番号') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_MW0014'));
                } else {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                }
            }
            
            if (empty($error_msg) && $conditions['processing_division'] != '3') {
                // 入力項目相関チェック
                if (strtotime($conditions['notice_start']) > strtotime($conditions['notice_end'])) {
                    $error_msg = str_replace('XXXXX','通知開始日および通知終了日',Config::get('m_CW0007'));
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
                        $error_msg = $this->delete_record($conditions['notice_number']);
                        break;
                }
            }
            
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0070_list');
            Session::set('m0070_list', $conditions);
            
        } else {
            if ($cond = Session::get('m0070_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('m0070_list');
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
                'division_list'            => $this->division_list,
                'position_list'            => $this->position_list,
                'error_message'            => $error_msg,
            )
        );
        
    }
}
