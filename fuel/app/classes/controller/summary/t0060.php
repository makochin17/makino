<?php
/**
 * ドライバー別売上集計画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Summary\T0060;
use \Model\Search\S0080;

class Controller_Summary_T0060 extends Controller_Hybrid {

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
    // 年リスト
    private $year_list = array();
    // 月リスト
    private $month_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= 'ドライバー別売上集計';
        $cnf['page_id'] 				    = '[T0060]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'ドライバー別売上集計';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

		$head   							= View::forge($this->head);
        $tree                               = View::forge($this->tree);
		$header 							= View::forge($this->header);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
        $tree->tree                         = $cnf['tree'];
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
		$this->template->sidemenu 	= $sidemenu;
		$this->template->footer 	= $footer;
        $this->template->tree       = $tree;

        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(false, T0060::$db);
        // 年リスト
        $this->year_list = GenerateList::getYearList();
        // 月リスト
        $this->month_list = GenerateList::getMonthList();
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
        
		if ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            $result = S0080::getSearchMember($code, S0080::$db);
            if (count($result) > 0) {
                $conditions['member_code'] = $result[0]['member_code'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        }
        
        return $error_msg;
	}
    
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
        	'division',
            'member_radio',
        	'member_code',
            'start_date',
            'end_date',
            'fare_radio',
        ), '');
        
        foreach ($conditions as $key => $val) {
            $conditions[$key] = Input::param($key, ''); // 検索項目
        }
        
        if (!empty(Input::param('output'))) {
            
            Session::set('t0060_list', $conditions);
            
            //集計開始・終了の入力チェック
            $result = T0060::checkDate();
            if (!empty($result)) {
                $error_msg = $result;
            } else {
                // 帳票出力
                $error_msg = T0060::createExcel();
            }
            
        } else {
            if ($cond = Session::get('t0060_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('t0060_list');
            } else {
                $conditions['end_date'] = date('Y-m').'-20';
                $conditions['start_date'] = date('Y-m', strtotime($conditions['end_date'] . '-1 month')).'-21';
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
                
                Session::set('t0060_list', $conditions);
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'division_list'                 => $this->division_list,
                'year_list'                     => $this->year_list,
                'month_list'                    => $this->month_list,
                'error_message'                 => $error_msg
            )
        );
        
    }
}
