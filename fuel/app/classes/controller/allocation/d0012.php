<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Allocation\D0010;

class Controller_Allocation_D0012 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // ページネーション
    private $pagenation_config = array(
        'uri_segment' 	=> 'p',
    	'num_links' 	=> 2,
    	'per_page' 		=> 50,
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
        $cnf['header_title']                = '配車入力（チャーター便）';
        $cnf['page_id']                     = '[D0010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '配車入力（チャーター便）';
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
            'common/modal.css'
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'allocation/d0010.js',
            'allocation/d0010_form.js',
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
        $this->division_list            = GenerateList::getDivisionList(false, D0010::$db);
        // 役職リスト取得
        $this->position_list            = GenerateList::getPositionList(false, D0010::$db);
        // 商品リスト取得
        $this->product_list             = GenerateList::getProductList(false, D0010::$db);
        // 車種リスト
        $this->car_model_list           = GenerateList::getCarModelList(false, D0010::$db);
        // 配車区分
        $this->delivery_category_list   = GenerateList::getDeliveryCategoryList(false, D0010::$db);
        // 税区分
        $this->tax_category_list        = GenerateList::getTaxCategoryList(false, D0010::$db);
        // 会社区分リスト取得
        $this->company_section_list     = GenerateList::getCompanySectionList(true);
        // 締日リスト取得
        $this->closing_date_list        = GenerateList::getClosingDateList(true);

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
            if ($result = D0010::getSearchClient($code, D0010::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D0010::getSearchCarrier($code, D0010::$db)) {
                $conditions['list'][$list_no]['carrier_code'] = $result[0]['carrier_code'];
                $conditions['list'][$list_no]['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            if ($result = D0010::getSearchProduct($code, D0010::$db)) {
                $conditions['list'][$list_no]['product_code'] = $result[0]['product_code'];
                $conditions['list'][$list_no]['product_name'] = $result[0]['product_name'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D0010::getSearchCar($code, D0010::$db)) {
                $conditions['list'][$list_no]['car_code']   = $result[0]['car_code'];
                $conditions['list'][$list_no]['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D0010::getMember($code, D0010::$db)) {
                $conditions['list'][$list_no]['member_code']    = $result[0]['member_code'];
                $conditions['list'][$list_no]['driver_name']    = $result[0]['driver_name'];
                $conditions['list'][$list_no]['phone_number']   = $result[0]['phone_number'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        } elseif ($code = Session::get('select_dispatch_code')) {
            // 配車履歴の検索にてレコード選択された場合
            if ($result = D0010::getDispatchCharter($code, D0010::$db)) {
                $carrying                                               = D0010::getCarryingCharter(null, $result['dispatch_number'], D0010::$db);
                $conditions['dispatch_number']                          = $result['dispatch_number'];
                $conditions['division_code']                            = $result['division_code'];
                $conditions['list'][$list_no]['sales_status']           = $result['sales_status'];
                $conditions['list'][$list_no]['stack_date']             = $result['stack_date'];
                $conditions['list'][$list_no]['drop_date']              = $result['drop_date'];
                $conditions['list'][$list_no]['stack_place']            = $result['stack_place'];
                $conditions['list'][$list_no]['drop_place']             = $result['drop_place'];
                $conditions['list'][$list_no]['client_code']            = $result['client_code'];
                $conditions['list'][$list_no]['client_name']            = $result['client_name'];
                $conditions['list'][$list_no]['product_code']           = $result['product_code'];
                $conditions['list'][$list_no]['product_name']           = $result['product_name'];
                $conditions['list'][$list_no]['car_model_code']         = $result['car_model_code'];
                $conditions['list'][$list_no]['carrier_code']           = $result['carrier_code'];
                $conditions['list'][$list_no]['carrier_name']           = $result['carrier_name'];
                $conditions['list'][$list_no]['car_code']               = $result['car_code'];
                // $conditions['list'][$list_no]['car_number']             = $result['car_number'];
                $conditions['list'][$list_no]['member_code']            = $result['member_code'];
                $conditions['list'][$list_no]['driver_name']            = $result['driver_name'];
                $conditions['list'][$list_no]['phone_number']           = $result['phone_number'];
                $conditions['list'][$list_no]['carrying_count']         = $result['carrying_count'];
                $conditions['list'][$list_no]['remarks']                = $result['remarks'];
                $conditions['list'][$list_no]['destination']            = $result['destination'];
                $conditions['list'][$list_no]['delivery_category']      = $result['delivery_category'];
                $conditions['list'][$list_no]['tax_category']           = $result['tax_category'];
                $conditions['list'][$list_no]['claim_sales']            = (!empty($result['claim_sales'])) ? $result['claim_sales']:0;
                $conditions['list'][$list_no]['carrier_payment']        = (!empty($result['carrier_payment'])) ? $result['carrier_payment']:0;
                $conditions['list'][$list_no]['claim_highway_fee']      = (!empty($result['claim_highway_fee'])) ? $result['claim_highway_fee']:0;
                $conditions['list'][$list_no]['claim_highway_claim']    = $result['claim_highway_claim'];
                $conditions['list'][$list_no]['carrier_highway_fee']    = (!empty($result['carrier_highway_fee'])) ? $result['carrier_highway_fee']:0;
                $conditions['list'][$list_no]['carrier_highway_claim']  = $result['carrier_highway_claim'];
                $conditions['list'][$list_no]['driver_highway_fee']     = (!empty($result['driver_highway_fee'])) ? $result['driver_highway_fee']:0;
                $conditions['list'][$list_no]['driver_highway_claim']   = $result['driver_highway_claim'];
                $conditions['list'][$list_no]['allowance']              = (!empty($result['allowance'])) ? $result['allowance']:0;
                $conditions['list'][$list_no]['overtime_fee']           = (!empty($result['overtime_fee'])) ? $result['overtime_fee']:0;
                $conditions['list'][$list_no]['stay']                   = (!empty($result['stay'])) ? $result['stay']:0;
                $conditions['list'][$list_no]['linking_wrap']           = (!empty($result['linking_wrap'])) ? $result['linking_wrap']:0;
                $conditions['list'][$list_no]['round_trip']             = $result['round_trip'];
                $conditions['list'][$list_no]['drop_appropriation']     = $result['drop_appropriation'];
                $conditions['list'][$list_no]['receipt_send_date']      = $result['receipt_send_date'];
                $conditions['list'][$list_no]['receipt_receive_date']   = $result['receipt_receive_date'];
                $conditions['list'][$list_no]['in_house_remarks']       = $result['in_house_remarks'];

                // $conditions['list'][$list_no]['carrying_count']     = $carrying;
                if (!empty($carrying)) {
                    $conditions['list'][$list_no]['carrying']           = $carrying;
                }
            } else {
                $error_msg = Config::get('m_DW0001');
            }
            Session::delete('select_dispatch_code');
        } elseif ($cond = Session::get('carrying_charter')) {
            // 分載にてレコード確定された場合
            $conditions             = $cond;
            $claim_sales            = 0;
            $carrier_payment        = 0;
            $cnt                    = 0;
            if (isset($cond['carrying'])) {
                foreach ($cond['carrying'] as $key => $val) {
                    if (!empty($val['car_model_code']) && !empty($val['car_code']) && !empty($val['driver_name']) &&
                        !empty($val['phone_number']) && !empty($val['claim_sales']) && !empty($val['carrier_payment'])) {
                        $claim_sales        = ($claim_sales + $val['claim_sales']);
                        $carrier_payment    = ($carrier_payment + $val['carrier_payment']);
                        $cnt++;
                    }
                }
                // // 請求売上合計
                // $conditions['list'][$list_no]['claim_sales']        = $claim_sales;
                // // 庸車支払合計
                // $conditions['list'][$list_no]['carrier_payment']    = $carrier_payment;
            }
            Session::set('carrying_charter', $conditions);
            if ($cnt > 1) {
                // 分載データ取得
            } else {
                $error_msg = Config::get('m_DW0001');
            }
            Session::delete('select_code');
        }
        Session::set('d0012_list', $conditions);

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
            // 下記項目が全て未入力の場合はスルー
            if (empty($val['stack_place']) && empty($val['drop_place']) && empty($val['client_code']) && empty($val['carrier_code']) && empty($val['driver_name']) && empty($val['phone_number']) && empty($val['car_code'])) {
                continue;
            }
            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');

            // 積日チェック
            $validation->add('list['.$key.'][stack_date]', '積日')
                ->add_rule('required');
            // 降日チェック
            $validation->add('list['.$key.'][drop_date]', '降日')
                ->add_rule('required');
            // 積地チェック
//            $validation->add('list['.$key.'][stack_place]', '積地')
//                ->add_rule('required');
            // 降地チェック
//            $validation->add('list['.$key.'][drop_place]', '降地')
//                ->add_rule('required');
            // 得意先Noチェック
            $validation->add('list['.$key.'][client_code]', '得意先No')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
            // 庸車先Noチェック
            $validation->add('list['.$key.'][carrier_code]', '傭車先No')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
            // 商品チェック
            $validation->add('list['.$key.'][product_name]', '商品')
                ->add_rule('required');
            // 車種チェック
            $validation->add('list['.$key.'][car_model_code]', '車種')
                ->add_rule('required');
            // 車番チェック
            $validation->add('list['.$key.'][car_code]', '車番')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 4)
                ->add_rule('is_numeric');
            // 運転手チェック
            $validation->add('list['.$key.'][driver_name]', '運転手')
                ->add_rule('required');
            // 配送区分チェック
            $validation->add('list['.$key.'][delivery_category]', '配送区分')
                ->add_rule('required');
            // 税区分チェック
            $validation->add('list['.$key.'][tax_category]', '税区分')
                ->add_rule('required');
            // 請求売上チェック
            $validation->add('list['.$key.'][claim_sales]', '請求売上')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 庸車支払チェック
            $validation->add('list['.$key.'][carrier_payment]', '庸車支払')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 請求高速料金チェック
            $validation->add('list['.$key.'][claim_highway_fee]', '請求高速料金')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 庸車高速料金チェック
            $validation->add('list['.$key.'][carrier_highway_fee]', '庸車高速料金')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // ドライバー高速料金チェック
            $validation->add('list['.$key.'][driver_highway_fee]', 'ドライバー高速料金')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 手当チェック
            $validation->add('list['.$key.'][allowance]', '手当')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 時間外チェック
            $validation->add('list['.$key.'][overtime_fee]', '時間外')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 泊まりチェック
            $validation->add('list['.$key.'][stay]', '泊まり')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            // 連結・ラップチェック
            $validation->add('list['.$key.'][linking_wrap]', '連結・ラップ')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('is_numeric');
            $validation->run();

        }
        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg                  = null;
        $carrying_flg               = false;
        $carrying_claim_sales       = 0;
        $carrying_carrier_payment   = 0;

        // 対象選択
        if (
             !empty($conditions['stack_date']) ||
             !empty($conditions['drop_date']) ||
             !empty($conditions['stack_place']) ||
             !empty($conditions['drop_place']) ||
             !empty($conditions['client_code']) ||
             !empty($conditions['carrier_code']) ||
             !empty($conditions['car_code']) ||
             !empty($conditions['driver_name']) ||
             !empty($conditions['phone_number']) ||
             !empty($conditions['destination'])
        ) {

            if (!empty($conditions['carrying'])) {
                foreach ($conditions['carrying'] as $key => $val) {
                    if (!empty($val['car_code']) && !empty($val['driver_name'])) {
                        $carrying_flg               = true;
                        $carrying_claim_sales       = $carrying_claim_sales + $val['claim_sales'];
                        $carrying_carrier_payment   = $carrying_carrier_payment + $val['carrier_payment'];
                    }
                }
            }

            // 分載の金額整合性チェック
            if ($carrying_flg === true) {
                // 分載レコードにある請求売上合計と、入力項目の請求売上の値が一致するかチェック
                if ($conditions['claim_sales'] != $carrying_claim_sales) {
                    return Config::get('m_DW0008');
                }
                // 分載レコードにある庸車支払合計と、入力項目の庸車支払の値が一致するかチェック
                //if ($conditions['carrier_payment'] != $carrying_carrier_payment) {
                //    return Config::get('m_DW0009');
                //}
            }

            // 傭車先コード取得
            if (empty($conditions['carrier_code'])) {
                $conditions['carrier_code'] = D0010::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0010::$db);
            }
            if (empty($conditions['carrier_code'])) {
                return str_replace('XXXXX','庸車No',Config::get('m_CW0005'));
            }
            // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
//            if (D0010::OurCompanyCheck($conditions['carrier_code'], D0010::$db)) {
//                // 車両コードが車両マスタに登録されているかチェック
//                if (!D0010::getNameById('car', $conditions['car_code'], D0010::$db)) {
//                    return Config::get('m_DW0021');
//                }
//            }

            // レコード登録
            $error_msg = D0010::create_record($conditions, D0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
        }

        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        Config::load('message');
        $error_msg                  = null;
        $carrying_flg               = false;
        $carrying_claim_sales       = 0;
        $carrying_carrier_payment   = 0;

        // 対象選択
        if (
             !empty($conditions['stack_date']) ||
             !empty($conditions['drop_date']) ||
             !empty($conditions['stack_place']) ||
             !empty($conditions['drop_place']) ||
             !empty($conditions['client_code']) ||
             !empty($conditions['carrier_code']) ||
             !empty($conditions['car_code']) ||
             !empty($conditions['driver_name']) ||
             !empty($conditions['phone_number']) ||
             !empty($conditions['destination'])
        ) {

            if (!empty($conditions['carrying'])) {
                foreach ($conditions['carrying'] as $key => $val) {
                    if (!empty($val['car_code']) && !empty($val['driver_name'])) {
                        $carrying_flg               = true;
                        $carrying_claim_sales       = $carrying_claim_sales + $val['claim_sales'];
                        $carrying_carrier_payment   = $carrying_carrier_payment + $val['carrier_payment'];
                    }
                }
            }

            // 分載の金額整合性チェック
            if ($carrying_flg === true) {
                // 分載レコードにある請求売上合計と、入力項目の請求売上の値が一致するかチェック
                if ($conditions['claim_sales'] != $carrying_claim_sales) {
                    return Config::get('m_DW0008');
                }
                // 分載レコードにある庸車支払合計と、入力項目の庸車支払の値が一致するかチェック
                //if ($conditions['carrier_payment'] != $carrying_carrier_payment) {
                //    return Config::get('m_DW0009');
                //}
            }

            // 傭車先コード取得
            if (empty($conditions['carrier_code'])) {
                $conditions['carrier_code'] = D0010::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0010::$db);
            }
            if (empty($conditions['carrier_code'])) {
                return str_replace('XXXXX','庸車No',Config::get('m_CW0005'));
            }
            // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
//            if (D0010::OurCompanyCheck($conditions['carrier_code'], D0010::$db)) {
//                // 車両コードが車両マスタに登録されているかチェック
//                if (!D0010::getNameById('car', $conditions['car_code'], D0010::$db)) {
//                    return str_replace('XXXXX','車両コード',Config::get('m_CW0005'));
//                }
//            }

            // レコード存在チェック
            if (!$result = D0010::getDispatchCharter($conditions['dispatch_number'], D0010::$db)) {
                return Config::get('m_DW0001');
            }

            // レコード更新
            $error_msg = D0010::update_record($conditions, D0010::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

        } else {
            throw new Exception(\Config::get('m_CE0001'), 1);
        }

        return null;

    }

    // 削除処理
    private function delete_record($conditions) {

        Config::load('message');
        $error_msg = null;

        if (empty($conditions['dispatch_number'])) {
            return Config::get('m_DW0007');
        }

        // レコード存在チェック
        if (!$result = D0010::getDispatchCharter($conditions['dispatch_number'], D0010::$db)) {
            return Config::get('m_DW0001');
        }

        $error_msg = D0010::delete_record($conditions, D0010::$db);
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
        $conditions         = D0010::getForms('dispatch');
        $carrying           = D0010::getForms('carrying');
        $select_record      = Input::param('select_record', '');
        $select_carrying    = Input::param('select_carrying', '');
        $carrying_url       = Input::param('carrying_url', '');
        $list_no            = Input::param('list_no', '0');
        $dispatch_number    = Input::param('dispatch_number', '');
        $select_cancel      = Session::get('select_cancel');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d0012_list');
            Session::delete('carrying_charter');
        } elseif (!empty(Input::param('processing_division_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d0012_list');
            $conditions['processing_division'] = Input::param('processing_division');

        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = D0010::setForms('dispatch', $conditions, Input::param());

            // 空チェック
            foreach ($conditions['list'] as $l_no => $val) {
                if ($l_no == 0) {
                    $error_column = '';
                    if (empty($val['stack_date'])) {
                        $error_column = '積日';
                    } elseif (empty($val['drop_date'])) {
                        $error_column = '降日';
//                    } elseif (empty($val['stack_place'])) {
//                        $error_column = '積地';
//                    } elseif (empty($val['drop_place'])) {
//                        $error_column = '降地';
                    } elseif (empty($val['client_code'])) {
                        $error_column = '得意先No';
                    } elseif (empty($val['car_code'])) {
                        $error_column = '車番';
                    } elseif (empty($val['driver_name'])) {
                        $error_column = '運転手';
                    }
                    if (!empty($error_column)) {
                        $error_msg = str_replace('XXXXX', $error_column, Config::get('m_CW0005'));
                    }
                }
            }
            if (empty($error_msg)) {
                // 入力値チェック
                $validation = $this->validate_info($conditions);
                $errors     = $validation->error();
                $error_column = '';
                // 入力値チェックのエラー判定
                if (!empty($errors)) {
                    foreach($validation->error() as $key => $e) {
                        if (preg_match('/stack_date/', $key)) {
                            $error_column = '積日';
                        } elseif (preg_match('/drop_date/', $key)) {
                            $error_column = '降日';
//                        } elseif (preg_match('/stack_place/', $key)) {
//                            $error_column = '積地';
//                        } elseif (preg_match('/drop_place/', $key)) {
//                            $error_column = '降地';
                        } elseif (preg_match('/client_code/', $key)) {
                            $error_column = '得意先No';
                        } elseif (preg_match('/carrier_code/', $key)) {
                            $error_column = '庸車先No';
                        } elseif (preg_match('/product_name/', $key)) {
                            $error_column = '商品';
                        } elseif (preg_match('/car_model_code/', $key)) {
                            $error_column = '車種';
                        } elseif (preg_match('/car_code/', $key)) {
                            $error_column = '車番';
                        } elseif (preg_match('/driver_name/', $key)) {
                            $error_column = '運転手';
                        } elseif (preg_match('/delivery_category/', $key)) {
                            $error_column = '配送区分';
                        } elseif (preg_match('/tax_category/', $key)) {
                            $error_column = '税区分';
                        } elseif (preg_match('/claim_sales/', $key)) {
                            $error_column = '請求売上';
                        } elseif (preg_match('/carrier_payment/', $key)) {
                            $error_column = '庸車支払';
                        } elseif (preg_match('/claim_highway_fee/', $key)) {
                            $error_column = '請求高速料金';
                        } elseif (preg_match('/carrier_highway_fee/', $key)) {
                            $error_column = '庸車高速料金';
                        } elseif (preg_match('/driver_highway_fee/', $key)) {
                            $error_column = 'ドライバー高速料金';
                        } elseif (preg_match('/allowance/', $key)) {
                            $error_column = '手当';
                        } elseif (preg_match('/overtime_fee/', $key)) {
                            $error_column = '時間外';
                        } elseif (preg_match('/stay/', $key)) {
                            $error_column = '泊まり';
                        } elseif (preg_match('/linking_wrap/', $key)) {
                            $error_column = '連結・ラップ';
                        } elseif (preg_match('/receipt_send_date/', $key)) {
                            $error_column = '受領書送付日';
                        } elseif (preg_match('/receipt_receive_date/', $key)) {
                            $error_column = '受領書受領日';
                        }
                        if ($validation->error()[$key]->rule == 'required') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                        } elseif ($validation->error()[$key]->rule == 'valid_strings' ||
                                                                $validation->error()[$key]->rule == 'is_numeric' ||
                                                                $validation->error()[$key]->rule == 'trim_max_lengths') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                        } else {
                            // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                        }
                        break;
                    }
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(D0010::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 下記項目が全て未入力の場合はスルー
                        if (empty($val['stack_place']) && empty($val['drop_place']) && empty($val['client_code']) && empty($val['carrier_code']) && empty($val['driver_name']) && empty($val['phone_number']) && empty($val['car_code'])) {
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
                                $val['dispatch_number'] = $conditions['dispatch_number'];
                                $error_msg = $this->update_record($val);
                                break;
                            case '3':
                                // 削除処理
                                $val['dispatch_number'] = $conditions['dispatch_number'];
                                $error_msg = $this->delete_record($val);
                                break;
                        }
                    }
                    if (empty($error_msg)) {
                        DB::commit_transaction(D0010::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0009')."');</script>";
                                break;
                            case '2':
                                // 更新処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0010')."');</script>";
                                break;
                            case '3':
                                // 削除処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0011')."');</script>";
                                break;
                        }
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = D0010::getForms();
                    $redirect_flag = true;
                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(D0010::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d0012_list');
            Session::set('d0012_list', $conditions);
        } else {
            if ($cond = Session::get('d0012_list', array())) {
                $conditions = $cond;
                Session::delete('d0012_list');
            }
            if (!empty($dispatch_number)) {
                Session::set('select_dispatch_code', $dispatch_number);
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = D0010::setForms('dispatch', $conditions, Input::param());
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);
            }
            if (!empty($select_cancel)) {
                Session::set('d0012_list', $conditions);
            }
            Session::delete('select_client_code');
            Session::delete('select_product_code');
            Session::delete('select_carrier_code');
            Session::delete('select_car_code');
            Session::delete('select_member_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('d0012_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('allocation/d0011')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'carrying_url'              => \Uri::create(AccessControl::getActiveController().'/carrying'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,
                'carrying'                  => $carrying,

                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'position_list'             => $this->position_list,
                'product_list'              => $this->product_list,
                'car_model_list'            => $this->car_model_list,
                'delivery_category_list'    => $this->delivery_category_list,
                'tax_category_list'         => $this->tax_category_list,
                'company_section_list'      => $this->company_section_list,
                'closing_date_list'         => $this->closing_date_list,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                // 分載得意先検索リストデータ
                'client_list'               => D0010::getMasterList('client', D0010::$db),
                // 分載傭車先検索リストデータ
                'carrier_list'              => D0010::getMasterList('carrier', D0010::$db),
                // 分載車両検索リストデータ
                'car_list'                  => D0010::getMasterList('car', D0010::$db),
                // 分載ドライバー検索リストデータ
                'driver_list'               => D0010::getMasterList('driver', D0010::$db),

                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

    // 画面キー操作でのデータ取得
    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = D0010::getNameById($type, $code, D0010::$db);

        return $this->response($data);
    }

    public function action_master() {

        $data               = array();
        $type               = Input::param('type', '');
        $carrying_line_no   = Input::param('carrying_line_no', '');

        $data = D0010::getMasterList($type, D0010::$db);
        $data['carrying_line_no'] = $carrying_line_no;

        return $this->response($data);
    }

}
