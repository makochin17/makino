<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Car\C0010;
use \Model\Car\C0021;

class Controller_Car_C0021 extends Controller_Hybrid {

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

    // 会社マスタリスト
    private $company_list       = array();
    // タイヤ種別リスト
    private $tire_kind_list     = array();
    // 保管場所リスト
    private $location_list      = array();
    // YES/NOリスト
    private $yes_no_list        = array();
    // 作業所要時間リスト
    private $work_time_list     = array();

    // ユーザ情報
    private $user_authority     = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'detail':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '車両情報詳細';
        $cnf['page_id']                     = '[C0021]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '車両情報詳細';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

        if ($this->mode == 'reset') {
            $header                         = View::forge('header_logout');
        } else {
            $header                         = View::forge($this->header);
        }
        $head                               = View::forge($this->head);
        $tree                               = View::forge($this->tree);
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
            // 'jquery_ui/jquery.ui.core.css',
            // 'jquery_ui/jquery.ui.datepicker.css',
            // 'jquery_ui/jquery.ui.theme.css',
            'common/jquery.jqplot.css',
            'common/jqModal.css'
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'common/style.css',
            'common/modal.css',
            'car/c0021.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'car/c0021.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 会社マスタリスト
        $this->company_list             = GenerateList::getCompanyList(true, C0010::$db);
        // タイヤ種別リスト
        $this->tire_kind_list           = GenerateList::getTireKindList(true, C0010::$db);
        // 保管場所リスト
        $this->location_list            = GenerateList::getLocationList(true, C0010::$db);
        // YES/NOリスト
        $this->yes_no_list              = GenerateList::getYesnoFlgList(false);
        // 作業所要時間リスト
        $this->work_time_list           = GenerateList::getWorkTimeList(false);

        // ユーザ権限取得
        $this->user_authority           = $auth_data['user_authority'];
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
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
	}

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $cnt                = 0;
        $error_msg          = null;
        $init_flag          = false;
        $redirect_flag      = false;
        $conditions         = C0021::getForms('car');
        $conditions         = C0021::setForms('car', $conditions, Input::param());

        if (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            \Response::redirect(\Uri::create('car/c0020'));
        }

        // 詳細情報取得
        if (!empty($conditions['car_id']) && !empty($conditions['customer_code'])) {
            $conditions     = C0021::getCar($conditions['car_id'], $conditions['customer_code'], C0021::$db);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                      => $conditions,
                'docroot'                   => DOCROOT,

                'company_list'              => $this->company_list,
                'location_list'             => $this->location_list,
                'yes_no_list'               => $this->yes_no_list,
                'work_time_list'            => $this->work_time_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),

                'error_message'             => $error_msg
            )
        );

    }

}
