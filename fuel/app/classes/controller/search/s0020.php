<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Search\S0020;

class Controller_Search_S0020 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base_popup';
    private $head     	= 'head';

    // ページネーション
    private $pagenation_config = array(
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
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '車両検索';

		$head                               = View::forge($this->head);
		$head->title                        = $cnf['header_title'];

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

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIS0020", S0020::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

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
		$this->initViewForge($auth_data);
	}

	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 車両番号チェック
        $validation->add('car_code', '車両番号')
            ->add_rule('trim_max_lengths', 10);
        // 車種名チェック
        $validation->add('car_name', '車種')
            ->add_rule('trim_max_lengths', 250);
		$validation->run();
		return $validation;
	}

    public function action_index() {

        Config::load('message');
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $init_flag      = false;
        $mode           = Input::param('mode', '');
        $conditions 	= array_fill_keys(array(
        	'car_code',
        	'car_name',
        ), '');

        if (!empty(Input::param('cancel')) && Security::check_token()) {
            // キャンセルボタンが押下された場合の処理
            Session::set('select_cancel', true);
            Session::delete('s0020_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";
        } elseif (!empty(Input::param('select')) && Security::check_token()) {
            // 選択ボタンが押下された場合の処理
            Session::set('select_car_code', Input::param('select_code'));
            Session::set('select_car_mode', $mode);
            Session::delete('s0020_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";

        } elseif (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力値チェック
			$validation = $this->validate_info();
			$errors = $validation->error();
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e) {
                    if (preg_match('/car_code/', $key)) {
                        $error_column = '車両番号';
                    } elseif (preg_match('/car_name/', $key)) {
                        $error_column = '車種';
                    }
                    // チェック項目はコードのみのため固定
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
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
				}
			}

            /**
             * セッションに検索条件を設定
             */
            Session::delete('s0020_list');
            Session::set('s0020_list', $conditions);

        } else {
            if ($cond = Session::get('s0020_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }

            } else {
                $init_flag = true;
            }

            //初期表示もエクスポートに備えて条件保存する
            Session::set('s0020_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if (!$init_flag) {
            $total                      = S0020::getSearch(true, $conditions, null, null, S0020::$db);
        } else {
            // 初期表示時は検索しない
            $total = 0;
        }
        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = S0020::getSearch(false, $conditions, $offset, $limit, S0020::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                 => $total,
                'mode'                  => $mode,
                'data'                  => $conditions,

                'list_data'             => $list_data,
                'offset'                => $offset,
                'error_message'         => $error_msg,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());

    }
}
