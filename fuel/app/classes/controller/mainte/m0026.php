<?php
/**
 * 得意先会社情報編集画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Mainte\M0020\M0020;
use \Model\Search\S0021;

class Controller_Mainte_M0026 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';
    
    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '得意先会社情報編集';
        $cnf['page_id'] 				    = '[M0026]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '得意先会社情報編集';
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
    
//    private function validate_info() {
//
//		// 入力チェック
//		$validation = Validation::forge('valid_master');
//        $validation->add_callable('myvalidation');
//		// 得意先会社コードチェック
//		$validation->add('client_company_code', '得意先会社コード')
//			->add_rule('is_numeric');
//		$validation->run();
//		return $validation;
//	}
//    
//    // 検索画面にてレコード選択された場合の処理
//    private function set_info(&$conditions) {
//        $error_msg = null;
//        
//		if ($code = Session::get('select_client_company_code')) {
//            // 得意先会社の検索にてレコード選択された場合
//            $result = S0021::getSearchClientCompany($code, S0021::$db);
//            if (count($result) > 0) {
//                $conditions['client_company_code'] = $result[0]['client_company_code'];
//                $conditions['l_client_company_name'] = $result[0]['company_name'];
//            } else {
//                $error_msg = Config::get('m_MW0003');
//            }
//            Session::delete('select_client_company_code');
//        }
//        
//        return $error_msg;
//	}
    
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
            'company_radio',
            'company_name',
            'sales_office_radio',
            'sales_office_name',
            'department_radio',
            'department_name',
            'client_code',
            'client_company_code',
            'client_company_name',
            'client_sales_office_code',
            'client_sales_office_name',
            'client_department_code',
            'client_department_name',
            'closing_date',
            'criterion_closing_date',
            'official_name',
            'official_name_kana',
            'postal_code',
            'address',
            'phone_number',
            'fax_number',
            'person_in_charge_surname',
            'person_in_charge_name',
        ), '');
        $conditions_company = array_fill_keys(array(
            'company_radio',
            'company_name',
//        	'client_company_code',
//            'l_client_company_name',
        ), '');
        
        //担当部署の項目セット
        $division_list = GenerateList::getDivisionList(false, M0020::$db);
		$conditions = M0020::setDepartmentInChargeColumn($division_list, $conditions);
        
        if (!empty(Input::param('back')) && Security::check_token()) {
            // 「戻る」ボタン押下
            
            // 得意先編集画面へリダイレクト
            \Response::redirect(\Uri::create('mainte/m0025'));
        } elseif (!empty(Input::param('next')) && Security::check_token()) {
            // 「次へ」ボタン押下
            
            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_company as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            if ($conditions['company_radio'] == 1) {
                //変更なしの場合
                $conditions['company_name'] = $conditions['client_company_name'];
            } else {
                //名称変更の場合
                
                //入力値チェック
                if (empty($conditions['company_name'])) {
                    $error_msg = str_replace('XXXXX','得意先会社名',Config::get('m_CW0005'));
                }
//                //入力値チェック
//                if (empty($conditions['client_company_code'])) {
//                    $error_msg = str_replace('XXXXX','得意先会社コード',Config::get('m_CW0005'));
//                } else {
//                    //属性チェック
//                    $validation = $this->validate_info();
//                    $errors = $validation->error();
//                    if (!empty($errors)) {
//                        foreach($validation->error() as $key => $e) {
//                            // チェック項目は得意先会社コードのみのため固定
//                            $error_msg = str_replace('XXXXX','得意先会社コード',Config::get('m_CW0006'));
//                        }
//                    }
//                }
            }
            
            //チェック正常なら画面遷移
            if (empty($error_msg)) {
                //セッションに値を保持
                Session::set('m0025_list', $conditions);

                if (!empty($conditions['client_sales_office_code'])) {
                    // 次の画面へリダイレクト
                    \Response::redirect(\Uri::create('mainte/m0027'));
                } else {
                    // 得意先情報編集画面へリダイレクト
                    \Response::redirect(\Uri::create('mainte/m0025'));
                }
            }
        } else {
            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            
//            if (!empty(Input::param('select_record'))) {
//                // 検索画面からコードが連携された場合の処理
//                foreach ($conditions_company as $key => $val) {
//                    $conditions[$key] = Input::param($key, ''); // 検索項目
//                }
//                
//                // 連携されたコードによる情報取得＆値セット
//                $error_msg = $this->set_info($conditions);
//            }
            
            //セッションに値を保持
            Session::set('m0025_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'error_message'                 => $error_msg
            )
        );
        
    }
}
