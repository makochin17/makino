<?php
/**
 * 商品検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Auth\C0020;

class Controller_Auth_C0020 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_logout';
    private $head     	= 'head';
	private $header   	= 'header_logout';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 処理区分リスト
    private $processing_division_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= 'ログアウト';
        $cnf['page_id'] 				    = '[C0020]';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'ログアウト';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController());
		$cnf['tree']['page_title'] 			= 'ログアウト';

		$head   							= View::forge($this->head);
		$header 							= View::forge($this->header);
		$tree   							= View::forge($this->tree);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
		$tree->tree							= $cnf['tree'];
		// $sidemenu->system_title				= $cnf['system_title'];
		// $sidemenu->system_title_alpha		= $cnf['system_title_alpha'];
        $sidemenu->login_user_name          = $auth_data['full_name'];
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
		$this->template->head         = $head;
        $this->template->header       = $header;
		// $this->template->tree         = $tree;
		// $this->template->sidemenu     = $sidemenu;
		$this->template->footer       = $footer;

        // 処理区分リスト取得
        // $this->processing_division_list = GenerateList::getProcessingDivisionList();
	}

	public function before() {
		parent::before();
		// ログアウトチェック
		if(!Auth::check()) {
			Response::redirect(\Uri::base(false));
		}

		// 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');;

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
		// ログアウトIDチェック
		$validation->add('userid', 'ログインID')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes'))
            ->add_rule('trim_max_lengths', 10)
        ;
		// パスワードチェック
		$validation->add('password', 'パスワード')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes', 'dots', 'commas', 'punctuation'))
            ->add_rule('trim_max_lengths', 14)
        ;
		$validation->run();
		return $validation;
	}

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;

        // ログアウト処理
        Auth::logout();
        Session::delete('auth_data');

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'            => $error_msg,
            )
        );
    }
}
