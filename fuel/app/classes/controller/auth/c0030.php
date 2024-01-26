<?php
/**
 * 商品検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Auth\C0030;

class Controller_Auth_C0030 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 画面モード
    private $mode       = null;

    // 処理区分リスト
    private $processing_division_list = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = 'パスワード変更';
        $cnf['page_id']                     = '[C0030]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'パスワード変更';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = 'パスワード変更';

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
        // $sidemenu->system_title             = $cnf['system_title'];
        // $sidemenu->system_title_alpha       = $cnf['system_title_alpha'];
        $sidemenu->copyright                = $cnf['copyright'];

        // テンプレートに定義するCSS・JS
        $ary_jquery_ui_css = array(
            ''
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'modal/dialog.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        if ($this->mode == 'reset') {
            $this->template->tree       = '';
            $this->template->sidemenu   = '';
        } else {
            $this->template->tree       = $tree;
            $this->template->sidemenu   = $sidemenu;
        }
        $this->template->footer         = $footer;

        // 処理区分リスト取得
        // $this->processing_division_list = GenerateList::getProcessingDivisionList();
    }

	public function before() {
		parent::before();
		// ログインチェック
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
        // 旧パスワードチェック
        $validation->add('old_password', '現在のパスワード')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes', 'dots', 'commas', 'punctuation'))
        ;
        // 旧パスワードチェック
        $validation->add('new_password', '新しいパスワード')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes', 'dots', 'commas', 'punctuation'))
        ;
		// 旧パスワードチェック
		$validation->add('new_password_cf', 'パスワード確認入力')
            ->add_rule('required')
            ->add_rule('valid_strings', array('alpha', 'numeric', 'dashes', 'dots', 'commas', 'punctuation'))
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
        $mode_msg       = null;
        $modal_flg      = false;
        $login_name     = C0030::getLoginUserData('name');
        $user_id        = C0030::getLoginUserData('user_id');
        $conditions 	= array_fill_keys(array(
        	'old_password',
            'new_password',
            'new_password_cf',
        ), '');

        // パスワード有効期限切れにてログイン画面から遷移してきた場合
        if ($this->mode == 'reset') {
            $mode_msg   = Config::get('m_CI0001');;
        }

        if (Input::method() == 'POST' && Security::check_token()) {

            // POSTデータ取得
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            // 入力チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'old_password':
                            $error_column = '現在のパスワード';
                            break;
                        case 'new_password':
                            $error_column = '新しいパスワード';
                            break;
                        case 'new_password_cf':
                            $error_column = 'パスワード確認入力';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings' || $validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            // 新パスワード整合性チェック
            if (empty($error_msg) && $conditions['new_password'] != $conditions['new_password_cf']) {
                $error_msg = Config::get('m_CW0004');
            }

            // パスワード変更
            if (empty($error_msg)) {
                // パスワード生成
                if(\Auth::change_password($conditions['old_password'], $conditions['new_password'], $user_id)){
                    // パスワード変更成功
                    // パスワード有効期限算出
                    $days       = C0030::getPasswordLimit(C0030::$db);
                    $limit_date = date('Y-m-d', strtotime('+'.$days.' day'));
                    // パスワード有効期限更新
                    if (!C0030::updPasswordLimit(C0030::getLoginUserData('member_code'), $limit_date, C0030::$db)) {
                        $error_msg = 'パスワード有効期限の更新に失敗しました';
                    }
                    // 操作ログ出力
                    $result = OpeLog::addOpeLog('CI0002', Config::get('m_CI0002'), C0030::getLoginUserData('user_id'), C0030::$db);
                    if (!$result) {
                        Log::error(Config::get('m_CE0007'));
                        $error_msg = Config::get('m_CE0007');
                    } else {
                        // ログアウト処理
                        Auth::logout();
                        $modal_flg = true;
                    }
                }else{
                    // パスワード変更失敗
                    $error_msg = Config::get('m_CW0003');
                }
            }
        }
        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'modal_flg'                 => $modal_flg,
                'login_name'                => $login_name,
                'mode'                      => $this->mode,
                'mode_msg'                  => $mode_msg,
                'error_message'             => $error_msg,
            )
        );
    }
}
