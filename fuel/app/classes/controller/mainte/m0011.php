<?php
/**
 * 商品検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Common\PagingConfig;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0010\M0011;

class Controller_Mainte_M0011 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 画面モード
    private $mode       = null;

    // ページネーション
    private $pagenation_config = array(
        'uri_segment'   => 'p',
        'num_links'     => 2,
        'per_page'      => 50,
        'name'          => 'default',
        'show_first'    => true,
        'show_last'     => true,
    );

    // Uploadクラスの設定
    private $upload_config = array(
        'randomize'     => true,
        'ext_whitelist' => array('xls', 'xlsx', 'tsv', 'csv'),
    );

    // ユーザ権限情報
    private $authority_list     = array();

    // ユーザ情報
    private $user_authority     = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = 'ユーザー一覧';
        $cnf['page_id']                     = '[M0011]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'ユーザー一覧';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = 'ユーザー一覧';

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
        $paging_config = PagingConfig::getPagingConfig("UIM0011", C0010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

        // ユーザ権限情報
        $this->authority_list           = GenerateList::getUserAuthority(true, M0010::$db);
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
        $auth_data = AuthConfig::getAuthConfig('all');;

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = M0011::getSearchCustomer($code, M0011::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        }

        if ($code = Session::get('select_member_code')) {
            // 検索にてレコード選択された場合
            if ($result = M0010::getMember($code, M0010::$db)) {
                foreach ($result as $key => $val) {
                    $conditions['member_code']  = $val['member_code'];
                    $conditions['full_name']    = $val['full_name'];
                }
            } else {
                $error_msg = Config::get('m_MW0003');
            }
            Session::delete('select_member_code');
        }

        return $error_msg;
    }

    // 削除処理
    private function delete_record($conditions) {

        try {
            DB::start_transaction(M0010::$db);

            $error_msg = M0010::delete_record($conditions, M0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        return null;
    }

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $error_msg_sub  = null;
        $search_flag    = true;
        $search_mode    = 1;
        $conditions     = array_fill_keys(array(
            'member_code',
            'user_id',
            'full_name',
            'user_authority',
            'customer_code',
            'customer_name',
            'search_mode'
        ), '');

        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            //データ削除
            $error_msg = $this->delete_record($conditions);
        }

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('m0011_list');
        } elseif ((!empty(Input::param('search')) || !empty(Input::param('search_today'))) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            // // 入力値チェック
            // $validation = $this->validate_info();
            // $errors = $validation->error();
            // if (!empty($errors)) {
            //     foreach($validation->error() as $key => $e) {
            //         // チェック項目は配車番号のみのため固定
            //         $error_msg = str_replace('XXXXX','customer_code',Config::get('m_CW0006'));
            //     }
            // }

            $conditions['search_mode'] = $search_mode;

            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0011_list');
            Session::set('m0011_list', $conditions);

        } else {
            if ($cond = Session::get('m0011_list', array())) {

                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                $search_mode = $conditions['search_mode'];

                if (!empty(Input::param('select_record'))) {
                    // 検索項目の検索画面からコードが連携された場合の処理

                    foreach ($conditions as $key => $val) {
                        $conditions[$key] = Input::param($key, ''); // 検索項目
                    }

                    // 連携されたコードによる情報取得＆値セット
                    $error_msg      = $this->set_info($conditions);
                    $search_flag    = false;
                }

            } else {
                $search_flag = false;
            }

            //初期表示もエクスポートに備えて条件保存する
            Session::set('m0011_list', $conditions);

        }

        /**
         * ページング設定&検索実行
         */
        if ($search_flag) {
            $total = M0011::getSearch('count', $conditions, null, null, $search_mode, M0011::$db);

            // 検索上限チェック
            if (Config::get('m0011_limit') < $total) {
                $error_msg = str_replace('XXXXX',Config::get('m0011_limit'),Config::get('m_DW0015'));
                $error_msg_sub = "※入力してください";
                $total = 0;
            }
        } else {
            // 検索しない
            $total = 0;
        }

        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('m0011_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);

            //ページネーションのページ数をセッションに保存
            Session::set('m0011_page', $page);
        } else {
            //ページネーションのページ数をセッションに保存
            Session::set('m0011_page', Input::get('p'));
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = M0011::getSearch('search', $conditions, $offset, $limit, $search_mode, M0011::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        //明細部のレコード件数取得
        $list_count = 0;
        if (is_countable($list_data)){
            $list_count = count($list_data);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                 => $total,
                'data'                  => $conditions,
                'list_data'             => $list_data,
                'list_count'            => $list_count,
                'offset'                => $offset,
                'error_message'         => $error_msg,
                'error_message_sub'     => $error_msg_sub,

                'userinfo'              => AuthConfig::getAuthConfig('all'),
                'authority_list'        => $this->authority_list,
                'user_authority'        => $this->user_authority,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }

}
