<?php
/**
 * 入出庫照会画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Stock\D1150;

class Controller_Stock_D1150 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    private $format_array = array(
                               'xls'    => 'Excel5',
                               'xlsx'   => 'Excel2007'
                             );

    // ページネーション
    private $pagenation_config = array(
        'uri_segment' 	=> 'p',
    	'num_links' 	=> 2,
    	'per_page' 		=> 10,
    	'name' 			=> 'default',
    	'show_first' 	=> true,
    	'show_last' 	=> true,
    );

    // 課リスト
    private $division_list              = array();
    // 売上ステータスリスト
    private $sales_status_list          = array();
    // 入出庫区分リスト
    private $stock_change_list          = array();
    // 単位リスト
    private $unit_list                  = array();
    // 登録者リスト
    private $create_user_list           = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        if (Request::main()->action == 'export') {
            $this->format = 'xlsx';
            return true;
        }
        $is_ajax = Input::is_ajax();
        if ($is_ajax) {
            $this->format = 'json';
            return true;
        }
        return false;
    }

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '入出庫情報照会';
        $cnf['page_id']                     = '[D1150]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '入出庫情報照会';
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
            'common/jquery.jqplot.css',
            'common/jqModal.css'
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'common/style.css',
            'common/modal.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'allocation/d0040.js'
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UID1150", D1150::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, D1150::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 入出庫区分
        $this->stock_change_list        = GenerateList::getStockChangeCategoryList(true, D1150::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(true, D1150::$db);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(true, D1150::$db);

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
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = D1150::getSearchClient($code, D1150::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        }

        return $error_msg;
    }

    // 入力チェック
	private function input_check() {
        $error_msg = "";
        $validation = $this->validate_info();
        $errors     = $validation->error();
        
        // 入力値チェックのエラー判定
        if (!empty($errors)) {
            foreach($validation->error() as $key => $e) {
                if (preg_match('/stock_change_number/', $key)) {
                    $error_item = 'stock_change_number';
                } elseif (preg_match('/stock_number/', $key)) {
                    $error_item = 'stock_number';
                } elseif (preg_match('/client_code/', $key)) {
                    $error_item = 'client_code';
                } elseif (preg_match('/product_name/', $key)) {
                    $error_item = 'product_name';
                } elseif (preg_match('/from_destination_date/', $key)) {
                    $error_item = 'from_destination_date';
                } elseif (preg_match('/to_destination_date/', $key)) {
                    $error_item = 'to_destination_date';
                } elseif (preg_match('/destination/', $key)) {
                    $error_item = 'destination';
                }

                $item = D1150::getValidateItems();
                $error_column = $item[$error_item]['name'];
                $column_length = $item[$error_item]['max_lengths'];

                if ($validation->error()[$key]->rule == 'required') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    $error_msg = str_replace('xxxxx',$column_length,$error_msg);
                } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                }
                break;
            }
        }
        
        return $error_msg;
    }
    
    // バリデーションチェック
	private function validate_info() {

		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        $item = D1150::getValidateItems();
		// 入出庫番号チェック
		$validation->add('stock_change_number', $item['stock_change_number']['name'])
            ->add_rule('trim_max_lengths', $item['stock_change_number']['max_lengths'])
            ->add_rule('is_numeric');
        // 在庫番号チェック
		$validation->add('stock_number', $item['stock_number']['name'])
            ->add_rule('trim_max_lengths', $item['stock_number']['max_lengths'])
            ->add_rule('is_numeric');
        // 得意先チェック
		$validation->add('client_code', $item['client_code']['name'])
            ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 商品名チェック
		$validation->add('product_name', $item['product_name']['name'])
            ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
        // 日付Fromチェック
		$validation->add('from_destination_date', $item['from_destination_date']['name'])
            ->add_rule('valid_date_format');
        // 日付Toチェック
		$validation->add('to_destination_date', $item['to_destination_date']['name'])
            ->add_rule('valid_date_format');
        // 運行先チェック
		$validation->add('destination', $item['destination']['name'])
            ->add_rule('trim_max_lengths', $item['destination']['max_lengths']);
		$validation->run();
		return $validation;
	}

    public function action_index() {

        Config::load('message');
        Config::load('searchlimit');

        /**
         * 検索項目の取得＆初期設定
         */
        $total              = 0;
        $offset             = 0;
        $error_msg          = null;
        $error_msg_sub      = null;
        $date_error_msg1    = null;
        $date_error_msg2    = null;
        $init_flag          = false;
        $popup_flag         = false;
        $list_data          = array();
        $conditions         = D1150::getForms();
        $select_record      = Input::param('select_record', '');
        $excel_dl           = Input::param('excel_dl', '');

        if (Input::method() == 'POST') {
            if (!empty(Input::param('input_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('d1150_list');
            } elseif (!empty(Input::param('search'))) {
                // 確定ボタンが押下された場合の処理
                $conditions = D1150::setForms($conditions, Input::param());

                // 入力値チェック
                $error_msg = $this->input_check();
                // 日付相関チェック（日付）
                if (!empty($conditions['from_destination_date']) && !empty($conditions['to_destination_date'])) {
                    if ($conditions['from_destination_date'] > $conditions['to_destination_date']) {
                        $error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
                    }
                }

                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('d1150_list');
                Session::set('d1150_list', $conditions);
            } elseif (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = D1150::setForms($conditions, Input::param());
                // 連携されたコードによる情報取得＆値セット
                $error_msg  = $this->set_info($conditions);
                $popup_flag = true;
                
                Session::delete('select_client_code');
                
                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('d1150_list');
                Session::set('d1150_list', $conditions);
            } elseif (!empty($excel_dl)) {
                // エクセル出力ボタンが押下された場合の処理
                $conditions = D1150::setForms($conditions, Input::param());
                // 入力値チェック
                $error_msg = $this->input_check();
                // 日付相関チェック（日付）
                if (!empty($conditions['from_destination_date']) && !empty($conditions['to_destination_date'])) {
                    if ($conditions['from_destination_date'] > $conditions['to_destination_date']) {
                        $error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
                    }
                }
                // エクセル出力
                if (empty($error_msg)) {
                    $error_msg = $this->export($conditions);
                }
            }
        } else {
            if ($cond = Session::get('d1150_list', array())) {
                $conditions = $cond;
                Session::delete('d1150_list');
            } else {
                $init_flag = true;
            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('d1150_list', $conditions);

        }

        if (empty($popup_flag)) {
            /**
             * ページング設定&検索実行
             */
            if (!$init_flag) {
                $total = D1150::getSearch('count', $conditions, null, null, D1150::$db);
                
                // 検索上限チェック
                if (Config::get('d1150_limit') < $total) {
                    $error_msg = str_replace('XXXXX',Config::get('d1150_limit'),Config::get('m_DW0015'));
                    $error_msg_sub = "※入力してください";
                    $total = 0;
                }
            } else {
                // 初期表示時は検索しない
                $total = 0;
            }
            $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
            $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
            $limit                          = $pagination->per_page;
            $offset                         = $pagination->offset;
            if ($total > 0) {
                $list_data                  = D1150::getSearch('search', $conditions, $offset, $limit, D1150::$db);
            } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg) && empty($date_error_msg1) && empty($date_error_msg2)) {
                $error_msg = Config::get('m_CI0003');
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'carrying_url'              => \Uri::create(AccessControl::getActiveController().'/carrying'),

                'total'                     => $total,
                'list_data'                 => $list_data,
                'offset'                    => $offset,

                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'sales_status_list'         => $this->sales_status_list,
                'stock_change_list'         => $this->stock_change_list,
                'unit_list'                 => $this->unit_list,
                'create_user_list'          => $this->create_user_list,

                'error_message'             => $error_msg,
                'error_message_sub'         => $error_msg_sub,
                'date_error_message1'       => $date_error_msg1,
                'date_error_message2'       => $date_error_msg2,
            )
        );
        $this->template->content->set_safe('pager', (empty($popup_flag)) ? $pagination->render():'');

    }

    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = D1150::getNameById($type, $code, D1150::$db);

        return $this->response($data);
    }

    private function export() {

        $excel_data                 = array();
        $file                       = date('Ymd').'_入出庫情報一覧表';
        $headers                    = D1150::getHeader();
        $conditions                 = D1150::getForms();
        $conditions                 = D1150::setForms($conditions, Input::param());

        // 課リスト取得
        $division_list            = GenerateList::getDivisionList(true, D1150::$db);
        // 売上ステータスリスト
        $sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 入出庫区分
        $stock_change_list        = GenerateList::getStockChangeCategoryList(true, D1150::$db);
        // 単位リスト取得
        $unit_list                = GenerateList::getUnitList(true, D1150::$db);
        // 車種リスト
        $carmodel_list            = GenerateList::getCarModelList(true, D1150::$db);
        
        if (!empty($conditions)) {
            $excel_data[] = $headers;
            $total = D1150::getSearch('count', $conditions, null, null, D1150::$db);
            
            //0件チェック
            if (0 >= $total) {
                return Config::get('m_CI0004');
            }
            
            //検索上限チェック
            if (Config::get('d1151_limit') < $total) {
                return str_replace('XXXXX',Config::get('d1151_limit'),Config::get('m_DW0016'));
            }

            $res    = D1150::getSearch('export', $conditions, null, null, D1150::$db);
            //\DB::select(\DB::expr('NOW()'))->execute(D1150::$db);
            if (!empty($res)) {
                foreach($res as $key => $val){
                    $excel_data[]      = array(
                        'stock_change_number'	 => sprintf('%010d', $val['stock_change_number']),
                        'stock_number'      	 => sprintf('%010d', $val['stock_number']),
                        'division_code'     	 => sprintf('%03d', $val['division_code']),
                        'division_name'     	 => $division_list[$val['division_code']],
                        'client_code'       	 => sprintf('%05d', $val['client_code']),
                        'client_name'       	 => $val['client_name'],
                        'product_name'      	 => $val['product_name'],
                        'maker_name'        	 => $val['maker_name'],
                        'stock_change_code' 	 => sprintf('%02d', $val['stock_change_code']),
                        'stock_change_name' 	 => $stock_change_list[$val['stock_change_code']],
                        'destination_date'  	 => str_replace('-', '/', $val['destination_date']),
                        'destination'       	 => $val['destination'],
                        'volume'            	 => floatval(number_format($val['volume'], 6)),
                        'unit_code'          	 => sprintf('%02d', $val['unit_code']),
                        'unit_name'         	 => $unit_list[$val['unit_code']],
                        'fee'               	 => number_format($val['fee']),
                        'sales_status'      	 => $sales_status_list[$val['sales_status']],
                        'remarks'           	 => $val['remarks']
                    );
                }
            }
            /**
             * Excel ファイルへの書き出し
             */
            $title   = $file;
            $data    = $excel_data;

            // Excelデータ作成
            $content = Data::create_stock_change('xlsx', $title, '入出庫情報一覧表', $data);
            // $content = Data::create_utf8($this->format, $title, $data);
            $this->response->set_header('Content-Disposition', 'attachment; filename="'.$file.'.xlsx"');
            return $this->response($content);

        }

        Response::redirect(AccessControl::getActiveController());

    }

}
