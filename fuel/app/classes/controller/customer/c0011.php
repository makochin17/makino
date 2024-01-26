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
use \Model\Customer\C0011;

class Controller_Customer_C0011 extends Controller_Hybrid {

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

    // 性別リスト
    private $sex_list           = array();
    // お客様区分リスト
    private $customer_type_list = array();
    // 退会フラグリスト
    private $resign_flg_list    = array();

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
        $cnf['header_title']                = 'お客様情報登録';
        $cnf['page_id']                     = '[C0011]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'お客様情報登録';
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
            'customer/c0011.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'customer/c0011.js',
            'customer/c0011_form.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 性別リスト取得
        $this->sex_list                 = GenerateList::getSexList(true, C0011::$db);
        // お客様区分リスト
        $this->customer_type_list       = GenerateList::getCustomerTypeList(true, C0011::$db);
        // 退会フラグリスト
        $this->resign_flg_list          = GenerateList::getResignFlgList(false, C0011::$db);

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

        if ($code_list = Session::get('select_customer_code')) {
            // 履歴の検索にてレコード選択された場合
            $customer_code_list = explode(",", $code_list);
            $customer_code_count = 0;
            if (is_countable($customer_code_list)){
                $customer_code_count = count($customer_code_list);
            }
            for($i = 0; $i < $customer_code_count; $i++){
                if ($result = C0011::getDispatchShare($customer_code_list[$i], C0011::$db)) {
                    $list_no = $i;
                    $conditions[$list_no]['customer_type']          = $result['customer_type'];
                    $conditions[$list_no]['customer_code']          = $result['customer_code'];
                    $conditions[$list_no]['customer_name']          = $result['customer_name'];
                    $conditions[$list_no]['customer_name_kana']     = $result['customer_name_kana'];
                    $conditions[$list_no]['zip']                    = $result['zip'];
                    $conditions[$list_no]['addr1']                  = $result['addr1'];
                    $conditions[$list_no]['addr2']                  = $result['addr2'];
                    $conditions[$list_no]['tel']                    = $result['tel'];
                    $conditions[$list_no]['fax']                    = $result['fax'];
                    $conditions[$list_no]['mobile']                 = $result['mobile'];
                    $conditions[$list_no]['mail_address']           = $result['mail_address'];
                    $conditions[$list_no]['office_name']            = $result['office_name'];
                    $conditions[$list_no]['manager_name']           = $result['manager_name'];
                    $conditions[$list_no]['birth_date']             = $result['birth_date'];
                    $conditions[$list_no]['sex']                    = $result['sex'];
                    $conditions[$list_no]['resign_flg']             = $result['resign_flg'];
                    $conditions[$list_no]['resign_date']            = $result['resign_date'];
                    $conditions[$list_no]['resign_reason']          = $result['resign_reason'];
                } else {
                    $error_msg = Config::get('m_DW0001');
                }
            }
            Session::delete('select_customer_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        $validation = Validation::forge('valid_customer');
        $validation->add_callable('myvalidation');

        // お客様区分チェック
        $validation->add('customer_type', 'お客様区分')
            ->add_rule('required_select');
        // お客様番号チェック
        $validation->add('customer_code', 'お客様番号')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 10);
        // お客様名チェック
        $validation->add('customer_name', 'お客様名')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250);
        // お客様名かなチェック
        $validation->add('customer_name_kana', 'お客様名かな')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250);
        // 郵便番号チェック
        $validation->add('zip', '郵便番号')
            ->add_rule('required')
            ->add_rule('valid_zip');
        // 住所チェック
        $validation->add('addr1', '住所')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250);
        // 電話番号チェック
        $validation->add('tel', '電話番号')
            ->add_rule('required')
            ->add_rule('valid_phone_no');
        // FAX番号チェック
        $validation->add('fax', 'FAX番号')
            ->add_rule('required')
            ->add_rule('valid_phone_no');
        // 携帯電話番号チェック
        $validation->add('mobile', '携帯電話番号')
            ->add_rule('required')
            ->add_rule('valid_phone_no');
        // メールアドレスチェック
        $validation->add('mail_address', 'メールアドレス')
            ->add_rule('required')
            ->add_rule('valid_mail');
        // 勤務先名チェック
        $validation->add('office_name', '勤務先名')
            ->add_rule('required');
        // 生年月日チェック
        $validation->add('birth_date', '生年月日')
            ->add_rule('valid_date_format');
        // 性別チェック
        $validation->add('sex', '性別')
            ->add_rule('required_select');

        $validation->run();

        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0011::$db);

            $error_msg = C0011::create_record($conditions, C0011::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(C0011::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0011::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_CUS004')."');</script>";
        return null;
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
        $conditions         = C0011::getForms('customer');
        $select_record      = Input::param('select_record', '');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('c0011_list');
        } elseif (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            Session::delete('c0011_list');
            \Response::redirect(\Uri::create('customer/c0010'));
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = C0011::setForms('customer', $conditions, Input::param());

            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_column = '';
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/customer_type/', $key)) {
                        $error_column = 'お客様区分';
                    } elseif (preg_match('/customer_code/', $key)) {
                        $error_column = 'お客様番号';
                    } elseif (preg_match('/customer_name/', $key)) {
                        $error_column = 'お客様名';
                    } elseif (preg_match('/customer_name_kana/', $key)) {
                        $error_column = 'お客様名かな';
                    } elseif (preg_match('/zip/', $key)) {
                        $error_column = '郵便番号';
                    } elseif (preg_match('/addr1/', $key)) {
                        $error_column = '住所';
                    } elseif (preg_match('/tel/', $key)) {
                        $error_column = '電話番号';
                    } elseif (preg_match('/fax/', $key)) {
                        $error_column = 'FAX番号';
                    } elseif (preg_match('/mobile/', $key)) {
                        $error_column = '携帯電話番号';
                    } elseif (preg_match('/mail_address/', $key)) {
                        $error_column = 'メールアドレス';
                    } elseif (preg_match('/office_name/', $key)) {
                        $error_column = '勤務先名';
                    } elseif (preg_match('/birth_date/', $key)) {
                        $error_column = '生年月日';
                    } elseif (preg_match('/sex/', $key)) {
                        $error_column = '性別';
                    }
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    } elseif ($validation->error()[$key]->rule == 'valid_zip') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0026'));
                    } elseif ($validation->error()[$key]->rule == 'valid_phone_no') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0027'));
                    } elseif ($validation->error()[$key]->rule == 'valid_mail') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0023'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(C0011::$db);

                    switch ($conditions['mode']){
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
                            $error_msg = $this->delete_record($conditions);
                            break;
                    }

                    if (empty($error_msg)) {
                        DB::commit_transaction(C0011::$db);
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = C0011::getForms();
                    Session::delete('c0011_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(C0011::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('c0011_list');
            Session::set('c0011_list', $conditions);
        } else {
            $conditions = C0011::setForms('customer', $conditions, Input::param());
            if ($cond = Session::get('c0011_list', array())) {
                $conditions = $cond;
                Session::delete('c0011_list');
            }

            if (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
            }

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('customer/c1010')),

                'data'                      => $conditions,

                'sex_list'                  => $this->sex_list,
                'customer_type_list'        => $this->customer_type_list,
                'resign_flg_list'           => $this->resign_flg_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),

                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

    // 画面キー操作でのデータ取得
    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = C0011::getNameById($type, $code, C0011::$db);

        return $this->response($data);
    }

    public function action_master() {

        $data               = array();
        $type               = Input::param('type', '');
        $carrying_line_no   = Input::param('carrying_line_no', '');

        $data = C0011::getMasterList($type, C0011::$db);
        $data['carrying_line_no'] = $carrying_line_no;

        return $this->response($data);
    }

}
