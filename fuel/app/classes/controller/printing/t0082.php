<?php
/**
 * 請求明細印刷画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Printing\T0082;
use \Model\Search\S0080;
use \Model\Mainte\M0020\M0020;

class Controller_Printing_T0082 extends Controller_Hybrid {

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

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '請求明細印刷';
        $cnf['page_id'] 				    = '[T0082]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '請求明細印刷';
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
        $this->division_list = GenerateList::getDivisionList(false, T0082::$db);
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
        
		if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            $result = S0080::getSearchClient($code, T0082::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_TW0002');
            }
            Session::delete('select_client_code');
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
            'client_radio',
        	'client_code',
            'target_date',
            'target_date_day',
        ), '');
        
        foreach ($conditions as $key => $val) {
            $conditions[$key] = Input::param($key, ''); // 検索項目
        }
        
        if (!empty(Input::param('output'))) {
            
            Session::set('t0082_list', $conditions);
            
            $closing_date = '';
            if ($conditions['client_radio'] == 2) {
                //得意先存在チェック
                $client_data = M0020::getClient($conditions['client_code'],T0082::$db);
                if (empty($client_data)) {
                    $error_msg = \Config::get('m_TW0002');
                } else {
                    $closing_date = $client_data[0]['closing_date'];
                }
            }
            
            //対象日付の入力チェック（締日が随時請求ならチェックしない）
            if ($closing_date != 50) {
                //対象年月の入力チェック
                if (empty($conditions['target_date'])) {
                    $error_msg = str_replace('XXXXX','対象年月',\Config::get('m_CW0005'));
                }
            } else {
                //対象日付の入力チェック
                if (empty($conditions['target_date_day'])) {
                    $error_msg = str_replace('XXXXX','対象日付',\Config::get('m_CW0005'));
                }
            }
            
            if (empty($error_msg)) {
                // 帳票出力
                $error_msg = T0082::createExcel();
            }
            
        } else {
            if ($cond = Session::get('t0082_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('t0082_list');
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
                
                Session::set('t0082_list', $conditions);
            }
            
        }
        
        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'division_list'                 => $this->division_list,
                'error_message'                 => $error_msg
            )
        );
        
    }
    
}
