<?php
/**
 * ユニットマスタ入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;

use \Model\System\C0080;

class Controller_System_C0080 extends Controller_Hybrid {

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
		$cnf['header_title'] 				= '会社情報';
        $cnf['page_id'] 				    = '[C0080]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '会社情報';
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
		$this->template->head       = $head;
        $this->template->header     = $header;
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

    private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');

        // 運営会社名チェック
        $validation->add('company_name', '運営会社名')
            ->add_rule('required')
        ;
        // システム名チェック
        $validation->add('system_name', 'システム名')
            ->add_rule('required')
        ;
        // 営業開始時間チェック
        $validation->add('start_time', '営業開始時間')
            ->add_rule('required')
            ->add_rule('valid_time_format')
        ;
        // 営業終了時間チェック
        $validation->add('end_time', '営業終了時間')
            ->add_rule('required')
            ->add_rule('valid_time_format')
        ;
        // 夏タイヤ残溝警告（赤表示）数チェック
        $validation->add('summer_tire_warning', '夏タイヤ残溝警告（赤表示）数')
            ->add_rule('required')
            ->add_rule('is_numeric_decimal', 2, true)
        ;
        // 夏タイヤ残溝注意（黄色表示）数チェック
        $validation->add('summer_tire_caution', '夏タイヤ残溝注意（黄色表示）数')
            ->add_rule('required')
            ->add_rule('is_numeric_decimal', 2, true)
        ;
        // 冬タイヤ残溝警告（赤表示）数チェック
        $validation->add('winter_tire_warning', '冬タイヤ残溝警告（赤表示）数')
            ->add_rule('required')
            ->add_rule('is_numeric_decimal', 2, true)
        ;
        // 冬タイヤ残溝注意（黄色表示）数チェック
        $validation->add('winter_tire_caution', '冬タイヤ残溝注意（黄色表示）数')
            ->add_rule('required')
            ->add_rule('is_numeric_decimal', 2, true)
        ;
		$validation->run();
		return $validation;
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

		if ($code = Session::get('select_client_company_code')) {
            // 得意先会社の検索にてレコード選択された場合
            $result = S0021::getSearchClientCompany($code, S0021::$db);
            if (count($result) > 0) {
                $conditions['client_company_code'] = $result[0]['client_company_code'];
                $conditions['l_client_company_name'] = $result[0]['company_name'];
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_client_company_code');
        }

        return $error_msg;
	}

    // 設定処理
    private function set_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0080::$db);

            if ($list = C0080::getCompany($conditions['id'], C0080::$db)) {
                $error_msg  = C0080::update_record($list['id'], $conditions, C0080::$db);
                $end_msg    = Config::get('m_CO0005');
            } else {
                $error_msg  = C0080::insert_record($conditions, C0080::$db);
                $end_msg    = Config::get('m_CO0004');
            }

            if (!is_null($error_msg)) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(C0080::$db);
                return $error_msg;
            }

            DB::commit_transaction(C0080::$db);

            echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0080::$db);
            // var_dump($e->getMessage());
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
            'id',
            'company_name',
            'system_name',
            'start_time',
            'end_time',
            'summer_tire_warning',
            'summer_tire_caution',
            'winter_tire_warning',
            'winter_tire_caution',
        ), '');

        if (!empty(Input::param('input_clear')) && Security::check_token()) {
            Session::delete('c0080_list');
        } elseif (!empty(Input::param('execution')) && Security::check_token()) {
            // 「次へ」ボタン押下
            // 確定ボタンが押下された場合の処理
            if ($cond = Session::get('c0080_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // セッションに検索条件を設定
            Session::set('c0080_list', $conditions);
            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'company_name':
                            $error_column = '運営会社名';
                            break;
                        case 'system_name':
                            $error_column = 'システム名';
                            break;
                        case 'start_time':
                            $error_column = '営業開始時間';
                            break;
                        case 'end_time':
                            $error_column = '営業終了時間';
                            break;
                        case 'summer_tire_warning':
                            $error_column = '夏タイヤ残溝警告（赤表示）数';
                            break;
                        case 'summer_tire_caution':
                            $error_column = '夏タイヤ残溝注意（黄色表示）数';
                            break;
                        case 'winter_tire_warning':
                            $error_column = '冬タイヤ残溝警告（赤表示）数';
                            break;
                        case 'winter_tire_caution':
                            $error_column = '冬タイヤ残溝注意（黄色表示）数';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                $error_msg = $this->set_record($conditions);
            }

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('c0080_list');
                // \Response::redirect(\Uri::create('system/c0080'));
            }

        } else {
            // 会社情報取得
            $conditions = C0080::getCompany($conditions['id'], C0080::$db);

            if ($cond = Session::get('c0080_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            if (!empty(Input::param('select_record'))) {
                // // 検索画面からコードが連携された場合の処理
                // foreach ($conditions_company as $key => $val) {
                //     $conditions[$key] = Input::param($key, ''); // 検索項目
                // }
                // // 連携されたコードによる情報取得＆値セット
                // $error_msg = $this->set_info($conditions);
            }

            //セッションに値を保持
            Session::set('c0080_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'error_message'                 => $error_msg
            )
        );
    }
}
