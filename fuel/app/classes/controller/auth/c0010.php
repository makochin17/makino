<?php
/**
 * 商品検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Auth\C0010;

class Controller_Auth_C0010 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_login';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 処理区分リスト
    private $processing_division_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge(){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= 'ログイン';
        $cnf['page_id'] 				    = '[C0010]';
		$cnf['tree']['top'] 				= \Uri::base(false);
		$cnf['tree']['management_function']	= 'ログイン';
		$cnf['tree']['page_url'] 			= \Uri::create(AccessControl::getActiveController());
		$cnf['tree']['page_title'] 			= 'ログイン';

		$head   							= View::forge($this->head);
		$header 							= View::forge($this->header);
		$tree   							= View::forge($this->tree);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
		$tree->tree							= $cnf['tree'];
		$sidemenu->system_title				= $cnf['system_title'];
		$sidemenu->system_title_alpha		= $cnf['system_title_alpha'];
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
  //       $this->template->header       = $header;
		// $this->template->tree         = $tree;
		// $this->template->sidemenu     = $sidemenu;
		// $this->template->footer       = $footer;

        // 処理区分リスト取得
        // $this->processing_division_list = GenerateList::getProcessingDivisionList();
	}

	public function before() {
		parent::before();

		$this->initViewForge();
	}

	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
		// ログインIDチェック
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
        $conditions 	= array_fill_keys(array(
        	'userid',
        	'password',
        ), '');

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
                        case 'userid':
                            $error_column = 'ログインID';
                            break;
                        case 'password':
                            $error_column = 'パスワード';
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
            // ロックアウトチェック
            if (empty($error_msg) && $data = C0010::getMemberByUserId($conditions['userid'], C0010::$db)) {
                if ($data['lock_status'] == 1) {
                    $error_msg = Config::get('m_CW0002');
                }
            }
            // ログイン認証
            if (empty($error_msg) && !empty($data)) {
                // ログイン認証を行う
                $auth = Auth::instance();
                if($auth->login($data['user_id'], $conditions['password'])) {
                    // ログイン成功
                    $login_user_data = array(
                        'member_code'           => $data['member_code'],
                        'full_name'             => $data['full_name'],
                        'name_furigana'         => $data['name_furigana'],
                        'mail_address'          => $data['mail_address'],
                        'user_id'               => $data['user_id'],
                        'user_authority'        => $data['user_authority'],
                        'lock_status'           => $data['lock_status'],
                        'password'              => $conditions['password']
                    );
                    // ログイン情報テーブルにログインした社員情報を更新
                    $auth->update_user(array('profile_fields' => $login_user_data), $data['user_id']);
                    // 操作ログ出力
                    $result = OpeLog::addOpeLog('CI0006', Config::get('m_CI0006'), $data['user_id'], C0010::$db);
                    if (!$result) {
                        Log::error(Config::get('m_CE0007'));
                        // ログアウト処理
                        Auth::logout();
                        $error_msg = Config::get('m_CE0007');
                    } else {
                        // パスワード誤り回数リセット
                        C0010::updPasswordErrorCountReset($data['member_code'], C0010::$db);
                        // パスワード有効期限チェック
                        if (C0010::getMemberPasswordDateActive($data['member_code'], C0010::$db)) {
                            // トップメニューへ
                            Response::redirect(\Uri::create('top/c0040'));
                        } else {
                            // パスワード変更へ
                            Response::redirect(\Uri::create('auth/c0030?mode=reset'));
                        }
                    }
                } else {
                    // ログイン失敗
                    // ログアウト処理
                    Auth::logout();
                    // パスワード誤り回数カウントアップ
                    C0010::updPasswordErrorCountUp($data['member_code'], C0010::$db);
                    $error_msg = Config::get('m_CW0002');
                }

            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                     => $conditions,
                'error_message'            => $error_msg,
            )
        );
    }
}
