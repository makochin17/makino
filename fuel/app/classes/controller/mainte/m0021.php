<?php
/**
 * ユニットマスタ入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0020\M0024;
use \Model\Search\S0021;

class Controller_Mainte_M0021 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 予約タイプリスト
    private $schedule_type_list = array();
    // 顧客表示フラグリスト
    private $disp_flg_list      = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= 'ユニットマスタ登録';
        $cnf['page_id'] 				    = '[M0021]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'ユニットマスタ登録';
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

        // 予約タイプリスト
        $this->schedule_type_list   = GenerateList::getScheduleTypeList(false);
        // 顧客表示フラグリスト
        $this->disp_flg_list        = GenerateList::getDispFlgList(false);

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
        // ユニット名チェック
        $validation->add('unit_name', 'ユニット名')
            ->add_rule('required')
            // ->add_rule('master_duplicate', 'name', 'm_unit')
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

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(M0024::$db);

            $error_msg = M0024::create_record($conditions, M0024::$db);
            if (!is_null($error_msg)) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(M0024::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0024::$db);

            $end_msg = Config::get('m_MI0005');
            echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0024::$db);
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
            'schedule_type',
            'disp_flg',
            'unit_name',
        ), '');

        if (!empty(Input::param('back')) && Security::check_token()) {
            // 「戻る」ボタン押下
            // 検索画面へリダイレクト
            Session::delete('m0021_list');
            \Response::redirect(\Uri::create('mainte/m0020'));
        } elseif (!empty(Input::param('execution')) && Security::check_token()) {
            // 「次へ」ボタン押下
            // 確定ボタンが押下された場合の処理
            if ($cond = Session::get('m0021_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // セッションに検索条件を設定
            Session::set('m0021_list', $conditions);
            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'unit_name':
                            $error_column = 'ユニット名';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'master_duplicate') {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0024'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }

            if (empty($error_msg)) {
                // 登録処理
                $error_msg = $this->create_record($conditions);
            }

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0021_list');
                \Response::redirect(\Uri::create('mainte/m0020'));
            }

        } else {
            if ($cond = Session::get('m0021_list', array())) {
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
            Session::set('m0021_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'schedule_type_list'            => $this->schedule_type_list,
                'disp_flg_list'                 => $this->disp_flg_list,
                'error_message'                 => $error_msg
            )
        );
    }
}
