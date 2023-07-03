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
use \Model\Allocation\D1011;

class Controller_Allocation_D1012 extends Controller_Hybrid {

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
        $cnf['header_title']                = '配車入力（共配便）';
        $cnf['page_id']                     = '[D1012]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '配車入力（共配便）';
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
            'allocation/d1011.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'allocation/d1011.js',
            'allocation/d1011_form.js',
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
        $this->division_list            = GenerateList::getDivisionList(false, D1011::$db);
        // 役職リスト取得
        $this->position_list            = GenerateList::getPositionList(false, D1011::$db);
        // 商品リスト取得
        $this->product_list             = GenerateList::getProductList(false, D1011::$db);
        // 車種リスト
        $this->car_model_list           = GenerateList::getCarModelList(false, D1011::$db);
        // 配送区分リスト取得
        $this->delivery_list            = GenerateList::getShareDeliveryCategoryList(false);
        // 配車区分リスト取得
        $this->dispatch_list            = GenerateList::getDispatchCategoryList(false);
        // 地区リスト取得
        $this->area_list                = GenerateList::getAreaList(false, D1011::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(false, D1011::$db);
        // 登録者リスト取得
        $this->create_user_list         = GenerateList::getCreateUserList(false, D1011::$db);
        // 売上ステータスリスト取得
        $this->sales_status_list        = GenerateList::getSalesStatusList(false);
        // 会社区分リスト取得
        $this->company_section_list     = GenerateList::getCompanySectionList(true);
        // 締日リスト取得
        $this->closing_date_list        = GenerateList::getClosingDateList(true);

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
            if ($result = D1011::getSearchClient($code, D1011::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D1011::getSearchCarrier($code, D1011::$db)) {
                $conditions['list'][$list_no]['carrier_code'] = $result[0]['carrier_code'];
                $conditions['list'][$list_no]['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            if ($result = D1011::getSearchProduct($code, D1011::$db)) {
                $conditions['list'][$list_no]['product_code'] = $result[0]['product_code'];
                $conditions['list'][$list_no]['product_name'] = $result[0]['product_name'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D1011::getSearchCar($code, D1011::$db)) {
                $conditions['list'][$list_no]['car_code']   = $result[0]['car_code'];
                $conditions['list'][$list_no]['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D1011::getMember($code, D1011::$db)) {
                $conditions['list'][$list_no]['member_code']    = $result[0]['member_code'];
                $conditions['list'][$list_no]['driver_name']    = $result[0]['driver_name'];
                $conditions['list'][$list_no]['phone_number']   = $result[0]['phone_number'];
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
                if ($result = D1011::getDispatchShare($dispatch_code_list[$i], D1011::$db)) {
                    $list_no = $i;
                    $conditions['dispatch_number']                          = $result['dispatch_number'];
                    $conditions['division_code']                            = $result['division_code'];
                    $conditions['list'][$list_no]['delivery_code']          = $result['delivery_code'];
                    $conditions['list'][$list_no]['dispatch_code']          = $result['dispatch_code'];
                    $conditions['list'][$list_no]['area_code']              = $result['area_code'];
                    $conditions['list'][$list_no]['course']                 = $result['course'];
                    $conditions['list'][$list_no]['delivery_date']          = $result['delivery_date'];
                    $conditions['list'][$list_no]['pickup_date']            = $result['pickup_date'];
                    $conditions['list'][$list_no]['delivery_place']         = $result['delivery_place'];
                    $conditions['list'][$list_no]['pickup_place']           = $result['pickup_place'];
                    $conditions['list'][$list_no]['client_code']            = $result['client_code'];
                    $conditions['list'][$list_no]['client_name']            = $result['client_name'];
                    $conditions['list'][$list_no]['carrier_code']           = $result['carrier_code'];
                    $conditions['list'][$list_no]['carrier_name']           = $result['carrier_name'];
                    $conditions['list'][$list_no]['product_name']           = $result['product_name'];
                    $conditions['list'][$list_no]['maker_name']             = $result['maker_name'];
                    $conditions['list'][$list_no]['volume']                 = $result['volume'];
                    $conditions['list'][$list_no]['unit_code']              = $result['unit_code'];
                    $conditions['list'][$list_no]['car_model_code']         = $result['car_model_code'];
                    $conditions['list'][$list_no]['car_code']               = $result['car_code'];
                    $conditions['list'][$list_no]['member_code']            = $result['member_code'];
                    $conditions['list'][$list_no]['driver_name']            = $result['driver_name'];
                    $conditions['list'][$list_no]['remarks1']               = $result['remarks1'];
                    $conditions['list'][$list_no]['remarks2']               = $result['remarks2'];
                    $conditions['list'][$list_no]['remarks3']               = $result['remarks3'];
                    $conditions['list'][$list_no]['requester']              = $result['requester'];
                    $conditions['list'][$list_no]['inquiry_no']             = $result['inquiry_no'];
                    $conditions['list'][$list_no]['onsite_flag']            = $result['onsite_flag'];
                    $conditions['list'][$list_no]['delivery_address']       = $result['delivery_address'];
                    $conditions['list'][$list_no]['carrier_payment']        = (!empty($result['carrier_payment'])) ? $result['carrier_payment']:0;
                    $conditions['list'][$list_no]['sales_status']           = $result['sales_status'];
                } else {
                    $error_msg = Config::get('m_DW0001');
                }
            }
            Session::delete('select_dispatch_code');
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
            // 指定項目が全て未入力の場合はスルー
            if (!D1011::chkDispatchShareDataNull($val)) {
                continue;
            }
            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');

            // 配送区分チェック
            $validation->add('list['.$key.'][delivery_code]', '配送区分')
                ->add_rule('required_select');
            // 地区チェック
            $validation->add('list['.$key.'][area_code]', '地区')
                ->add_rule('required_select');
            // コースチェック
            $validation->add('list['.$key.'][course]', 'コース')
                ->add_rule('trim_max_lengths', 5);
            // 納品日チェック
            $validation->add('list['.$key.'][delivery_date]', '納品日')
                ->add_rule('delivery_and_pickup_required_date', $val['pickup_date'])
                ->add_rule('valid_date_format');
            // 引取日チェック
            $validation->add('list['.$key.'][pickup_date]', '引取日')
                ->add_rule('delivery_and_pickup_required_date', $val['delivery_date'])
                ->add_rule('valid_date_format');
            // 納品先チェック
            $validation->add('list['.$key.'][delivery_place]', '納品先')
                ->add_rule('trim_max_lengths', 30);
            // 引取先チェック
            $validation->add('list['.$key.'][pickup_place]', '引取先')
                ->add_rule('trim_max_lengths', 30);
            // 得意先Noチェック
            $validation->add('list['.$key.'][client_code]', '得意先No')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
            // 庸車先Noチェック
            $validation->add('list['.$key.'][carrier_code]', '傭車先No')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
            // 数量チェック
            $validation->add('list['.$key.'][volume]', '数量')
                ->add_rule('required')
                ->add_rule('is_numeric_decimal', 6);
            // 単位チェック
            $validation->add('list['.$key.'][unit_code]', '単位')
                ->add_rule('required_select');
            // 庸車費用チェック
            $validation->add('list['.$key.'][carrier_payment]', '庸車費用')
                ->add_rule('trim_max_lengths', 8)
                ->add_rule('valid_strings', array('numeric', 'commas'));
            // 商品名チェック
            $validation->add('list['.$key.'][product_name]', '商品名')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 30);
            // 車種チェック
            $validation->add('list['.$key.'][car_model_code]', '車種')
                ->add_rule('required');
            // メーカーチェック
            $validation->add('list['.$key.'][maker_name]', 'メーカー')
                ->add_rule('trim_max_lengths', 15);
            // 車両番号チェック
            $validation->add('list['.$key.'][car_code]', '車両番号')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 4)
                ->add_rule('is_numeric');
            // 依頼者チェック
            $validation->add('list['.$key.'][requester]', '依頼者')
                ->add_rule('trim_max_lengths', 15);
            // 問い合わせNoチェック
            $validation->add('list['.$key.'][inquiry_no]', '問い合わせNo')
                ->add_rule('trim_max_lengths', 15);
            // 納品先住所チェック
            $validation->add('list['.$key.'][delivery_address]', '納品先住所')
                ->add_rule('trim_max_lengths', 40);
            // 運転手チェック
            $validation->add('list['.$key.'][driver_name]', '運転手')
                ->add_rule('required');
            // 備考1チェック
            $validation->add('list['.$key.'][remarks1]', '備考1')
                ->add_rule('trim_max_lengths', 15);
            $validation->run();
            // 備考2チェック
            $validation->add('list['.$key.'][remarks2]', '備考2')
                ->add_rule('trim_max_lengths', 15);
            $validation->run();
            // 備考3チェック
            $validation->add('list['.$key.'][remarks3]', '備考3')
                ->add_rule('trim_max_lengths', 15);
            $validation->run();

        }
        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg                  = null;

        // 傭車先コード取得
        if (empty($conditions['carrier_code'])) {
            $conditions['carrier_code'] = D1011::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D1011::$db);
        }
        if (empty($conditions['carrier_code'])) {
            return str_replace('XXXXX','庸車No',Config::get('m_CW0005'));
        }
        // // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
        // if (D1011::OurCompanyCheck($conditions['carrier_code'], D1011::$db)) {
        //     // 車両コードが車両マスタに登録されているかチェック
        //     if (!D1011::getNameById('car', $conditions['car_code'], D1011::$db)) {
        //         return Config::get('m_DW0021');
        //     }
        // }

        // レコード登録
        $error_msg = D1011::create_record($conditions, D1011::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        Config::load('message');
        $error_msg                  = null;

        // 傭車先コード取得
        if (empty($conditions['carrier_code'])) {
            $conditions['carrier_code'] = D0010::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0010::$db);
        }
        if (empty($conditions['carrier_code'])) {
            return str_replace('XXXXX','庸車No',Config::get('m_CW0005'));
        }
        // // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
        // if (D0010::OurCompanyCheck($conditions['carrier_code'], D0010::$db)) {
        //     // 車両コードが車両マスタに登録されているかチェック
        //     if (!D0010::getNameById('car', $conditions['car_code'], D0010::$db)) {
        //         return str_replace('XXXXX','車両コード',Config::get('m_CW0005'));
        //     }
        // }

        // レコード存在チェック
        if (!$result = D1011::getDispatchShare($conditions['dispatch_number'], D1011::$db)) {
            return Config::get('m_DW0001');
        }

        // レコード更新
        $error_msg = D1011::update_record($conditions, D1011::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
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
        if (!$result = D1011::getDispatchShare($conditions['dispatch_number'], D1011::$db)) {
            return Config::get('m_DW0001');
        }

        $error_msg = D1011::delete_record($conditions, D1011::$db);
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
        $conditions         = D1011::getForms('dispatch');
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $dispatch_number    = Input::param('dispatch_number', '');
        $select_cancel      = Session::get('select_cancel');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d1012_list');
        } elseif (!empty(Input::param('processing_division_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d1012_list');
            $conditions['processing_division'] = Input::param('processing_division');

        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = D1011::setForms('dispatch', $conditions, Input::param());

            if ($conditions['processing_division'] == 2) {
                // 更新の時のみ
                // 入力値チェック
                if ($validation = $this->validate_info($conditions)){
                    $errors     = $validation->error();
                    $error_column = '';
                    // 入力値チェックのエラー判定
                    foreach($validation->error() as $key => $e) {
                        if (preg_match('/delivery_code/', $key)) {
                            $error_column = '配送区分';
                        } elseif (preg_match('/area_code/', $key)) {
                            $error_column = '地区';
                        } elseif (preg_match('/course/', $key)) {
                           $error_column = 'コース';
                        } elseif (preg_match('/delivery_date/', $key)) {
                           $error_column = '納品日';
                        } elseif (preg_match('/pickup_date/', $key)) {
                            $error_column = '引取日';
                        } elseif (preg_match('/delivery_place/', $key)) {
                           $error_column = '納品先';
                        } elseif (preg_match('/pickup_place/', $key)) {
                            $error_column = '引取先';
                        } elseif (preg_match('/client_code/', $key)) {
                            $error_column = '得意先No';
                        } elseif (preg_match('/carrier_code/', $key)) {
                            $error_column = '庸車先No';
                        } elseif (preg_match('/volume/', $key)) {
                            $error_column = '数量';
                        } elseif (preg_match('/unit_code/', $key)) {
                            $error_column = '単位';
                        } elseif (preg_match('/carrier_payment/', $key)) {
                            $error_column = '庸車費用';
                        } elseif (preg_match('/product_name/', $key)) {
                            $error_column = '商品名';
                        } elseif (preg_match('/car_model_code/', $key)) {
                            $error_column = '車種';
                        } elseif (preg_match('/maker_name/', $key)) {
                            $error_column = 'メーカー';
                        } elseif (preg_match('/car_code/', $key)) {
                            $error_column = '車両番号';
                        } elseif (preg_match('/requester/', $key)) {
                            $error_column = '依頼者';
                        } elseif (preg_match('/inquiry_no/', $key)) {
                            $error_column = '問い合わせNo';
                        } elseif (preg_match('/delivery_address/', $key)) {
                            $error_column = '納品先住所';
                        } elseif (preg_match('/driver_name/', $key)) {
                            $error_column = '運転手';
                        } elseif (preg_match('/remarks1/', $key)) {
                            $error_column = '備考1';
                        } elseif (preg_match('/remarks2/', $key)) {
                            $error_column = '備考2';
                        } elseif (preg_match('/remarks3/', $key)) {
                            $error_column = '備考3';
                        }
                        
                        if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                        } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                        } elseif ($validation->error()[$key]->rule == 'is_numeric') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                        } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                        } elseif ($validation->error()[$key]->rule == 'delivery_and_pickup_required_date') {
                            $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0027'));
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
                    DB::start_transaction(D1011::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 指定項目が全て未入力の場合はスルー
                        if (!D1011::chkDispatchShareDataNull($val)) {
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
                        DB::commit_transaction(D1011::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0021')."');</script>";
                                break;
                            case '2':
                                // 更新処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0022')."');</script>";
                                break;
                            case '3':
                                // 削除処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0023')."');</script>";
                                break;
                        }
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = D1011::getForms();
                    Session::delete('d1012_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(D1011::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d1012_list');
            Session::set('d1012_list', $conditions);
        } else {
            $conditions = D1011::setForms('dispatch', $conditions, Input::param());
            if ($cond = Session::get('d1012_list', array())) {
                $conditions = $cond;
                Session::delete('d1012_list');
            }

            if (!empty($dispatch_number)) {
                Session::set('select_dispatch_code', $dispatch_number);
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                $conditions = D1011::setForms('dispatch', $conditions, Input::param());
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
            // Session::set('d1012_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('allocation/d1010')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,

                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'position_list'             => $this->position_list,
                'division_list'             => $this->division_list,
                'sales_status_list'         => $this->sales_status_list,
                'product_list'              => $this->product_list,
                'car_model_list'            => $this->car_model_list,
                'delivery_list'             => $this->delivery_list,
                'dispatch_list'             => $this->dispatch_list,
                'area_list'                 => $this->area_list,
                'unit_list'                 => $this->unit_list,
                'create_user_list'          => $this->create_user_list,
                'user_authority'            => $this->user_authority,
                'company_section_list'      => $this->company_section_list,
                'closing_date_list'         => $this->closing_date_list,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                // 分載得意先検索リストデータ
                'client_list'               => D1011::getMasterList('client', D1011::$db),
                // 分載傭車先検索リストデータ
                'carrier_list'              => D1011::getMasterList('carrier', D1011::$db),
                // 分載車両検索リストデータ
                'car_list'                  => D1011::getMasterList('car', D1011::$db),
                // 分載ドライバー検索リストデータ
                'driver_list'               => D1011::getMasterList('driver', D1011::$db),

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

        $data = D1011::getNameById($type, $code, D1011::$db);

        return $this->response($data);
    }

    public function action_master() {

        $data               = array();
        $type               = Input::param('type', '');
        $carrying_line_no   = Input::param('carrying_line_no', '');

        $data = D1011::getMasterList($type, D1011::$db);
        $data['carrying_line_no'] = $carrying_line_no;

        return $this->response($data);
    }

}
