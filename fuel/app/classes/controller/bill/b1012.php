<?php
/**
 * 保管料情報登録画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Bill\B1011;

class Controller_Bill_B1012 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 課リスト
    private $division_list              = array();
    // 売上ステータスリスト
    private $sales_status_list          = array();
    // 単位リスト
    private $unit_list                  = array();
    // 端数処理リスト
    private $rounding_list              = array();
    // 配送区分リスト
    private $delivery_category_list     = array();
    // 地区リスト
    private $area_list                  = array();
    // 車種リスト
    private $carmodel_list              = array();
    // 登録者リスト
    private $create_user_list           = array();
    // ユーザ情報
    private $user_authority             = null;

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'detail':
            case 'carrying':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){

        // 画面モード設定
        $this->mode                         = Input::param('mode', '');
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '請求情報編集';
        $cnf['page_id']                     = '[B1012]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '請求情報編集';
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
            // 'jquery_ui/jquery.ui.core.css',
            // 'jquery_ui/jquery.ui.datepicker.css',
            // 'jquery_ui/jquery.ui.theme.css',
            'common/jquery.jqplot.css',
            'common/jqModal.css'
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'common/style.css',
            'common/modal.css',
            'bill/b1011.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'bill/b1011.js',
            'bill/b1011_form.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, B1011::$db);
        // 売上ステータスリスト
        $this->sales_status_list        = GenerateList::getSalesStatusList(true, 2);
        // 配送区分
        $this->delivery_category_list   = GenerateList::getShareDeliveryCategoryList(false);
        // 地区リスト取得
        $this->area_list                = GenerateList::getAreaList(false, B1011::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(false, B1011::$db);
        // 端数処理リスト取得
        $this->rounding_list            = GenerateList::getRoundingList(false);
        // 車種リスト
        $this->car_model_list           = GenerateList::getCarModelList(false, B1011::$db);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(true, B1011::$db);
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
        $auth_data = AuthConfig::getAuthConfig('all');

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
	}

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions, $list_no) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = B1011::getSearchClient($code, B1011::$db)) {
                $conditions['list'][$list_no]['client_code']    = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name']    = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = B1011::getSearchCarrier($code, B1011::$db)) {
                $conditions['list'][$list_no]['carrier_code']   = $result[0]['carrier_code'];
                $conditions['list'][$list_no]['carrier_name']   = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = B1011::getSearchCar($code, B1011::$db)) {
                $conditions['list'][$list_no]['car_code']       = $result[0]['car_code'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = B1011::getSearchMember($code, B1011::$db)) {
                $conditions['list'][$list_no]['member_code']    = $result[0]['member_code'];
                $conditions['list'][$list_no]['driver_name']    = $result[0]['driver_name'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        } elseif ($code_list = Session::get('select_dispatch_code')) {
            // 配車履歴の検索にてレコード選択された場合
            $dispatch_code_list = explode(",", $code_list);
            $dispatch_code_count = 0;
            if (is_countable($dispatch_code_list)){
                $dispatch_code_count = count($dispatch_code_list);
            }
            for($i = 0; $i < $dispatch_code_count; $i++){
                if ($result = B1011::getDispatchShare($dispatch_code_list[$i], B1011::$db)) {
                    $list_no = $i;
                    $conditions['list'][$list_no]['sales_status']                   = $result['sales_status'];
                    $conditions['list'][$list_no]['delivery_code']                  = $result['delivery_code'];
                    $conditions['list'][$list_no]['area_code']                      = $result['area_code'];
                    switch ($result['delivery_code']) {
                        case '1':       // 納品日
                            $conditions['list'][$list_no]['destination_date']       = $result['delivery_date'];
                            $conditions['list'][$list_no]['destination']            = $result['delivery_place'];
                            break;
                        case '2':       // 引取日
                            $conditions['list'][$list_no]['destination_date']       = $result['pickup_date'];
                            $conditions['list'][$list_no]['destination']            = $result['pickup_place'];
                            break;
                        case '3':       // 納品日or引取日
                        default:
                            if (!empty($result['delivery_date'])) {
                                $conditions['list'][$list_no]['destination_date']   = $result['delivery_date'];
                                $conditions['list'][$list_no]['destination']        = $result['delivery_place'];
                            } elseif (!empty($result['pickup_date'])) {
                                $conditions['list'][$list_no]['destination_date']   = $result['pickup_date'];
                                $conditions['list'][$list_no]['destination']        = $result['pickup_place'];
                            }
                            break;
                    }
                    $conditions['list'][$list_no]['client_code']                    = $result['client_code'];
                    $conditions['list'][$list_no]['client_name']                    = $result['client_name'];
                    $conditions['list'][$list_no]['carrier_code']                   = $result['carrier_code'];
                    $conditions['list'][$list_no]['carrier_name']                   = $result['carrier_name'];
                    // 配車情報
                    $conditions['list'][$list_no]['car_model_code']                 = $result['car_model_code'];
                    $conditions['list'][$list_no]['car_code']                       = $result['car_code'];
                    $conditions['list'][$list_no]['member_code']                    = $result['member_code'];
                    $conditions['list'][$list_no]['driver_name']                    = $result['driver_name'];
                    $conditions['list'][$list_no]['onsite_flag']                    = 0;
                    $conditions['list'][$list_no]['requester']                      = $result['requester'];
                    $conditions['list'][$list_no]['inquiry_no']                     = $result['inquiry_no'];
                    $conditions['list'][$list_no]['delivery_address']               = $result['delivery_address'];
                    $conditions['list'][$list_no]['remarks1']                       = $result['remarks1'];
                    $conditions['list'][$list_no]['remarks2']                       = $result['remarks2'];
                    $conditions['list'][$list_no]['remarks3']                       = $result['remarks3'];
                    $conditions['list'][$list_no]['dispatch_number']                = $result['dispatch_number'];
                    // 請求情報
                    $conditions['list'][$list_no]['place']                          = 0;
                    $conditions['list'][$list_no]['unit_price']                     = 0.00;
                    $conditions['list'][$list_no]['volume']                         = $result['volume'];
                    $conditions['list'][$list_no]['unit_code']                      = $result['unit_code'];
                    $conditions['list'][$list_no]['product_name']                   = $result['product_name'];
                    $conditions['list'][$list_no]['rounding_code']                  = 1;
                } else {
                    $error_msg = Config::get('m_DW0001');
                }
            }
            Session::delete('select_dispatch_code');
        } elseif ($code_list = Session::get('select_bill_code')) {
            // 請求履歴の検索にてレコード選択された場合
            $bill_code_list = explode(",", $code_list);
            $bill_code_count = 0;
            if (is_countable($bill_code_list)){
                $bill_code_count = count($bill_code_list);
            }
            for($i = 0; $i < $bill_code_count; $i++){
                if ($result = B1011::getBillShare($bill_code_list[$i], B1011::$db)) {

                    $list_no = $i;
                    $conditions['division_code']                                    = $result['division_code'];
                    $conditions['list'][$list_no]['sales_status']                   = $result['sales_status'];
                    $conditions['list'][$list_no]['delivery_code']                  = $result['delivery_code'];
                    $conditions['list'][$list_no]['area_code']                      = $result['area_code'];
                    $conditions['list'][$list_no]['destination_date']               = $result['destination_date'];
                    $conditions['list'][$list_no]['destination']                    = $result['destination'];
                    $conditions['list'][$list_no]['client_code']                    = $result['client_code'];
                    $conditions['list'][$list_no]['client_name']                    = $result['client_name'];
                    $conditions['list'][$list_no]['carrier_code']                   = $result['carrier_code'];
                    $conditions['list'][$list_no]['carrier_name']                   = $result['carrier_name'];
                    // 配車情報
                    $conditions['list'][$list_no]['car_model_code']                 = $result['car_model_code'];
                    $conditions['list'][$list_no]['car_code']                       = $result['car_code'];
                    $conditions['list'][$list_no]['member_code']                    = $result['member_code'];
                    $conditions['list'][$list_no]['driver_name']                    = $result['driver_name'];
                    $conditions['list'][$list_no]['onsite_flag']                    = $result['onsite_flag'];
                    $conditions['list'][$list_no]['requester']                      = $result['requester'];
                    $conditions['list'][$list_no]['inquiry_no']                     = $result['inquiry_no'];
                    $conditions['list'][$list_no]['delivery_address']               = $result['delivery_address'];
                    $conditions['list'][$list_no]['remarks1']                       = $result['remarks1'];
                    $conditions['list'][$list_no]['remarks2']                       = $result['remarks2'];
                    $conditions['list'][$list_no]['remarks3']                       = $result['remarks3'];
                    $conditions['list'][$list_no]['dispatch_number']                = $result['dispatch_number'];
                    // 請求情報
                    $conditions['list'][$list_no]['price']                          = number_format($result['price']);
                    $conditions['list'][$list_no]['unit_price']                     = number_format($result['unit_price'], 2);
                    $conditions['list'][$list_no]['volume']                         = $result['volume'];
                    $conditions['list'][$list_no]['unit_code']                      = $result['unit_code'];
                    $conditions['list'][$list_no]['product_name']                   = $result['product_name'];
                    $conditions['list'][$list_no]['rounding_code']                  = $result['rounding_code'];
                } else {
                    $error_msg = Config::get('m_BW0002');
                }
            }
            Session::delete('select_bill_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        foreach ($conditions['list'] as $key => $val) {
            // ２レコード目以降で処理区分が更新または削除の場合はスルー
            if ($key > 0 && $conditions['processing_division'] != 1) {
                continue;
            }
            // バリデーション対象チェック
            // ２レコード目以降で指定項目が全て未入力の場合はスルー
            if (!B1011::chkBillShareDataNull($val) && $key > 0) {
                continue;
            }
            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');
            $item = B1011::getValidateItems();

            // 請求番号チェック
            $validation->add('bill_number', $item['bill_number']['name'])
                ->add_rule('trim_max_lengths', $item['bill_number']['max_lengths'])
                ->add_rule('is_numeric');
            // 運行日チェック
            $validation->add('destination_date', $item['destination_date']['name'])
                ->add_rule('required')
                ->add_rule('valid_date_format');
            // 運行先チェック
            $validation->add('destination', $item['destination']['name'])
                ->add_rule('trim_max_lengths', $item['destination']['max_lengths']);
            // 得意先チェック
            $validation->add('client_code', $item['client_code']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
                ->add_rule('is_numeric');
            // 傭車先チェック
            $validation->add('carrier_code', $item['carrier_code']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['carrier_code']['max_lengths'])
                ->add_rule('is_numeric');
            // 単価チェック
            $validation->add('unit_price', $item['unit_price']['name'])
                ->add_rule('trim_max_lengths_int', $item['unit_price']['max_lengths'])
                ->add_rule('is_numeric_decimal', 2, true);
            // 数量チェック
            $validation->add('volume', $item['volume']['name'])
                ->add_rule('trim_max_lengths_int', $item['volume']['max_lengths'])
                ->add_rule('is_numeric_decimal', 6, true);
            // 金額整合性チェック
            $price      = intval(str_replace(',', '', $val['price']));
            $unit_price = (float)str_replace(',', '', $val['unit_price']);
            $volume     = (float)str_replace(',', '', $val['volume']);
            if ($unit_price > 0.00) {
                $total_fee  = ($unit_price * $volume);
                switch ($val['rounding_code']) {
                    case '1':   // 四捨五入
                        $total_fee = round($total_fee);
                        break;
                    case '2':   // 切り上げ
                        $total_fee = ceil($total_fee);
                        break;
                    case '3':   // 切り捨て
                        $total_fee = floor($total_fee);
                        break;
                }
                $validation->add('price', $item['price']['name'])
                    ->add_rule('required_brank')
                    ->add_rule('trim_max_lengths', $item['price']['max_lengths'])
                    ->add_rule('is_numeric_conma')
                    ->add_rule('amount_check', $total_fee);
            }
            // 商品名チェック
            $validation->add('product_name', $item['product_name']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
            // 車両番号チェック
            $validation->add('car_code', $item['car_code']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['car_code']['max_lengths'])
                ->add_rule('is_numeric');
            // 運転手チェック
            $validation->add('driver_name', $item['driver_name']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['driver_name']['max_lengths']);
            // 依頼者チェック
            $validation->add('requester', $item['requester']['name'])
                ->add_rule('trim_max_lengths', $item['requester']['max_lengths']);
            // 問い合わせNoチェック
            $validation->add('inquiry_no', $item['inquiry_no']['name'])
                ->add_rule('trim_max_lengths', $item['inquiry_no']['max_lengths']);
            // 納品先住所チェック
            $validation->add('delivery_address', $item['delivery_address']['name'])
                ->add_rule('trim_max_lengths', $item['delivery_address']['max_lengths']);
            // 備考1チェック
            $validation->add('remarks1', $item['remarks1']['name'])
                ->add_rule('trim_max_lengths', $item['remarks1']['max_lengths']);
            // 備考2チェック
            $validation->add('remarks2', $item['remarks2']['name'])
                ->add_rule('trim_max_lengths', $item['remarks2']['max_lengths']);
            // 備考3チェック
            $validation->add('remarks3', $item['remarks3']['name'])
                ->add_rule('trim_max_lengths', $item['remarks3']['max_lengths']);

            $value = array(
                           'bill_number'        => $val['bill_number'],
                           'destination_date'   => $val['destination_date'],
                           'destination'        => $val['destination'],
                           'client_code'        => $val['client_code'],
                           'carrier_code'       => $val['carrier_code'],
                           'unit_price'         => $val['unit_price'],
                           'volume'             => $val['volume'],
                           'price'              => $val['price'],
                           'product_name'       => $val['product_name'],
                           'car_code'           => $val['car_code'],
                           'driver_name'        => $val['driver_name'],
                           'requester'          => $val['requester'],
                           'inquiry_no'         => $val['inquiry_no'],
                           'delivery_address'   => $val['delivery_address'],
                           'remarks1'           => $val['remarks1'],
                           'remarks2'           => $val['remarks2'],
                           'remarks3'           => $val['remarks3'],
                           );

            if (!$validation->run($value)) {
                $error = $validation->error();

                foreach ($error as $i => $v) {
                    if (preg_match('/bill_number/', $i)) {
                        $error_item     = 'bill_number';
                        $error_column   = '請求番号';
                    } elseif (preg_match('/dispatch_number/', $i)) {
                        $error_item     = 'dispatch_number';
                        $error_column   = '配車番号';
                    } elseif (preg_match('/destination_date/', $i)) {
                        $error_item     = 'destination_date';
                        $error_column   = '運行日';
                    } elseif (preg_match('/destination/', $i)) {
                        $error_item     = 'destination';
                        $error_column   = '運行先';
                    } elseif (preg_match('/client_code/', $i)) {
                        $error_item     = 'client_code';
                        $error_column   = '得意先No';
                    } elseif (preg_match('/carrier_code/', $i)) {
                        $error_item     = 'carrier_code';
                        $error_column   = '傭車先No';
                    } elseif (preg_match('/unit_price/', $i)) {
                        $error_item     = 'unit_price';
                        $error_column   = '単価';
                    } elseif (preg_match('/volume/', $i)) {
                        $error_item     = 'volume';
                        $error_column   = '数量';
                    } elseif (preg_match('/price/', $i)) {
                        $error_item     = 'price';
                        $error_column   = '金額';
                    } elseif (preg_match('/product_name/', $i)) {
                        $error_item     = 'product_name';
                        $error_column   = '商品名';
                    } elseif (preg_match('/car_code/', $i)) {
                        $error_item     = 'car_code';
                        $error_column   = '車両番号';
                    } elseif (preg_match('/driver_name/', $i)) {
                        $error_item     = 'driver_name';
                        $error_column   = '運転手';
                    } elseif (preg_match('/requester/', $i)) {
                        $error_item     = 'requester';
                        $error_column   = '依頼者';
                    } elseif (preg_match('/inquiry_no/', $i)) {
                        $error_item     = 'inquiry_no';
                        $error_column   = '問い合わせNo';
                    } elseif (preg_match('/delivery_address/', $i)) {
                        $error_item     = 'delivery_address';
                        $error_column   = '納品先住所';
                    } elseif (preg_match('/remarks1/', $i)) {
                        $error_item     = 'remarks1';
                        $error_column   = '備考1';
                    } elseif (preg_match('/remarks2/', $i)) {
                        $error_item     = 'remarks2';
                        $error_column   = '備考2';
                    } elseif (preg_match('/remarks3/', $i)) {
                        $error_item     = 'remarks3';
                        $error_column   = '備考3';
                    }

                    $item           = B1011::getValidateItems();
                    $column_length  = $item[$error_item]['max_lengths'];

                    if ($error[$i]->rule == 'required' || $error[$i]->rule == 'required_brank') {
                        return str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($error[$i]->rule == 'trim_max_lengths' || $validation->error()[$key]->rule == 'trim_max_lengths_int') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                        return str_replace('xxxxx',$column_length,$error_msg);
                    } elseif ($error[$i]->rule == 'amount_check') {
                        return Config::get('m_BW0013');
                    } elseif ($error[$i]->rule == 'valid_date_format') {
                        return str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($error[$i]->rule == 'is_numeric' || $error[$i]->rule == 'is_numeric_conma') {
                        return str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    }

                }
            }
        }
        return null;
    }

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg = null;

        // レコード登録
        $error_msg = B1011::create_record($conditions, B1011::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        Config::load('message');
        $error_msg                  = null;

        // レコード存在チェック
        if (!$result = B1011::getBillShare($conditions['bill_number'], B1011::$db)) {
            return Config::get('m_CW0010');
        }

        // レコード更新
        $error_msg = B1011::update_record($conditions, B1011::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;

    }

    // 削除処理
    private function delete_record($conditions) {

        Config::load('message');
        $error_msg = null;

        if (empty($conditions['bill_number'])) {
            return Config::get('m_BW0019');
        }

        // レコード存在チェック
        if (!$result = B1011::getBillShare($conditions['bill_number'], B1011::$db)) {
            return Config::get('m_CW0010');
        }

        $error_msg = B1011::delete_record($conditions, B1011::$db);
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
        $cnt                = 0;
        $error_msg          = null;
        $init_flag          = false;
        $redirect_flag      = false;
        $conditions         = B1011::getForms('bill_share');
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $select_cancel      = Session::get('select_cancel');
        $bill_number        = Input::param('bill_number', '');

        if (false !== Input::param('init', false)) {
            // 初期表示
            Session::delete('b1012_list');
        }

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('b1012_list');
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = B1011::setForms('bill_share', $conditions, Input::param());

            if ($conditions['processing_division'] == 2) {
                // 更新の時のみ
                // 入力値チェック
                $error_msg = $this->validate_info($conditions);
            }

            // 特殊入力値チェック
            if (empty($error_msg)) {
                // 配車番号が設定されているもののレコード重複チェック
                foreach ($conditions['list'] as $key => $val) {

                    // ２レコード目以降で指定項目が全て未入力の場合はスルー
                    if (!B1011::chkBillShareDataNull($val) && $key > 0) {
                        continue;
                    }

                    // 金額整合性チェック
                    $price      = intval(str_replace(',', '', $val['price']));
                    $unit_price = (float)str_replace(',', '', $val['unit_price']);
                    $volume     = (float)str_replace(',', '', $val['volume']);

                    if ($unit_price > 0.00) {
                        $total_fee = ($unit_price * $volume);
                        switch ($val['rounding_code']) {
                            case '1':   // 四捨五入
                                $total_fee = round($total_fee);
                                break;
                            case '2':   // 切り上げ
                                $total_fee = ceil($total_fee);
                                break;
                            case '3':   // 切り捨て
                                $total_fee = floor($total_fee);
                                break;
                        }
                        $data1 = $price;
                        $data2 = intval(str_replace(',', '', $total_fee));

                        if ($data1 != $data2) {
                            $error_msg = Config::get('m_BW0013')."[金額　:　". $val['price']."]";
                            break;
                        }
                    }
                }
            }

            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(B1011::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 指定項目が全て未入力の場合はスルー
                        if (!B1011::chkBillShareDataNull($val)) {
                            continue;
                        }

                        $val['division_code'] = $conditions['division_code'];
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                $error_msg = $this->create_record($val);
                                break;
                            case '2':
                                // 更新処理
                                $val['bill_number'] = $conditions['bill_number'];
                                $error_msg = $this->update_record($val);
                                break;
                            case '3':
                                // 削除処理
                                $val['bill_number'] = $conditions['bill_number'];
                                $error_msg = $this->delete_record($val);
                                break;
                        }
                    }
                    if (empty($error_msg)) {
                        DB::commit_transaction(B1011::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_BI0005')."');</script>";
                                break;
                            case '2':
                                // 更新処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_BI0006')."');</script>";
                                break;
                            case '3':
                                // 削除処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_BI0007')."');</script>";
                                break;
                        }
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = B1011::getForms();
                    Session::delete('b1012_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(B1011::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('b1012_list');
            Session::set('b1012_list', $conditions);
        } else {
            $conditions = B1011::setForms('bill_share', $conditions, Input::param());

            if ($cond = Session::get('b1012_list', array())) {
                $conditions = $cond;
                Session::delete('b1012_list');
            }

            if (!empty($bill_number)) {
                Session::set('select_bill_code', $bill_number);
                $conditions['bill_number'] = $bill_number;
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);
            }
            Session::delete('select_client_code');
            Session::delete('select_carrier_code');
            Session::delete('select_car_code');
            Session::delete('select_member_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('b1012_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('bill/b1010?init')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'sales_status_list'         => $this->sales_status_list,
                'delivery_category_list'    => $this->delivery_category_list,
                'area_list'                 => $this->area_list,
                'unit_list'                 => $this->unit_list,
                'rounding_list'             => $this->rounding_list,
                'car_model_list'            => $this->car_model_list,
                'create_user_list'          => $this->create_user_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

}
