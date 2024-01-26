<?php
/**
 * 配車入力配車照会画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Allocation\D0040;
use \Model\Allocation\D0041;

class Controller_Allocation_D0040 extends Controller_Hybrid {

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
        $cnf['header_title']                = '配車照会（チャーター便）';
        $cnf['page_id']                     = '[D0040]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '配車照会（チャーター便）';
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


        // 処理区分リスト取得
        $this->processing_division_list = GenerateList::getProcessingDivisionList();
        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(true, D0040::$db);
        // 商品リスト取得
        $this->product_list             = GenerateList::getProductList(true, D0040::$db);
        // 車種リスト
        $this->carmodel_list            = GenerateList::getCarModelList(true, D0040::$db);
        // 配車区分
        $this->delivery_category_list   = GenerateList::getDeliveryCategoryList(true, D0040::$db);
        // 税区分
        $this->tax_category_list        = GenerateList::getTaxCategoryList(true, D0040::$db);
        // 売上区分リスト
        $this->sales_category_list      = GenerateList::getSalesCategoryList(true, D0040::$db);
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
            if ($result = D0040::getSearchClient($code, D0040::$db)) {
                $conditions['client_code'] = $result[0]['client_code'];
                //$conditions['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D0040::getSearchCarrier($code, D0040::$db)) {
                $conditions['carrier_code'] = $result[0]['carrier_code'];
                //$conditions['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            if ($result = D0040::getSearchProduct($code, D0040::$db)) {
                $conditions['product_code'] = $result[0]['product_code'];
                $conditions['product_name'] = $result[0]['product_name'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D0040::getSearchCar($code, D0040::$db)) {
                $conditions['car_code']   = $result[0]['car_code'];
                // $conditions['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D0040::getSearchMember($code, D0040::$db)) {
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

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg = null;

        // 傭車先コード取得
        if (empty($conditions['carrier_code'])) {
            $conditions['carrier_code'] = D0040::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0040::$db);
        }

        $error_msg = D0040::create_record($conditions, D0040::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        Config::load('message');
        $error_msg = null;

        // 傭車先コード取得
        if (empty($conditions['carrier_code'])) {
            $conditions['carrier_code'] = D0040::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0040::$db);
        }

        $error_msg = D0040::update_record($conditions, D0040::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
    }

    // 削除処理
    private function delete_record($conditions) {

        Config::load('message');
        $error_msg = null;

        $error_msg = D0040::delete_record($conditions, D0040::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
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
        $date_error_msg1    = null;
        $date_error_msg2    = null;
        $division_error_msg = null;
        $init_flag          = false;
        $popup_flag         = false;
        $list_data          = array();
        $conditions         = D0040::getForms();
        $select_record      = Input::param('select_record', '');
        $excel_dl           = Input::param('excel_dl', '');
        $output_dl          = Input::param('output_dl', '');

        if (Input::method() == 'POST') {
            if (!empty(Input::param('input_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('d0040_list');
            } elseif (!empty(Input::param('search'))) {
                // 確定ボタンが押下された場合の処理
                $conditions = D0040::setForms($conditions, Input::param());

                // 入力値チェック
                // 積日
                if (!empty($conditions['from_stack_date']) && !empty($conditions['to_stack_date'])) {
                    if ($conditions['from_stack_date'] > $conditions['to_stack_date']) {
                        $date_error_msg1 = str_replace('XXXXX','積日',Config::get('m_CW0007'));
                    }
                }
                // 降日
                if (!empty($conditions['from_drop_date']) && !empty($conditions['to_drop_date'])) {
                    if ($conditions['from_drop_date'] > $conditions['to_drop_date']) {
                        $date_error_msg2 = str_replace('XXXXX','降日',Config::get('m_CW0007'));
                    }
                }

                // // 入力値チェック
                // $validation = $this->validate_info($conditions);
                // $errors     = $validation->error();
                // // 入力値チェックのエラー判定
                // if (!empty($errors)) {
                //     foreach($validation->error() as $key => $e) {
                //         if (preg_match('/sales_date/', $key)) {
                //             $error_column = '日付';
                //         } elseif (preg_match('/sales_category_code/', $key)) {
                //             $error_column = '売上区分';
                //         } elseif (preg_match('/client_code/', $key)) {
                //             $error_column = '得意先No';
                //         } elseif (preg_match('/operation_count/', $key)) {
                //             $error_column = '稼働台数';
                //         } elseif (preg_match('/sales/', $key)) {
                //             $error_column = '売上';
                //         } elseif (preg_match('/delivery_category/', $key)) {
                //             $error_column = '配送区分';
                //         } elseif (preg_match('/highway_fee_claim/', $key)) {
                //             $error_column = '高速料金';
                //         } elseif (preg_match('/car_model_code/', $key)) {
                //             $error_column = '車種';
                //         } elseif (preg_match('/carrier_code/', $key)) {
                //             $error_column = '傭車先No';
                //         } elseif (preg_match('/driver_name/', $key)) {
                //             $error_column = '運転手';
                //         } elseif (preg_match('/carrier_cost/', $key)) {
                //             $error_column = '庸車費';
                //         }
                //         if ($validation->error()[$key]->rule == 'required') {
                //             $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                //         } elseif ($validation->error()[$key]->rule == 'valid_strings' ||
                //                                                 $validation->error()[$key]->rule == 'is_numeric' ||
                //                                                 $validation->error()[$key]->rule == 'trim_max_lengths') {
                //             $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                //         } else {
                //             // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                //         }
                //         break;
                //     }
                // }

                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('d0040_list');
                Session::set('d0040_list', $conditions);
            } elseif (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = D0040::setForms($conditions, Input::param());
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
                Session::delete('d0040_list');
                Session::set('d0040_list', $conditions);
            } elseif (!empty($excel_dl)) {
                // エクセル出力ボタンが押下された場合の処理
                $conditions = D0040::setForms($conditions, Input::param());
                // 入力値チェック
                // 積日
                if (!empty($conditions['from_stack_date']) && !empty($conditions['to_stack_date'])) {
                    if ($conditions['from_stack_date'] > $conditions['to_stack_date']) {
                        $date_error_msg1 = str_replace('XXXXX','積日',Config::get('m_CW0007'));
                    }
                }
                // 降日
                if (!empty($conditions['from_drop_date']) && !empty($conditions['to_drop_date'])) {
                    if ($conditions['from_drop_date'] > $conditions['to_drop_date']) {
                        $date_error_msg2 = str_replace('XXXXX','降日',Config::get('m_CW0007'));
                    }
                }
                // エクセル出力
                if (empty($date_error_msg1) && empty($date_error_msg2)) {
                    $this->export($conditions);
                }
            } elseif (!empty($output_dl)) {
                // 配車表出力ボタンが押下された場合の処理
                $conditions = D0040::setForms($conditions, Input::param());

                // 入力値チェック
                // 積日
                if (!empty($conditions['from_stack_date']) && !empty($conditions['to_stack_date'])) {
                    if ($conditions['from_stack_date'] > $conditions['to_stack_date']) {
                        $date_error_msg1 = str_replace('XXXXX','積日',Config::get('m_CW0007'));
                    }
                }
                // 降日
                if (!empty($conditions['from_drop_date']) && !empty($conditions['to_drop_date'])) {
                    if ($conditions['from_drop_date'] > $conditions['to_drop_date']) {
                        $date_error_msg2 = str_replace('XXXXX','降日',Config::get('m_CW0007'));
                    }
                }
                // 課
                if (!empty($conditions['division_code'])) {
                    if ($conditions['division_code'] == '000') {
                        $division_error_msg = Config::get('m_DW0013');
                    }
                }

                // エクセル出力
                if (empty($date_error_msg1) && empty($date_error_msg2) && empty($division_error_msg)) {
                    D0041::createExcel($conditions);
                }
            }
        } else {
            if ($cond = Session::get('d0040_list', array())) {
                $conditions = $cond;
                Session::delete('d0040_list');
            } else {
                $init_flag = true;
            }
            //初期表示もエクスポートに備えて条件保存する
            Session::set('d0040_list', $conditions);

        }

        if (empty($popup_flag)) {
            /**
             * ページング設定&検索実行
             */
            if (!$init_flag) {
                $total                      = D0040::getSearch('count', $conditions, null, null, D0040::$db);
            } else {
                // 初期表示時は検索しない
                $total = 0;
            }
            $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
            $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
            $limit                          = $pagination->per_page;
            $offset                         = $pagination->offset;
            if ($total > 0) {
                $dispatch                   = D0040::getSearch('search', $conditions, $offset, $limit, D0040::$db);
                if (!empty($dispatch)) {
                    foreach ($dispatch as $key => $val) {
                        $carrying               = D0040::getCarryingCharter(null, $val['dispatch_number'], D0040::$db);
                        $list_data[] = array(
                            'dispatch_number'       => $val['dispatch_number'],
                            'division_code'         => $val['division_code'],
                            'division_name'         => $val['division_name'],
                            'sales_status'          => $val['sales_status'],
                            'stack_date'            => $val['stack_date'],
                            'drop_date'             => $val['drop_date'],
                            'stack_place'           => $val['stack_place'],
                            'drop_place'            => $val['drop_place'],
                            'client_code'           => $val['client_code'],
                            'client_name'           => $val['client_name'],
                            'product_code'          => $val['product_code'],
                            'product_name'          => $val['product_name'],
                            'car_model_code'        => $val['car_model_code'],
                            'car_model_name'        => $val['car_model_name'],
                            'carrier_code'          => $val['carrier_code'],
                            'carrier_name'          => $val['carrier_name'],
                            'car_code'              => $val['car_code'],
                            // 'car_number'            => $val['car_number'],
                            'member_code'           => $val['member_code'],
                            'driver_name'           => $val['driver_name'],
                            'destination'           => $val['destination'],
                            'phone_number'          => $val['phone_number'],
                            'carrying_count'        => $val['carrying_count'],
                            'remarks'               => $val['remarks'],
                            'delivery_category'     => $val['delivery_category'],
                            'tax_category'          => $val['tax_category'],
                            'claim_sales'           => $val['claim_sales'],
                            'carrier_payment'       => $val['carrier_payment'],
                            'claim_highway_fee'     => $val['claim_highway_fee'],
                            'claim_highway_claim'   => $val['claim_highway_claim'],
                            'carrier_highway_fee'   => $val['carrier_highway_fee'],
                            'carrier_highway_claim' => $val['carrier_highway_claim'],
                            'driver_highway_fee'    => $val['driver_highway_fee'],
                            'driver_highway_claim'  => $val['driver_highway_claim'],
                            'allowance'             => $val['allowance'],
                            'overtime_fee'          => $val['overtime_fee'],
                            'stay'                  => $val['stay'],
                            'linking_wrap'          => $val['linking_wrap'],
                            'round_trip'            => $val['round_trip'],
                            'drop_appropriation'    => $val['drop_appropriation'],
                            'receipt_send_date'     => $val['receipt_send_date'],
                            'receipt_receive_date'  => $val['receipt_receive_date'],
                            'in_house_remarks'      => $val['in_house_remarks'],
                            'carrying_count'        => \DB::count_last_query(D0040::$db)
                        );
                    }
                }
            } elseif (Input::method() == 'POST' && Security::check_token() && empty($error_msg) && empty($date_error_msg1) && empty($date_error_msg2) && empty($division_error_msg)) {
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

                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'product_list'              => $this->product_list,
                'carmodel_list'             => $this->carmodel_list,
                'delivery_category_list'    => $this->delivery_category_list,
                'tax_category_list'         => $this->tax_category_list,
                'sales_category_list'       => $this->sales_category_list,
                'sales_status_list'         => $this->sales_status_list,

                'error_message'             => $error_msg,
                'date_error_message1'       => $date_error_msg1,
                'date_error_message2'       => $date_error_msg2,
                'division_error_msg'        => $division_error_msg,
            )
        );
        $this->template->content->set_safe('pager', (empty($popup_flag)) ? $pagination->render():'');

    }

    public function action_carrying() {

        $res               = array();
        $dispatch_number   = Input::param('id', '');

        $res['dispatch']   = D0040::getDispatchCharter($dispatch_number, D0040::$db);
        $res['carrying']   = D0040::getCarryingCharter(null, $dispatch_number, D0040::$db);

        return $this->response($res);
    }

    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = D0040::getNameById($type, $code, D0040::$db);

        return $this->response($data);
    }

    private function export() {

        $excel_data                 = array();
        $file                       = date('Ymd').'_配車一覧表';
        $headers                    = D0040::getHeader();

        // 課リスト取得
        $division_list            = GenerateList::getDivisionList(true, D0040::$db);
        // 商品リスト取得
        $product_list             = GenerateList::getProductList(true, D0040::$db);
        // 車種リスト
        $carmodel_list            = GenerateList::getCarModelList(true, D0040::$db);
        // 配車区分
        $delivery_category_list   = GenerateList::getDeliveryCategoryList(true, D0040::$db);
        // 税区分
        $tax_category_list        = GenerateList::getTaxCategoryList(true, D0040::$db);
        // 売上区分リスト
        $sales_category_list      = GenerateList::getSalesCategoryList(true, D0040::$db);
        // 売上ステータスリスト
        $sales_status_list        = GenerateList::getSalesStatusList(true);

        if ($conditions = Session::get('d0040_list', false)) {
            $excel_data[] = $headers;
            $total = D0040::getSearch('count', $conditions, null, null, D0040::$db);
            \DB::select(\DB::expr('NOW()'))->execute(D0040::$db);
            if (0 < $total && $total <= $this->export_limit) {
                $res    = D0040::getSearch('export', $conditions, null, null, D0040::$db);
                \DB::select(\DB::expr('NOW()'))->execute(D0040::$db);
                if (!empty($res)) {
                    foreach($res as $key => $val){
                        if ($carrying = D0040::getCarryingCharter(null, $val['dispatch_number'], D0040::$db)){
                            foreach ($carrying as $cnt => $v) {
                                // 分載データ有
                                $excel_data[]      = array(
                                    'dispatch_number'               => ($cnt == 0) ? sprintf('%010d', $val['dispatch_number']):'',
                                    'division_code'                 => ($cnt == 0) ? sprintf('%03d', $val['division_code']):'',
                                    'division_name'                 => ($cnt == 0) ? $val['division_name']:'',
                                    'sales_status'                  => ($cnt == 0) ? ($val['sales_status'] == '2') ? '○':'×':'',
                                    'stack_date'                    => ($cnt == 0) ? str_replace('-', '/', $val['stack_date']):'',
                                    'drop_date'                     => ($cnt == 0) ? str_replace('-', '/', $val['drop_date']):'',
                                    'stack_place'                   => ($cnt == 0) ? $val['stack_place']:'',
                                    'drop_place'                    => ($cnt == 0) ? $val['drop_place']:'',
                                    'client_code'                   => ($cnt == 0) ? sprintf('%05d', $val['client_code']):'',
                                    'client_name'                   => ($cnt == 0) ? $val['client_name']:'',
                                    'product_code'                  => ($cnt == 0) ? sprintf('%04d', $val['product_code']):'',
                                    'product_name'                  => ($cnt == 0) ? $val['product_name']:'',
                                    'car_model_code'                => ($cnt == 0) ? sprintf('%03d', $val['car_model_code']):'',
                                    'car_model_name'                => ($cnt == 0) ? $val['car_model_name']:'',
                                    'carrier_code'                  => ($cnt == 0) ? sprintf('%05d', $val['carrier_code']):'',
                                    'carrier_name'                  => ($cnt == 0) ? $val['carrier_name']:'',
                                    'car_code'                      => ($cnt == 0) ? sprintf('%04d', $val['car_code']):'',
                                    // 'car_number'                    => ($cnt == 0) ? $val['car_number']:'',
                                    'member_code'                   => ($cnt == 0) ? sprintf('%05d', $val['member_code']):'',
                                    'driver_name'                   => ($cnt == 0) ? $val['driver_name']:'',
                                    'phone_number'                  => ($cnt == 0) ? $val['phone_number']:'',
                                    'carrying_flg'                  => ($cnt == 0) ? 'あり':'',
                                    'remarks'                       => ($cnt == 0) ? $val['remarks']:'',
                                    'delivery_category'             => ($cnt == 0) ? isset($delivery_category_list[$val['delivery_category']]) ? $delivery_category_list[$val['delivery_category']] : '' :'',
                                    'tax_category'                  => ($cnt == 0) ? isset($tax_category_list[$val['tax_category']]) ? $tax_category_list[$val['tax_category']]:'':'',
                                    'claim_sales'                   => ($cnt == 0) ? (!empty($val['claim_sales'])) ? number_format($val['claim_sales']):'0':'',
                                    'carrier_payment'               => ($cnt == 0) ? (!empty($val['carrier_payment'])) ? number_format($val['carrier_payment']):'0':'',
                                    'claim_highway_fee'             => ($cnt == 0) ? (!empty($val['claim_highway_fee'])) ? number_format($val['claim_highway_fee']):'0':'',
                                    'claim_highway_claim'           => ($cnt == 0) ? ($val['claim_highway_claim'] == '2') ? '○':'×':'',
                                    'carrier_highway_fee'           => ($cnt == 0) ? (!empty($val['carrier_highway_fee'])) ? number_format($val['carrier_highway_fee']):'0':'',
                                    'carrier_highway_claim'         => ($cnt == 0) ? ($val['carrier_highway_claim'] == '2') ? '○':'×':'',
                                    'driver_highway_fee'            => ($cnt == 0) ? (!empty($val['driver_highway_fee'])) ? number_format($val['driver_highway_fee']):'0':'',
                                    'driver_highway_claim'          => ($cnt == 0) ? ($val['driver_highway_claim'] == '2') ? '○':'×':'',
                                    'allowance'                     => ($cnt == 0) ? (!empty($val['allowance'])) ? number_format($val['allowance']):'0':'',
                                    'overtime_fee'                  => ($cnt == 0) ? (!empty($val['overtime_fee'])) ? number_format($val['overtime_fee']):'0':'',
                                    'stay'                          => ($cnt == 0) ? (!empty($val['stay'])) ? number_format($val['stay']):'0':'',
                                    'linking_wrap'                  => ($cnt == 0) ? (!empty($val['linking_wrap'])) ? number_format($val['linking_wrap']):'0':'',
                                    'round_trip'                    => ($cnt == 0) ? ($val['round_trip'] == '2') ? '○':'×':'',
                                    'drop_appropriation'            => ($cnt == 0) ? ($val['drop_appropriation'] == '2') ? '○':'×':'',
                                    'receipt_send_date'             => ($cnt == 0) ? str_replace('-', '/', $val['receipt_send_date']):'',
                                    'receipt_receive_date'          => ($cnt == 0) ? str_replace('-', '/', $val['receipt_receive_date']):'',
                                    'in_house_remarks'              => ($cnt == 0) ? $val['in_house_remarks']:'',
                                    'carrying_number'               => sprintf('%010d', $v['carrying_number']),
                                    'carrying_dispatch_number'      => sprintf('%010d', $v['dispatch_number']),
                                    'carrying_stack_date'           => str_replace('-', '/', $v['stack_date']),
                                    'carrying_drop_date'            => str_replace('-', '/', $v['drop_date']),
                                    'carrying_stack_place'          => $v['stack_place'],
                                    'carrying_drop_place'           => $v['drop_place'],
                                    'carrying_client_code'          => sprintf('%05d', $v['client_code']),
                                    'carrying_client_name'          => $v['client_name'],
                                    'carrying_car_model_code'       => sprintf('%03d', $v['car_model_code']),
                                    'carrying_car_model_name'       => $v['car_model_name'],
                                    'carrying_carrier_code'         => sprintf('%05d', $v['carrier_code']),
                                    'carrying_carrier_name'         => $v['carrier_name'],
                                    'carrying_car_code'             => sprintf('%04d', $v['car_code']),
                                    // 'carrying_car_number'           => $v['car_number'],
                                    'carrying_member_code'          => sprintf('%05d', $v['member_code']),
                                    'carrying_driver_name'          => $v['driver_name'],
                                    'carrying_phone_number'         => $v['phone_number'],
                                    'carrying_destination'          => $v['destination'],
                                    'carrying_claim_sales'          => (!empty($v['claim_sales'])) ? number_format($v['claim_sales']):'0',
                                    'carrying_carrier_payment'      => (!empty($v['carrier_payment'])) ? number_format($v['carrier_payment']):'0',
                                    'carrying_claim_highway_fee'    => (!empty($v['claim_highway_fee'])) ? number_format($v['claim_highway_fee']):'0',
                                    'carrying_claim_highway_claim'  => ($v['claim_highway_claim'] == '2') ? '○':'×',
                                    'carrying_carrier_highway_fee'  => (!empty($v['carrier_highway_fee'])) ? number_format($v['carrier_highway_fee']):'0',
                                    'carrying_carrier_highway_claim'=> ($v['carrier_highway_claim'] == '2') ? '○':'×',
                                    'carrying_driver_highway_fee'   => (!empty($v['driver_highway_fee'])) ? number_format($v['driver_highway_fee']):'0',
                                    'carrying_driver_highway_claim' => ($v['driver_highway_claim'] == '2') ? '○':'×'
                                );
                            }
                        } else {
                            // 分載データ無
                            $excel_data[]       = array(
                                'dispatch_number'               => sprintf('%010d', $val['dispatch_number']),
                                'division_code'                 => sprintf('%03d', $val['division_code']),
                                'division_name'                 => $val['division_name'],
                                'sales_status'                  => ($val['sales_status'] == '2') ? '○':'×',
                                'stack_date'                    => str_replace('-', '/', $val['stack_date']),
                                'drop_date'                     => str_replace('-', '/', $val['drop_date']),
                                'stack_place'                   => $val['stack_place'],
                                'drop_place'                    => $val['drop_place'],
                                'client_code'                   => sprintf('%05d', $val['client_code']),
                                'client_name'                   => $val['client_name'],
                                'product_code'                  => sprintf('%04d', $val['product_code']),
                                'product_name'                  => $val['product_name'],
                                'car_model_code'                => sprintf('%03d', $val['car_model_code']),
                                'car_model_name'                => $val['car_model_name'],
                                'carrier_code'                  => sprintf('%05d', $val['carrier_code']),
                                'carrier_name'                  => $val['carrier_name'],
                                'car_code'                      => sprintf('%04d', $val['car_code']),
                                // 'car_number'                    => $val['car_number'],
                                'member_code'                   => sprintf('%05d', $val['member_code']),
                                'driver_name'                   => $val['driver_name'],
                                'phone_number'                  => $val['phone_number'],
                                'carrying_flg'                  => 'なし',
                                'remarks'                       => $val['remarks'],
                                'delivery_category'             => isset($delivery_category_list[$val['delivery_category']]) ? $delivery_category_list[$val['delivery_category']] : '',
                                'tax_category'                  => isset($tax_category_list[$val['tax_category']]) ? $tax_category_list[$val['tax_category']]:'',
                                'claim_sales'                   => (!empty($val['claim_sales'])) ? number_format($val['claim_sales']):'0',
                                'carrier_payment'               => (!empty($val['carrier_payment'])) ? number_format($val['carrier_payment']):'0',
                                'claim_highway_fee'             => (!empty($val['claim_highway_fee'])) ? number_format($val['claim_highway_fee']):'0',
                                'claim_highway_claim'           => ($val['claim_highway_claim'] == '2') ? '○':'×',
                                'carrier_highway_fee'           => (!empty($val['carrier_highway_fee'])) ? number_format($val['carrier_highway_fee']):'0',
                                'carrier_highway_claim'         => ($val['carrier_highway_claim'] == '2') ? '○':'×',
                                'driver_highway_fee'            => (!empty($val['driver_highway_fee'])) ? number_format($val['driver_highway_fee']):'0',
                                'driver_highway_claim'          => ($val['driver_highway_claim'] == '2') ? '○':'×',
                                'allowance'                     => (!empty($val['allowance'])) ? number_format($val['allowance']):'0',
                                'overtime_fee'                  => (!empty($val['overtime_fee'])) ? number_format($val['overtime_fee']):'0',
                                'stay'                          => (!empty($val['stay'])) ? number_format($val['stay']):'0',
                                'linking_wrap'                  => (!empty($val['linking_wrap'])) ? number_format($val['linking_wrap']):'0',
                                'round_trip'                    => ($val['round_trip'] == '2') ? '○':'×',
                                'drop_appropriation'            => ($val['drop_appropriation'] == '2') ? '○':'×',
                                'receipt_send_date'             => str_replace('-', '/', $val['receipt_send_date']),
                                'receipt_receive_date'          => str_replace('-', '/', $val['receipt_receive_date']),
                                'in_house_remarks'              => $val['in_house_remarks'],
                                'carrying_number'               => '',
                                'carrying_dispatch_number'      => '',
                                'carrying_stack_date'           => '',
                                'carrying_drop_date'            => '',
                                'carrying_stack_place'          => '',
                                'carrying_drop_place'           => '',
                                'carrying_client_code'          => '',
                                'carrying_client_name'          => '',
                                'carrying_car_model_code'       => '',
                                'carrying_car_model_name'       => '',
                                'carrying_carrier_code'         => '',
                                'carrying_carrier_name'         => '',
                                'carrying_car_code'             => '',
                                // 'carrying_car_number'           => '',
                                'carrying_member_code'          => '',
                                'carrying_driver_name'          => '',
                                'carrying_phone_number'         => '',
                                'carrying_destination'          => '',
                                'carrying_claim_sales'          => '',
                                'carrying_carrier_payment'      => '',
                                'carrying_claim_highway_fee'    => '',
                                'carrying_claim_highway_claim'  => '',
                                'carrying_carrier_highway_fee'  => '',
                                'carrying_carrier_highway_claim'=> '',
                                'carrying_driver_highway_fee'   => '',
                                'carrying_driver_highway_claim' => ''
                            );
                        }
                    }
                }
                /**
                 * Excel ファイルへの書き出し
                 */
                $title   = $file;
                $data    = $excel_data;
                // // データを追加
                // if (!empty($excel_data)) {
                //     $data[]  = array_keys($excel_data[0]); // ヘッダータイトル
                //     foreach ($excel_data as $key => $val) {
                //         $data[] = $val;
                //     }
                // }
                // Excelデータ作成
                $content = Data::create_dispatch_carrying('xlsx', $title, '配車データ', $data);
                // $content = Data::create_utf8($this->format, $title, $data);
                $this->response->set_header('Content-Disposition', 'attachment; filename="'.$file.'.xlsx"');
                return $this->response($content);

            }
        }

        Response::redirect(AccessControl::getActiveController());

    }

}
