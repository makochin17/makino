<?php
/**
 * 売上補正入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Allocation\D0030;

class Controller_Allocation_D0030 extends Controller_Hybrid {

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
        if (Request::main()->action == 'detail') {
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
        $cnf['header_title']                = '月極その他情報入力';
        $cnf['page_id']                     = '[D0030]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '月極その他情報入力';
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
            // 'modal/dialog.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'allocation/d0030.js'
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
        $this->division_list            = GenerateList::getDivisionList(false, D0030::$db);
        // 商品リスト取得
        $this->product_list             = GenerateList::getProductList(false, D0030::$db);
        // 車種リスト
        $this->carmodel_list            = GenerateList::getCarModelList(false, D0030::$db);
        // 配車区分
        $this->delivery_category_list   = GenerateList::getDeliveryCategoryList(false, D0030::$db);
        // 税区分
        $this->tax_category_list        = GenerateList::getTaxCategoryList(false, D0030::$db);
        // 売上区分リスト取得
        $this->sales_category_list      = GenerateList::getSalesCategoryList(false, D0030::$db);

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
    private function set_info(&$conditions, $list_no) {
        $error_msg = null;

        if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = D0030::getSearchClient($code, D0030::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        } elseif ($code = Session::get('select_carrier_code')) {
            // 庸車先の検索にてレコード選択された場合
            if ($result = D0030::getSearchCarrier($code, D0030::$db)) {
                $conditions['list'][$list_no]['carrier_code'] = $result[0]['carrier_code'];
                $conditions['list'][$list_no]['carrier_name'] = $result[0]['carrier_name'];
            } else {
                $error_msg = Config::get('m_DW0004');
            }
            Session::delete('select_carrier_code');
        } elseif ($code = Session::get('select_product_code')) {
            // 商品の検索にてレコード選択された場合
            if ($result = D0030::getSearchProduct($code, D0030::$db)) {
                $conditions['list'][$list_no]['product_code'] = $result[0]['product_code'];
                $conditions['list'][$list_no]['product_name'] = $result[0]['product_name'];
            } else {
                $error_msg = Config::get('m_DW0003');
            }
            Session::delete('select_product_code');
        } elseif ($code = Session::get('select_car_code')) {
            // 車両の検索にてレコード選択された場合
            if ($result = D0030::getSearchCar($code, D0030::$db)) {
                $conditions['list'][$list_no]['car_code']   = $result[0]['car_code'];
                $conditions['list'][$list_no]['car_number'] = $result[0]['car_number'];
            } else {
                $error_msg = Config::get('m_DW0005');
            }
            Session::delete('select_car_code');
        } elseif ($code = Session::get('select_member_code')) {
            // 社員の検索にてレコード選択された場合
            if ($result = D0030::getSearchMember($code, D0030::$db)) {
                $conditions['list'][$list_no]['driver_name']    = $result[0]['driver_name'];
                $conditions['list'][$list_no]['phone_number']   = $result[0]['phone_number'];
                $conditions['list'][$list_no]['member_code']    = $result[0]['member_code'];
            } else {
                $error_msg = Config::get('m_DW0006');
            }
            Session::delete('select_member_code');
        } elseif ($code_list = Session::get('select_sales_correction_number')) {
            // 月極その他情報引用の検索にてレコード選択された場合
            $sales_correction_code_list = explode(",", $code_list);
            $sales_correction_code_count = 0;
            if (is_countable($sales_correction_code_list)){
                $sales_correction_code_count = count($sales_correction_code_list);
            }
            for($i = 0; $i < $sales_correction_code_count; $i++){
                if ($result = D0030::getSalesCorrection($sales_correction_code_list[$i], D0030::$db)) {
                    $list_no = $i;
                    //$conditions['division_code']                                = $result['division_code'];
                    $conditions['list'][$list_no]['sales_correction_number']    = $result['sales_correction_number'];
                    $conditions['list'][$list_no]['sales_status']               = $result['sales_status'];
                    $conditions['list'][$list_no]['member_code']                = $result['member_code'];
                    $conditions['list'][$list_no]['sales_category_code']        = $result['sales_category_code'];
                    $conditions['list'][$list_no]['sales_category_value']       = $result['sales_category_value'];
                    $conditions['list'][$list_no]['client_code']                = $result['client_code'];
                    $conditions['list'][$list_no]['client_name']                = $result['client_name'];
                    $conditions['list'][$list_no]['car_model_code']             = $result['car_model_code'];
                    $conditions['list'][$list_no]['car_model_name']             = $result['car_model_name'];
                    $conditions['list'][$list_no]['car_code']                   = $result['car_code'];
                    $conditions['list'][$list_no]['carrier_code']               = $result['carrier_code'];
                    $conditions['list'][$list_no]['carrier_name']               = $result['carrier_name'];
                    $conditions['list'][$list_no]['member_code']                = $result['member_code'];
                    $conditions['list'][$list_no]['driver_name']                = $result['driver_name'];
                    $conditions['list'][$list_no]['sales_date']                 = $result['sales_date'];
                    $conditions['list'][$list_no]['operation_count']            = $result['operation_count'];
                    $conditions['list'][$list_no]['delivery_category']          = $result['delivery_category'];
                    $conditions['list'][$list_no]['sales']                      = (!empty($result['sales'])) ? $result['sales']:'0';
                    $conditions['list'][$list_no]['carrier_cost']               = (!empty($result['carrier_cost'])) ? $result['carrier_cost']:'0';
                    $conditions['list'][$list_no]['highway_fee']                = (!empty($result['highway_fee'])) ? $result['highway_fee']:'0';
                    $conditions['list'][$list_no]['highway_fee_claim']          = (!empty($result['highway_fee_claim'])) ? $result['highway_fee_claim']:'1';
                    $conditions['list'][$list_no]['overtime_fee']               = (!empty($result['overtime_fee'])) ? $result['overtime_fee']:'0';
                    $conditions['list'][$list_no]['remarks']                    = $result['remarks'];
                } else {
                    $error_msg = Config::get('m_DW0006');
                }
            }
        }
        Session::delete('select_code');

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
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
            // 庸車Noチェック
            $validation->add('list['.$key.'][carrier_code]', '傭車先No')
                ->add_rule('required')
                ->add_rule('trim_max_lengths', 5)
                ->add_rule('is_numeric');
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
            if (!empty($val['overtime_fee'])) {
                // 時間外チェック
                $validation->add('list['.$key.'][overtime_fee]', '時間外')
                    ->add_rule('trim_max_lengths', 8)
                    ->add_rule('is_numeric');
            }
            
            // 車番チェック
            $validation->add('list['.$key.'][car_code]', '車番')
                ->add_rule('trim_max_lengths', 4)
                ->add_rule('is_numeric');

            if ($val['sales_category_code'] == '03' || $val['sales_category_code'] == '99') {
                // 売上区分（保管料、その他）
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
                // 運転手チェック
                $validation->add('list['.$key.'][driver_name]', '運転手')
//                    ->add_rule('required')
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
            $conditions['carrier_code'] = D0030::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0030::$db);
        }

        $error_msg = D0030::create_record($conditions, D0030::$db);
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
            $conditions['carrier_code'] = D0030::getCarrierCode($conditions['member_code'], $conditions['driver_name'], D0030::$db);
        }

        $error_msg = D0030::update_record($conditions, D0030::$db);
        if (!is_null($error_msg)) {
            return $error_msg;
        }

        return null;
    }

    // 削除処理
    private function delete_record($conditions) {

        Config::load('message');
        $error_msg = null;

        $error_msg = D0030::delete_record($conditions, D0030::$db);
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
        $conditions         = D0030::getForms();
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $select_cancel      = Session::get('select_cancel');

        if (Input::method() == 'POST' && Security::check_token()) {
            if (!empty(Input::param('input_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('d0030_list');
            } elseif (!empty(Input::param('processing_division_clear'))) {
                // 入力項目クリアボタンが押下された場合の処理
                Session::delete('d0030_list');
                $conditions['processing_division'] = Input::param('processing_division');
            } elseif (!empty(Input::param('execution'))) {
                // 確定ボタンが押下された場合の処理
                $conditions = D0030::setForms($conditions, Input::param());

                // 空チェック
                foreach ($conditions['list'] as $l_no => $val) {
                    if ($l_no == 0) {
                        $error_column = '';
                        if (empty($val['sales_date'])) {
                            $error_column = '日付';
                        } elseif (empty($val['client_code'])) {
                            $error_column = '得意先No';
                        } elseif (empty($val['carrier_code'])) {
                            $error_column = '傭車先No';
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
                    // 入力値チェックのエラー判定
                    if (!empty($errors)) {
                        foreach($validation->error() as $key => $e) {
                            if (preg_match('/sales_date/', $key)) {
                                $error_column = '日付';
                            } elseif (preg_match('/sales_category_code/', $key)) {
                                $error_column = '売上区分';
                            } elseif (preg_match('/client_code/', $key)) {
                                $error_column = '得意先No';
                            } elseif (preg_match('/operation_count/', $key)) {
                                $error_column = '稼働台数';
                            } elseif (preg_match('/sales/', $key)) {
                                $error_column = '売上';
                            } elseif (preg_match('/delivery_category/', $key)) {
                                $error_column = '配送区分';
                            } elseif (preg_match('/highway_fee_claim/', $key)) {
                                $error_column = '高速料金';
                            } elseif (preg_match('/overtime_fee/', $key)) {
                                $error_column = '時間外';
                            } elseif (preg_match('/car_model_code/', $key)) {
                                $error_column = '車種';
                            } elseif (preg_match('/car_code/', $key)) {
                                $error_column = '車番';
                            } elseif (preg_match('/carrier_code/', $key)) {
                                $error_column = '傭車先No';
                            } elseif (preg_match('/driver_name/', $key)) {
                                $error_column = '運転手';
                            } elseif (preg_match('/carrier_cost/', $key)) {
                                $error_column = '庸車費';
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
                        DB::start_transaction(D0030::$db);

                        foreach ($conditions['list'] as $key => $val) {
                            // ２レコード目以降で処理区分が更新または削除の場合はスルー
                            if ($key >0 && $conditions['processing_division'] != 1) {
                                continue;
                            }
                            // ２レコード目以降で日付、売上区分、得意先No、売上が未入力の場合はスルー
                            if ($key > 0 && empty($val['sales_date']) && empty($val['client_code']) && empty($val['sales'])) {
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
                                    $error_msg = $this->update_record($val);
                                    break;
                                case '3':
                                    // 削除処理
                                    $error_msg = $this->delete_record($val);
                                    break;
                            }
                        }

                        DB::commit_transaction(D0030::$db);
                        if (empty($error_msg)) {
                            switch ($conditions['processing_division']){
                                case '1':
                                    // 登録処理
                                    echo "<script type='text/javascript'>alert('".Config::get('m_DI0012')."');</script>";
                                    break;
                                case '2':
                                    // 更新処理
                                    echo "<script type='text/javascript'>alert('".Config::get('m_DI0013')."');</script>";
                                    break;
                                case '3':
                                    // 削除処理
                                    echo "<script type='text/javascript'>alert('".Config::get('m_DI0014')."');</script>";
                                    break;
                            }
                        }
                        // 成功したらフォーム情報を初期化
                        $conditions = D0030::getForms();
                        Session::delete('d0030_list');
                        $redirect_flag = true;
                    } catch (Exception $e) {
                        // トランザクションクエリをロールバックする
                        DB::rollback_transaction(D0030::$db);
                        Log::error($e->getMessage());
                        $error_msg = Config::get('m_CE0001');
                        $error_msg = $e->getMessage();
                    }
                }

                /**
                 * セッションに検索条件を設定
                 */
                Session::delete('d0030_list');
                Session::set('d0030_list', $conditions);
            }
        } else {
            $conditions = D0030::setForms($conditions, Input::param());
            if ($cond = Session::get('d0030_list', array())) {
                $conditions = $cond;
                Session::delete('d0030_list');
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);

            }
            Session::delete('select_client_code');
            Session::delete('select_product_code');
            Session::delete('select_carrier_code');
            Session::delete('select_car_code');
            Session::delete('select_member_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('d0030_list', $conditions);

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('allocation/d0031')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),

                'data'                      => $conditions,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),

                'processing_division_list'  => $this->processing_division_list,
                'division_list'             => $this->division_list,
                'product_list'              => $this->product_list,
                'carmodel_list'             => $this->carmodel_list,
                'delivery_category_list'    => $this->delivery_category_list,
                'tax_category_list'         => $this->tax_category_list,
                'sales_category_list'       => $this->sales_category_list,

                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

    public function action_detail() {

        $data = array();
        $type = Input::param('type', '');
        $code = Input::param('code', '');

        $data = D0030::getNameById($type, $code, D0030::$db);

        return $this->response($data);
    }

}
