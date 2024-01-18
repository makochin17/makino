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
use \Model\Car\C0013;

class Controller_Car_C0013 extends Controller_Hybrid {

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
        $cnf['page_id']                     = '[C0013]';
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
            'car/c0013.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'car/c0013.js',
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

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = C0010::getSearchCustomer($code, C0010::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        $validation = Validation::forge('valid_car');
        $validation->add_callable('myvalidation');

        // 旧車両IDチェック
        $validation->add('old_car_id', '車両ID')
            ->add_rule('required')
            ->add_rule('is_numeric')
        ;
        // 登録番号/車両番号チェック
        $validation->add('car_code', '登録番号')
            ->add_rule('required')
        ;
        // お客様番号チェック
        $validation->add('customer_code', 'お客様番号')
            ->add_rule('required')
        ;
        // お客様名チェック
        $validation->add('customer_name', 'お客様')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 所有者名チェック
        $validation->add('owner_name', '所有者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 使用者名チェック
        $validation->add('consumer_name', '使用者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 車種名チェック
        $validation->add('car_name', '車種')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;

        // 夏タイヤメーカーチェック
        if (!empty($conditions['summer_tire_maker'])) {
            $validation->add('summer_tire_maker', '夏タイヤメーカー')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ商品名チェック
        if (!empty($conditions['summer_tire_product_name'])) {
            $validation->add('summer_tire_product_name', '夏タイヤ商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤサイズチェック
        if (!empty($conditions['summer_tire_size'])) {
            $validation->add('summer_tire_size', '夏タイヤサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤタイヤパターンチェック
        if (!empty($conditions['summer_tire_pattern'])) {
            $validation->add('summer_tire_pattern', '夏タイヤタイヤパターン')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤホイール商品名チェック
        if (!empty($conditions['summer_tire_wheel_product_name'])) {
            $validation->add('summer_tire_wheel_product_name', '夏タイヤホイール商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤホイールサイズチェック
        if (!empty($conditions['summer_tire_wheel_size'])) {
            $validation->add('summer_tire_wheel_size', '夏タイヤホイールサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ製造年チェック
        if (!empty($conditions['summer_tire_made_date'])) {
            $validation->add('summer_tire_made_date', '夏タイヤ製造年')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ残溝数１チェック
        if (!empty($conditions['summer_tire_remaining_groove1'])) {
            $validation->add('summer_tire_remaining_groove1', '夏タイヤ残溝数１')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数２チェック
        if (!empty($conditions['summer_tire_remaining_groove2'])) {
            $validation->add('summer_tire_remaining_groove2', '夏タイヤ残溝数２')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数３チェック
        if (!empty($conditions['summer_tire_remaining_groove3'])) {
            $validation->add('summer_tire_remaining_groove3', '夏タイヤ残溝数３')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数４チェック
        if (!empty($conditions['summer_tire_remaining_groove4'])) {
            $validation->add('summer_tire_remaining_groove4', '夏タイヤ残溝数４')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤパンク、傷チェック
        if (!empty($conditions['summer_tire_punk'])) {
            $validation->add('summer_tire_punk', '夏タイヤパンク')
                ->add_rule('trim_max_lengths', 250)
            ;
        }

        // 冬タイヤメーカーチェック
        if (!empty($conditions['winter_tire_maker'])) {
            $validation->add('winter_tire_maker', '冬タイヤメーカー')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ商品名チェック
        if (!empty($conditions['winter_tire_product_name'])) {
            $validation->add('winter_tire_product_name', '冬タイヤ商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤサイズチェック
        if (!empty($conditions['winter_tire_size'])) {
            $validation->add('winter_tire_size', '冬タイヤサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤタイヤパターンチェック
        if (!empty($conditions['winter_tire_pattern'])) {
            $validation->add('winter_tire_pattern', '冬タイヤタイヤパターン')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤホイール商品名チェック
        if (!empty($conditions['winter_tire_wheel_product_name'])) {
            $validation->add('winter_tire_wheel_product_name', '冬タイヤホイール商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤホイールサイズチェック
        if (!empty($conditions['winter_tire_wheel_size'])) {
            $validation->add('winter_tire_wheel_size', '冬タイヤホイールサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ製造年チェック
        if (!empty($conditions['winter_tire_made_date'])) {
            $validation->add('winter_tire_made_date', '冬タイヤ製造年')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ残溝数１チェック
        if (!empty($conditions['winter_tire_remaining_groove1'])) {
            $validation->add('winter_tire_remaining_groove1', '冬タイヤ残溝数１')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数２チェック
        if (!empty($conditions['winter_tire_remaining_groove2'])) {
            $validation->add('winter_tire_remaining_groove2', '冬タイヤ残溝数２')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数３チェック
        if (!empty($conditions['winter_tire_remaining_groove3'])) {
            $validation->add('winter_tire_remaining_groove3', '冬タイヤ残溝数３')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数４チェック
        if (!empty($conditions['winter_tire_remaining_groove4'])) {
            $validation->add('winter_tire_remaining_groove4', '冬タイヤ残溝数４')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤパンク、傷チェック
        if (!empty($conditions['winter_tire_punk'])) {
            $validation->add('winter_tire_punk', '冬タイヤパンク')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 注意事項チェック
        if (!empty($conditions['note'])) {
            $validation->add('note', '注意事項')
                ->add_rule('trim_max_lengths', 2000)
            ;
        }
        // メッセージチェック
        if (!empty($conditions['message'])) {
            $validation->add('message', 'メッセージ')
                ->add_rule('trim_max_lengths', 2000)
            ;
        }

        $validation->run();

        return $validation;
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
        $conditions         = C0013::getForms('car');
        $conditions         = C0013::setForms('car', $conditions, Input::param());

        if (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            \Response::redirect(\Uri::create('car/c0010'));
        }

        // 詳細情報取得
        if (!empty($conditions['car_id'])) {
            $conditions     = C0013::getCar($conditions['car_id'], null, C0013::$db);
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
