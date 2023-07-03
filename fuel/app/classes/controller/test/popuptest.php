<?php
/**
 * ユーザーマスタ一覧画面
 */
//use \Model\Init;
use \Model\AccessControl;
//use \Model\Test\Popuptest;

class Controller_Test_Popuptest extends Controller_Hybrid {

	protected $format = 'json';

	// テンプレート定義
	public $template  	= 'template_base_popup';
	private $head     	= 'head';
	//private $header   	= 'header';
	//private $tree 		= 'tree';
	//private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

	// ページネーション
	private $pagenation_config = array(
		//'uri' => $this->category_page,
		//'uri_segment' => 3,
	    'uri_segment' 	=> 'p',
		'num_links' 	=> 2,
		'per_page' 		=> 50,
		'name' 			=> 'default',
		'show_first' 	=> true,
		'show_last' 	=> true,
	);

	/**
	* 画面共通初期設定
	**/
	private function initViewForge($auth_data){

		// サイト設定
		$cnf 								= \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '車種検索';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'システム管理／名簿マスタ';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController().'?init');
		$cnf['tree']['page_title'] 			= '名簿一覧';

		$head   							= View::forge($this->head);
		//$header 							= View::forge($this->header);
		//$tree   							= View::forge($this->tree);
		//$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		//$header->header_title				= $cnf['header_title'];
		//$tree->tree							= $cnf['tree'];
		//$sidemenu->system_title				= $cnf['system_title'];
		//$sidemenu->system_title_alpha		= $cnf['system_title_alpha'];
		//$sidemenu->copyright				= $cnf['copyright'];

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

		$ary_footer_js = array(
			'common/jquery.min.js',
			'common/skel.min.js',
			'common/util.js',
			'common/main.js',
			'master/common.js'
		);
		Asset::js($ary_footer_js, array(), 'footer_js', false);
		// テンプレートに渡す定義
		$this->template->head   	= $head;
		//$this->template->header 	= $header;
		//$this->template->tree 		= $tree;
		//$this->template->sidemenu 	= $sidemenu;
		$this->template->footer 	= $footer;

	}

	public function before() {
		parent::before();
		// ログインチェック
		//if(!Auth::check()) {
		//	Response::redirect(\Uri::base(false));
		//}

		// 初期設定(共通画面設定)
		//Init::getInitialize();
		// 担当者情報を設定
		//$auth_data = Init::get('auth_data');
                $auth_data = null;

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}

	public function action_index() {
        
        

        /**
         * 検索項目の取得＆初期設定
         */
        $committee_list = null;//Popuptest::getCommittee(Popuptest::$db);
        $pageno     	= Input::param('p', '1');
		$error_msg 		= array();
        $conditions 	= array_fill_keys(array(
        	'from_code',
        	'to_code',
        	'name'
        ), '');

        /**
         * 検索条件の設定
         */
        if (Input::method() == 'POST') {
            $code = Session::get('select_code');
            $alert = "<script type='text/javascript'>alert('code:". $code. "');</script>";
            echo $alert;
            Session::delete('select_code');
        }
        if (Input::method() == 'POST' && Security::check_token()) {

            foreach ($conditions as $key => $val) {

                $conditions[$key] = Input::param($key, ''); // 検索項目

            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('mroster_list');
            Session::set('mroster_list', $conditions);
        } else {
            if ($cond = Session::get('mroster_list', array())) {

                foreach ($cond as $key => $val) {

                    $conditions[$key] = $val;

                }

            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('mroster_list', $conditions);
        }

       /**
         * ページング設定&検索実行
         */
        $total                      = 0;//Common::getSearch('index', true, $conditions, null, null, Common::$db);
		$this->pagenation_config	+= array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
		$pagination 				= Pagination::forge('mypagination', $this->pagenation_config);
		$limit 						= $pagination->per_page;
		$offset 					= $pagination->offset;
        $list_data                  = array();
        if ($total > 0) {
            $list_data              = null;//Common::getSearch('index', false, $conditions, $offset, $limit, Common::$db);
        }

		$this->template->content = View::forge(AccessControl::getActiveController(),
												array(
                                                    'total'                 => $total,
                                                    'data'                  => $conditions,
                                                    'list_data'             => $list_data,
                                                    'offset'                => $offset,
                                                    'pageno'                => $pageno,
                                                    'list'                	=> $committee_list,
												)
		);
		$this->template->content->set_safe('pager', $pagination->render(0, 'out'));

	}

}
