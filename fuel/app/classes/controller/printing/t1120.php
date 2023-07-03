<?php
/**
 * 配車照会（共配便）画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Printing\T1120;

class Controller_Printing_T1120 extends Controller_Hybrid {

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
    // 車種リスト
    private $carmodel_list              = array();
    // 得意先リスト
    private $client_list                = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '納品書印刷';
        $cnf['page_id']                     = '[T1120]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '納品書印刷';
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
            'printing/t1120.js'
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIT1120", T1120::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, T1120::$db);
        // 車種リスト
        $this->carmodel_list            = GenerateList::getCarModelList(true, T1120::$db);
        // 得意先リスト取得
        $this->client_list              = T1120::getDeliverySlipList(false, T1120::$db);

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

        if ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = T1120::getSearchCarrier($code, T1120::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = T1120::getSearchCar($code, T1120::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                // $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        }

        return $error_msg;
    }

    // 入力チェック
	private function input_check($output_dl) {
        $error_msg = "";
        $validation = $this->validate_info($output_dl);
        $errors     = $validation->error();
        
        // 入力値チェックのエラー判定
        if (!empty($errors)) {
            foreach($validation->error() as $key => $e) {
                if (preg_match('/dispatch_number/', $key)) {
                    $error_item = 'dispatch_number';
                } elseif (preg_match('/delivery_place/', $key)) {
                    $error_item = 'delivery_place';
                } elseif (preg_match('/carrier_code/', $key)) {
                    $error_item = 'carrier_code';
                } elseif (preg_match('/car_code/', $key)) {
                    $error_item = 'car_code';
                } elseif (preg_match('/from_delivery_date/', $key)) {
                    $error_item = 'from_delivery_date';
                } elseif (preg_match('/to_delivery_date/', $key)) {
                    $error_item = 'to_delivery_date';
                }

                $item = T1120::getValidateItems();
                $error_column = $item[$error_item]['name'];
                $column_length = $item[$error_item]['max_lengths'];

                if ($validation->error()[$key]->rule == 'required' && $error_item == 'car_code') {
                    $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0031'));
                } elseif ($validation->error()[$key]->rule == 'required') {
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
	private function validate_info($output_dl) {

		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        $item = T1120::getValidateItems();
		// 配車番号チェック
		$validation->add('dispatch_number', $item['dispatch_number']['name'])
            ->add_rule('trim_max_lengths', $item['dispatch_number']['max_lengths'])
            ->add_rule('is_numeric');
        // 納品先チェック
		$validation->add('delivery_place', $item['delivery_place']['name'])
            ->add_rule('trim_max_lengths', $item['delivery_place']['max_lengths']);
        // 庸車先チェック
		$validation->add('carrier_code', $item['carrier_code']['name'])
            ->add_rule('trim_max_lengths', $item['carrier_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 車両番号チェック
        $validation->add('car_code', $item['car_code']['name'])
            ->add_rule('trim_max_lengths', $item['car_code']['max_lengths'])
            ->add_rule('is_numeric');
        // 納品日Fromチェック
		$validation->add('from_delivery_date', $item['from_delivery_date']['name'])
            ->add_rule('valid_date_format');
        // 納品日Toチェック
		$validation->add('to_delivery_date', $item['to_delivery_date']['name'])
            ->add_rule('valid_date_format');
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
        $conditions         = T1120::getForms();
        $select_record      = Input::param('select_record', '');
        $excel_dl           = Input::param('excel_dl', '');
        $output_dl          = Input::param('output_dl', '');

        if (Input::method() == 'POST') {
            if (!empty(Input::param('input_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('t1120_list');
            } elseif (!empty(Input::param('delivery_slip_code')) && !empty(Input::param('select_dispatch_info'))) {
                // 選択ボタンが押下された場合の処理
                T1120::createExcel(Input::param('delivery_slip_code'), Input::param('select_dispatch_info'));

            } elseif (!empty(Input::param('search'))) {
                // 確定ボタンが押下された場合の処理
                $conditions = T1120::setForms($conditions, Input::param());

                // 入力値チェック
                $error_msg = $this->input_check($output_dl);
                // 日付相関チェック（納品日）
                if (!empty($conditions['from_delivery_date']) && !empty($conditions['to_delivery_date'])) {
                    if ($conditions['from_delivery_date'] > $conditions['to_delivery_date']) {
                        $error_msg = str_replace('XXXXX','納品日',Config::get('m_CW0007'));
                    }
                }

                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('t1120_list');
                Session::set('t1120_list', $conditions);
            } elseif (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = T1120::setForms($conditions, Input::param());
                // 連携されたコードによる情報取得＆値セット
                $error_msg  = $this->set_info($conditions);
                $popup_flag = true;
                
                Session::delete('select_client_code');
                Session::delete('select_product_code');
                Session::delete('select_carrier_code');
                Session::delete('select_car_code');
                Session::delete('select_member_code');
                
                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('t1120_list');
                Session::set('t1120_list', $conditions);
            } elseif (!empty($excel_dl)) {
                // エクセル出力ボタンが押下された場合の処理
                $conditions = T1120::setForms($conditions, Input::param());
                // 入力値チェック
                $error_msg = $this->input_check($output_dl);
                // 日付相関チェック（納品日）
                if (!empty($conditions['from_delivery_date']) && !empty($conditions['to_delivery_date'])) {
                    if ($conditions['from_delivery_date'] > $conditions['to_delivery_date']) {
                        $error_msg = str_replace('XXXXX','納品日',Config::get('m_CW0007'));
                    }
                }
                // エクセル出力
                if (empty($error_msg)) {
                    $error_msg = $this->export($conditions);
                }
            } elseif (!empty($output_dl)) {
                // 配車表出力ボタンが押下された場合の処理
                $conditions = T1120::setForms($conditions, Input::param());

                // 入力値チェック
                $error_msg = $this->input_check($output_dl);
                // 日付相関チェック（納品日）
                if (!empty($conditions['from_delivery_date']) && !empty($conditions['to_delivery_date'])) {
                    if ($conditions['from_delivery_date'] > $conditions['to_delivery_date']) {
                        $error_msg = str_replace('XXXXX','納品日',Config::get('m_CW0007'));
                    }
                }

                // 配車表出力
                if (empty($error_msg)) {
                    $error_msg = D1041::createExcel($conditions);
                }
            }
        } else {
            if ($cond = Session::get('t1120_list', array())) {
                $conditions = $cond;
                Session::delete('t1120_list');
            } else {
                $init_flag = true;
            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('t1120_list', $conditions);

        }

        if (empty($popup_flag)) {
            /**
             * ページング設定&検索実行
             */
            if (!$init_flag) {
                $list_data = T1120::getSearch('count', $conditions, null, null, T1120::$db);
                $total = 0;
                if (is_countable($list_data)){
                    $total = count($list_data);
                }
                
                // 検索上限チェック
                if (Config::get('t1120_limit') < $total) {
                    $error_msg = str_replace('XXXXX',Config::get('t1120_limit'),Config::get('m_DW0015'));
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
                $list_data                  = T1120::getSearch('search', $conditions, $offset, $limit, T1120::$db);
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
                'carmodel_list'             => $this->carmodel_list,
                'client_list'               => $this->client_list,

                'error_message'             => $error_msg,
                'error_message_sub'         => $error_msg_sub,
                'date_error_message1'       => $date_error_msg1,
                'date_error_message2'       => $date_error_msg2,
            )
        );
        $this->template->content->set_safe('pager', (empty($popup_flag)) ? $pagination->render():'');

    }
}
