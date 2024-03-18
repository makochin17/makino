<?php
/**
 * ユニットマスタ画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0020\M0025;

class Controller_Mainte_M0020 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // ページネーション
    private $pagenation_config = array(
        'uri_segment' 	=> 'p',
    	'num_links' 	=> 2,
    	'per_page' 		=> 50,
    	'name' 			=> 'default',
    	'show_first' 	=> true,
    	'show_last' 	=> true,
    );

    // 予約タイプリスト
    private $schedule_type_list = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = 'ユニットマスタ';
        $cnf['page_id']                     = '[M0020]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'ユニットマスタ';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = 'ユニットマスタ';

        $header                             = View::forge($this->header);
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
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIM0011", M0020::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

        // 予約タイプリスト
        $this->schedule_type_list = GenerateList::getScheduleTypeList(false);

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
		// ユニット名チェック
		$validation->add('unit_name', 'ユニット名')
            // ->add_rule('required')
            // ->add_rule('master_duplicate', 'name', 'm_unit')
        ;
		$validation->run();
		return $validation;
	}

    // レコード削除処理
    private function delete_record() {

        $unit_code = Input::post('unit_code', '');
        try {
            DB::start_transaction(M0020::$db);

            // レコード存在チェック
            if (!$result = M0020::getUnit($unit_code, M0020::$db)) {
                return Config::get('m_MW0003');
            }

            // レコード削除（論理）
            $error_msg = M0020::delUnit($unit_code, M0020::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(M0020::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0020::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0020::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        return null;
    }

    /**
    * 顧客表示フラグ更新
    **/
    public function edit_disp_flg($unit_code, $disp_flg) {

        try {
            DB::start_transaction(M0020::$db);

            // レコード存在チェック
            if (!$result = M0020::getUnit($unit_code, M0020::$db)) {
                return Config::get('m_MW0003');
            }

            // レコード削除（論理）
            if (empty(M0025::updUnitDispFlg($unit_code, $disp_flg, M0020::$db))) {
                DB::rollback_transaction(M0020::$db);
                return 'フラグの更新に失敗しました';
            }

            DB::commit_transaction(M0020::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0020::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 初期設定
         */
        $error_msg      = null;
        $search_flag    = true;
        $init_flag      = false;
        $conditions 	= array_fill_keys(array(
            'schedule_type',
            'disp_flg',
        	'unit_name',
        ), '');

        if (!empty(Input::param('excel'))) {
            // エクセル出力ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力値チェック
			$validation  = $this->validate_info();
			$errors      = $validation->error();
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e) {
                    // チェック項目はユニット名のみのため固定
                    $error_msg = str_replace('XXXXX','ユニット名',Config::get('m_CW0006'));
				}
			}

            // エクセル出力
            if (empty($error_msg)) {
                M0020::createTsv($conditions, M0020::$db);
            }

        }
        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理
            //ユニットデータ削除
            $error_msg = $this->delete_record();

        }

        if (Input::post('processing_division', '') == '4' && Security::check_token()) {
            // 表示フラグボタンが押下された場合の処理
            $unit_code  = Input::post('unit_code', '');
            $disp_flg   = Input::post('disp_flg', '');
            //ユニットデータ削除
            $error_msg = $this->edit_disp_flg($unit_code, $disp_flg);

        }

        if (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // 入力値チェック
			$validation  = $this->validate_info();
			$errors      = $validation->error();
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e) {
                    // チェック項目はユニット名のみのため固定
                    $error_msg = str_replace('XXXXX','ユニット名',Config::get('m_CW0006'));
				}
			}
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0020_list');
            Session::set('m0020_list', $conditions);
        } else {
            if ($cond = Session::get('m0020_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            } else {
                $search_flag = false;
            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('m0020_list', $conditions);
        }

        /**
         * ページング設定&検索実行
         */
        if (!$init_flag) {
            $total                      = M0020::getSearch('count', $conditions, null, null, M0020::$db);
        } else {
            // 初期表示時は検索しない
            $total = 0;
        }
        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('m0020_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        $list_data                      = array();

        //ページネーションのページ数をセッションに保存
        Session::set('m0020_page', Input::get('p'));

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = M0020::getSearch('search', $conditions, $offset, $limit, M0020::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                 => $total,
                'data'                  => $conditions,
                'schedule_type_list'    => $this->schedule_type_list,
                'list_data'             => $list_data,
                'offset'                => $offset,
                'error_message'         => $error_msg,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }

}
