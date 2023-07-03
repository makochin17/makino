<?php
/**
 * 庸車先マスタメンテナンス画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030\M0030;

class Controller_Mainte_M0030 extends Controller_Hybrid {

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

    // 会社区分リスト
    private $company_section_list = array();
    
    // 締日リスト
    private $closing_date_list = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '庸車先マスタメンテナンス';
        $cnf['page_id']                     = '[M0030]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '庸車先マスタメンテナンス';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '庸車先マスタメンテナンス';

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
        $paging_config = PagingConfig::getPagingConfig("UIS0030", M0030::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 会社区分リスト取得
        $this->company_section_list = GenerateList::getCompanySectionList(true);
        
        // 締日リスト
        $this->closing_date_list        = GenerateList::getClosingDateList(true);
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
		// 庸車先コードチェック
		$validation->add('carrier_code', '庸車先コード')
			->add_rule('is_numeric');
		$validation->run();
		return $validation;
	}
    
    // レコード削除処理
    private function delete_record() {
        
        $carrier_code = Input::post('carrier_code', '');
        
        try {
            DB::start_transaction(M0030::$db);
            
            // レコード存在チェック
            if (!$result = M0030::getCarrier($carrier_code, M0030::$db)) {
                return Config::get('m_MW0003');
            }

            // レコード削除（論理）
            $error_msg = M0030::delCarrier($carrier_code, M0030::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(M0030::$db);
                return $error_msg;
            }
            
            DB::commit_transaction(M0030::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0030::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        
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
        	'carrier_code',
        	'company_name',
        	'sales_office_name',
        	'department_name',
        	'closing_date',
            'company_section',
            'official_name',
            'official_name_kana',
        ), '');

        if (!empty(Input::param('excel'))) {
            // エクセル出力ボタンが押下された場合の処理
            
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            // 入力値チェック
			$validation = $this->validate_info();
			$errors = $validation->error();
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e) {
                    // チェック項目は庸車先コードのみのため固定
                    $error_msg = str_replace('XXXXX','庸車先コード',Config::get('m_CW0006'));
				}
			}
            
            // エクセル出力
            if (empty($error_msg)) {
                M0030::createTsv($conditions, M0030::$db);
            }
            
        }
        if (Input::post('processing_division', '') == '3' && Security::check_token()) {
            // 削除ボタンが押下された場合の処理
            
            //庸車先データ削除
            $error_msg = $this->delete_record();
            
        }
        if (!empty(Input::param('search')) && Security::check_token()) {
            // 検索ボタンが押下された場合の処理

            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            // 入力値チェック
			$validation = $this->validate_info();
			$errors = $validation->error();
			if (!empty($errors)) {
				foreach($validation->error() as $key => $e) {
                    // チェック項目は庸車先コードのみのため固定
                    $error_msg = str_replace('XXXXX','庸車先コード',Config::get('m_CW0006'));
				}
			}
            
            /**
             * セッションに検索条件を設定
             */
            Session::delete('m0030_list');
            Session::set('m0030_list', $conditions);
            
        } else {
            if ($cond = Session::get('m0030_list', array())) {
                
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }

            } else {
                $search_flag = false;
            }
            
            //初期表示もエクスポートに備えて条件保存する
            Session::set('m0030_list', $conditions);

        }
        
        /**
         * ページング設定&検索実行
         */
        if (!$init_flag) {
            $total                      = M0030::getSearchCount($conditions, M0030::$db);
        } else {
            // 初期表示時は検索しない
            $total = 0;
        }
        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('m0030_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);
        }
        
        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        $list_data                      = array();
        
        //ページネーションのページ数をセッションに保存
        Session::set('m0030_page', Input::get('p'));
        
        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = M0030::getSearch($conditions, $offset, $limit, M0030::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }
        
        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                 => $total,
                'data'                  => $conditions,
                'company_section_list'  => $this->company_section_list,
                'closing_date_list'     => $this->closing_date_list,
                'list_data'             => $list_data,
                'offset'                => $offset,
                'error_message'         => $error_msg,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());
    }

}
