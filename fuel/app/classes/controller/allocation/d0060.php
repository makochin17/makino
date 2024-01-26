<?php
/**
 * 配車入力売上補正照会画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Allocation\D0060;

class Controller_Allocation_D0060 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    private $export_limit = 10000;
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

    // 処理区分リスト
    private $processing_division_list   = array();
    // 課リスト
    private $division_list              = array();
    // 役職リスト
    private $position_list              = array();
    // 商品リスト
    private $product_list               = array();
    // 車種リスト
    private $carmodel_list              = array();
    // 売上ステータスリスト
    private $sales_status_list          = array();

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
            $this->format = 'xlsx';
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
        $cnf['header_title']                = '月極その他情報照会';
        $cnf['page_id']                     = '[D0060]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '月極その他情報照会';
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
            ''
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'common/style.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'allocation/d0060.js'
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;


        // 処理区分リスト取得
        $this->processing_division_list = GenerateList::getProcessingDivisionList();
        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, D0060::$db);
        // 商品リスト取得
        $this->product_list             = GenerateList::getProductList(false, D0060::$db);
        // 車種リスト
        $this->carmodel_list            = GenerateList::getCarModelList(true, D0060::$db);
        // 配車区分
        $this->delivery_category_list   = GenerateList::getDeliveryCategoryList(true, D0060::$db);
        // 税区分
        $this->tax_category_list        = GenerateList::getTaxCategoryList(false, D0060::$db);
        // 売上区分リスト取得
        $this->sales_category_list      = GenerateList::getSalesCategoryList(true, D0060::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true);

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
            if ($result = D0060::getSearchClient($code, D0060::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D0060::getSearchCarrier($code, D0060::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            if ($result = D0060::getSearchProduct($code, D0060::$db)) {
                $conditions['product_code'] = $result[0]['product_code'];
                $conditions['product_name'] = $result[0]['product_name'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D0060::getSearchCar($code, D0060::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D0060::getSearchMember($code, D0060::$db)) {
                $conditions['driver_name']    = $result[0]['driver_name'];
                $conditions['member_code']    = $result[0]['member_code'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        }

        return $error_msg;
    }

    // 入力チェック
	private function validate_info($conditions) {

		// 入力チェック
        foreach ($conditions['list'] as $key => $val) {
            // ２レコード目以降で処理区分が更新または削除の場合はスルー
            if ($key > 0 && $conditions['processing_division'] != 1) {
                continue;
            }
            // ２レコード目以降で日付、売上区分、得意先No、売上が未入力の場合はスルー
            if ($key > 0 && empty($val['sales_date']) && empty($val['client_code']) && empty($val['sales'])) {
                continue;
            }

            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');

            // 日付チェック
            $validation->add('list['.$key.'][sales_date]', '日付')
                ->add_rule('required');
            // 売上区分チェック
            if ($val['sales_category_code'] == '99') {
                // 売上区分（その他の場合）
                $validation->add('list['.$key.'][sales_category_code]', '売上区分')
                    ->add_rule('required')
                    ->add_rule('valid_strings', array('alpha', 'numeric'))
                    ->add_rule('trim_max_lengths', 10);
            } else {
                // 売上区分（その他以外の場合）
                $validation->add('list['.$key.'][sales_category_code]', '売上区分')
                    ->add_rule('required');
            }
            // 得意先Noチェック
            $validation->add('list['.$key.'][client_code]', '得意先No')
                ->add_rule('required');
            if (!empty($val['operation_count'])) {
                // 稼働台数チェック
                $validation->add('list['.$key.'][operation_count]', '稼働台数')
                    ->add_rule('is_numeric');
            }
            // 売上チェック
            $validation->add('list['.$key.'][sales]', '売上')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 配送区分チェック
            $validation->add('list['.$key.'][delivery_category]', '配送区分')
                ->add_rule('required');

            if (!empty($val['highway_fee'])) {
                // 高速料金チェック
                $validation->add('list['.$key.'][highway_fee]', '高速料金')
                    ->add_rule('trim_max_lengths', 8)
                    ->add_rule('is_numeric');
            }

            if ($val['sales_category_code'] == '03' || $val['sales_category_code'] == '99') {
                // 売上区分（保管料、その他）
                // 庸車Noチェック
                $validation->add('list['.$key.'][carrier_code]', '庸車No')
                    ->add_rule('trim_max_lengths', 5)
                    ->add_rule('is_numeric');
                // 運転手チェック
                $validation->add('list['.$key.'][driver_name]', '運転手')
                    ->add_rule('trim_max_lengths', 6);
                // 庸車費チェック
                $validation->add('list['.$key.'][carrier_cost]', '庸車費')
                    ->add_rule('trim_max_lengths', 8)
                    ->add_rule('is_numeric');
            } else {
                // 売上区分（定期便、作業員）
                // 車種チェック
                $validation->add('list['.$key.'][car_model_code]', '車種')
                    ->add_rule('required');
                // 庸車Noチェック
                $validation->add('list['.$key.'][carrier_code]', '庸車No')
                    ->add_rule('required')
                    ->add_rule('trim_max_lengths', 5)
                    ->add_rule('is_numeric');
                // 運転手チェック
                $validation->add('list['.$key.'][driver_name]', '運転手')
                    ->add_rule('required')
                    ->add_rule('trim_max_lengths', 6);
                // 庸車費チェック
                $validation->add('list['.$key.'][carrier_cost]', '庸車費')
                    ->add_rule('required')
                    ->add_rule('trim_max_lengths', 8)
                    ->add_rule('is_numeric');

            }
        }
		$validation->run();
		return $validation;
	}

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $total              = 0;
        $offset             = 0;
        $cnt                = 0;
        $error_msg          = null;
        $date_error_msg     = null;
        $init_flag          = false;
        $popup_flag         = false;
        $list_data          = array();
        $conditions         = D0060::getForms();
        $select_record      = Input::param('select_record', '');
        $excel_dl           = Input::param('excel_dl', '');

        if (Input::method() == 'POST') {
            if (!empty(Input::param('input_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('d0060_list');
                //初期表示もエクスポートに備えて条件保存する
                Session::set('d0060_list', $conditions);
            } elseif (!empty(Input::param('search'))) {
                // 確定ボタンが押下された場合の処理
                $conditions = D0060::setForms($conditions, Input::param());

                // 入力値チェック
                if (!empty($conditions['from_sales_date']) && !empty($conditions['to_sales_date'])) {
                    if ($conditions['from_sales_date'] > $conditions['to_sales_date']) {
                        $date_error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
                    }
                }
                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('d0060_list');
                Session::set('d0060_list', $conditions);
            } elseif (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = D0060::setForms($conditions, Input::param());
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
                Session::delete('d0060_list');
                Session::set('d0060_list', $conditions);
            } elseif (!empty($excel_dl)) {
                // エクセル出力ボタンが押下された場合の処理
                $conditions = D0060::setForms($conditions, Input::param());
                // 入力値チェック
                if (!empty($conditions['from_sales_date']) && !empty($conditions['to_sales_date'])) {
                    if ($conditions['from_sales_date'] > $conditions['to_sales_date']) {
                        $date_error_msg = str_replace('XXXXX','日付',Config::get('m_CW0007'));
                    }
                }
                // エクセル出力
                if (empty($date_error_msg)) {
                    $this->export($conditions);
                }
            }
        } else {
            if ($cond = Session::get('d0060_list', array())) {
                $conditions = $cond;
                Session::delete('d0060_list');
            } else {
                $init_flag = true;
            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('d0060_list', $conditions);

        }

        if (empty($popup_flag)) {
            /**
             * ページング設定&検索実行
             */
            if (!$init_flag) {
                $total                      = D0060::getSearch('count', $conditions, null, null, D0060::$db);
            } else {
                // 初期表示時は検索しない
                $total = 0;
            }
            $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
            $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
            $limit                          = $pagination->per_page;
            $offset                         = $pagination->offset;
            if ($total > 0) {
                $list_data                  = D0060::getSearch('search', $conditions, $offset, $limit, D0060::$db);
            } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg) && empty($date_error_msg)) {
                $error_msg = Config::get('m_CI0003');
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                     => $total,
                'list_data'                 => $list_data,
                'offset'                    => $offset,

                'data'                      => $conditions,

                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'product_list'              => $this->product_list,
                'carmodel_list'             => $this->carmodel_list,
                'delivery_category_list'    => $this->delivery_category_list,
                'tax_category_list'         => $this->tax_category_list,
                'sales_category_list'       => $this->sales_category_list,
                'sales_status_list'         => $this->sales_status_list,

                'error_message'             => $error_msg,
                'date_error_message'        => $date_error_msg,
            )
        );
        $this->template->content->set_safe('pager', (empty($popup_flag)) ? $pagination->render():'');

    }

    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = D0060::getNameById($type, $code, D0060::$db);

        return $this->response($data);
    }

    private function export($conditions) {

        $excel_data                 = array();
        $file                       = date('Ymd').'_売上補正一覧表';
        // 課リスト取得
        $division_list            = GenerateList::getDivisionList(true, D0060::$db);
        // 車種リスト
        $carmodel_list            = GenerateList::getCarModelList(true, D0060::$db);
        // 配車区分
        $delivery_category_list   = GenerateList::getDeliveryCategoryList(true, D0060::$db);
        // 売上区分リスト取得
        $sales_category_list      = GenerateList::getSalesCategoryList(true, D0060::$db);

        $total = D0060::getSearch('count', $conditions, null, null, D0060::$db);
        \DB::select(\DB::expr('NOW()'))->execute(D0060::$db);
        if (0 < $total && $total <= $this->export_limit) {
            $res    = D0060::getSearch('export', $conditions, null, null, D0060::$db);
            \DB::select(\DB::expr('NOW()'))->execute(D0060::$db);
            if (!empty($res)) {
                foreach($res as $key => $val){
                    $excel_data[]  = array(
                        '売上補正番号'        => sprintf('%010d', $val['sales_correction_number']),
                        '課コード'           => sprintf('%03d', $val['division_code']),
                        '課名'               => isset($division_list[$val['division_code']]) ? $division_list[$val['division_code']] : '',
                        '売上ステータス'      => ($val['sales_status'] == '2') ? '○':'×',
                        '日付'               => (!empty($val['sales_date'])) ? str_replace('-', '/', $val['sales_date']) : '',
                        '売上区分'           => isset($sales_category_list[$val['sales_category_code']]) ? $sales_category_list[$val['sales_category_code']] : '',
                        '得意先コード'       => (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']) : '',
                        '得意先名'           => (!empty($val['client_name'])) ? $val['client_name'] : '',
                        '庸車先コード'       => (!empty($val['carrier_code'])) ? sprintf('%05d', $val['carrier_code']) : '',
                        '庸車先名'           => (!empty($val['carrier_name'])) ? $val['carrier_name'] : '',
                        '車種コード'         => (!empty($val['car_model_code'])) ? sprintf('%03d', $val['car_model_code']) : '',
                        '車種名'             => isset($carmodel_list[$val['car_model_code']]) ? $carmodel_list[$val['car_model_code']] : '',
                        '車両コード'         => (!empty($val['car_code'])) ? sprintf('%04d', $val['car_code']) : '',
                        '社員コード'         => (!empty($val['member_code'])) ? sprintf('%05d', $val['member_code']) : '',
                        '運転手'             => (!empty($val['driver_name'])) ? $val['driver_name'] : '',
                        '稼働台数'           => (!empty($val['operation_count'])) ? $val['operation_count'] : '',
                        '配送区分'           => isset($delivery_category_list[$val['delivery_category']]) ? $delivery_category_list[$val['delivery_category']] : '',
                        '請求売上'           => (!empty($val['sales'])) ? number_format($val['sales']) : '',
                        '庸車支払'           => (!empty($val['carrier_cost'])) ? number_format($val['carrier_cost']) : '',
                        '高速料金'           => (!empty($val['highway_fee'])) ? number_format($val['highway_fee']) : '',
                        '高速請求有無'        => ($val['highway_fee_claim'] == '2') ? '○':'×',
                        '時間外'             => (!empty($val['overtime_fee'])) ? number_format($val['overtime_fee']) : '',
                        '備考'               => (!empty($val['remarks'])) ? $val['remarks'] : ''
                    );
                }
            }

            /**
             * Excel ファイルへの書き出し
             */
            $title   = $file;
            $data    = array();
            // データを追加
            if (!empty($excel_data)) {
                $data[]  = array_keys($excel_data[0]); // ヘッダータイトル
                foreach ($excel_data as $key => $val) {
                    $data[] = $val;
                }
            }
            // Excelデータ作成
            $content = Data::create_salescorrection('xlsx', $file, '売上補正データ', $data);
            // $content = Data::create_utf8($this->format, $title, $data);
            // $this->response->set_header('Content-Type', 'application/octet-stream');
            // $this->response->set_header('Content-Disposition', 'attachment; filename="'.$file.'.xlsx"');
            return $this->response(true);

        }

    }

}
