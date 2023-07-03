<?php
/**
 * 庸車先情報入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030\M0034;
use \Model\Mainte\M0030\M0030;

class Controller_Mainte_M0034 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 課リスト
    private $division_list = array();
    // 締日リスト
    private $closing_category_list   = array();
    private $closing_date_list1   = array();
    private $closing_date_list2   = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '庸車先情報入力';
        $cnf['page_id']                     = '[M0034]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '庸車先情報入力';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

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

        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, M0034::$db);
        // 締日リスト
        $this->closing_category_list    = GenerateList::getClosingCategoryList();
        $this->closing_date_list1       = GenerateList::getClosingDateList2(false);
        $this->closing_date_list2       = GenerateList::getClosingDateList2(true);
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
        $validation->add('text_carrier_code', '庸車先コード')
            //->add_rule('required')
            ->add_rule('valid_strings', array('numeric'))
        ;
        // 会社名チェック
        $validation->add('carrier_company_name', '会社名')
            ->add_rule('required')
        ;
        // 締日チェック
        $validation->add('closing_category', '締日')
            ->add_rule('required')
        ;
        // 会社区分チェック
        $validation->add('company_section', '会社区分')
            ->add_rule('required')
        ;
        // 基準締日チェック
        $validation->add('criterion_closing_date', '基準締日')
            ->add_rule('required')
        ;
        // 正式名称チェック
        $validation->add('official_name', '正式名称')
            ->add_rule('required')
        ;
        // 正式名称（カナ）チェック
        $validation->add('official_name_kana', '正式名称（カナ）')
            ->add_rule('is_half_katakana')
        ;
        // 郵便番号チェック
        $validation->add('postal_code', '郵便番号')
            //->add_rule('required')
            ->add_rule('valid_strings', array('numeric', 'dashes'))
        ;
        // 住所チェック
        $validation->add('address', '住所１')
            //->add_rule('required')
        ;
        // 電話番号チェック
        $validation->add('phone_number', '電話番号')
            //->add_rule('required')
            ->add_rule('valid_strings', array('numeric', 'dashes'))
        ;
        // FAX番号チェック
        $validation->add('fax_number', 'FAX番号')
            //->add_rule('required')
            ->add_rule('valid_strings', array('numeric', 'dashes'))
        ;
        
		$validation->run();
		return $validation;
	}

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(M0034::$db);
            
            if ($conditions['carrier_radio'] == '1') {
                //コード値が自動採番ならコード値を取得
                $conditions['carrier_code'] = M0034::getCarrierCode(M0034::$db);
            }
            
            $closing_date = "";
            switch ($conditions['closing_category']){
                case "1": //月1回
                    $closing_date = $conditions['closing_date_1'];
                    break;
                case "2": //月2回
                    $closing_date = 51;
                    break;
                case "3": //月3回
                    $closing_date = 52;
                    break;
                case "4": //都度
                    $closing_date = 50;
                    break;
            }
            $conditions['closing_date'] = $closing_date;

            $error_msg = M0034::create_record($conditions, M0034::$db);
            if (!is_null($error_msg)) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(M0034::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0034::$db);
            
            $end_msg = Config::get('m_MI0005');
            echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0034::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        return null;
    }

//    // 更新処理
//    private function update_record($conditions) {
//
//        try {
//            DB::start_transaction(M0034::$db);
//
//            $error_msg = M0034::update_record($conditions, M0034::$db);
//            if (!is_null($error_msg)) {
//                return $error_msg;
//            }
//
//            DB::commit_transaction(M0034::$db);
//        } catch (Exception $e) {
//            // トランザクションクエリをロールバックする
//            DB::rollback_transaction(M0034::$db);
//            // return $e->getMessage();
//            return Config::get('m_CE0001');
//        }
//        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
//        return null;
//    }
//
//    // 削除処理
//    private function delete_record($conditions) {
//
//        try {
//            DB::start_transaction(M0034::$db);
//
//            $error_msg = M0034::delete_record($conditions, M0034::$db);
//            if (!is_null($error_msg)) {
//                return $error_msg;
//            }
//
//            DB::commit_transaction(M0034::$db);
//        } catch (Exception $e) {
//            // トランザクションクエリをロールバックする
//            DB::rollback_transaction(M0034::$db);
//            // return $e->getMessage();
//            return Config::get('m_CE0001');
//        }
//
//        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
//        return null;
//    }

    public function action_index() {

        Config::load('message');

        /**
         * 初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
        	'company_radio',
            'company_name',
        	'carrier_company_code',
            'l_carrier_company_name',
            'sales_office_radio',
            'sales_office_name',
        	'carrier_sales_office_code',
            'l_carrier_sales_office_name',
            'department_radio',
            'department_name',
            'carrier_code',
            'carrier_radio',
            'carrier_company_name',
            'carrier_sales_office_name',
            'carrier_department_name',
            'closing_category',
            'company_section',
            'criterion_closing_date',
            'closing_date_1',
            'closing_date_2',
            'closing_date_3',
            'official_name',
            'official_name_kana',
            'postal_code',
            'address',
            'address2',
            'phone_number',
            'fax_number',
            'person_in_charge_surname',
            'person_in_charge_name',
        ), '');
        
        $conditions_carrier = array_fill_keys(array(
            'carrier_code',
            'text_carrier_code',
            'carrier_radio',
        	'carrier_company_name',
            'carrier_sales_office_name',
            'carrier_department_name',
            'closing_category',
            'company_section',
            'criterion_closing_date',
            'closing_date_1',
            'closing_date_2',
            'closing_date_3',
            'official_name',
            'official_name_kana',
            'postal_code',
            'address',
            'address2',
            'phone_number',
            'fax_number',
            'person_in_charge_surname',
            'person_in_charge_name',
        ), '');
        
        //担当部署の項目セット
        $conditions = M0030::setDepartmentInChargeColumn($this->division_list, $conditions);
        $conditions_carrier = M0030::setDepartmentInChargeColumn($this->division_list, $conditions_carrier);

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('m0034_list');
            \Response::redirect(\Uri::create('mainte/m0031'));
        } elseif (!empty(Input::param('cancel')) && Input::method() == 'POST' && Security::check_token()) {
            // 「キャンセル」ボタン押下
            
            // 検索画面へリダイレクト
            Session::delete('m0034_list');
            \Response::redirect(\Uri::create('mainte/m0030'));
        } elseif (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 「戻る」ボタン押下
            
            if ($cond = Session::get('m0034_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_carrier as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            $conditions['carrier_code'] = $conditions['text_carrier_code'];
            
            //セッションに値を保持
            Session::set('m0034_list', $conditions);
            
            // 前画面へリダイレクト
            if ($conditions['sales_office_radio'] == 3 || empty($conditions['company_radio'])) {
                \Response::redirect(\Uri::create('mainte/m0031'));
            }
            if ($conditions['department_radio'] == 2) {
                \Response::redirect(\Uri::create('mainte/m0032'));
            }
            \Response::redirect(\Uri::create('mainte/m0033'));
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            if ($cond = Session::get('m0034_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_carrier as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            $conditions['carrier_code'] = $conditions['text_carrier_code'];
            
            // セッションに検索条件を設定
            Session::set('m0034_list', $conditions);
            
            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'text_carrier_code':
                            $error_column = '庸車先コード';
                            break;
                        case 'carrier_company_name':
                            $error_column = '会社名';
                            break;
                        case 'closing_date':
                            $error_column = '締日';
                            break;
                        case 'company_section':
                            $error_column = '会社区分';
                            break;
                        case 'criterion_closing_date':
                            $error_column = '基準締日';
                            break;
                        case 'official_name':
                            $error_column = '正式名称';
                            break;
                        case 'official_name_kana':
                            $error_column = '正式名称（カナ）';
                            break;
                        case 'postal_code':
                            $error_column = '郵便番号';
                            break;
                        case 'address':
                            $error_column = '住所１';
                            break;
                        case 'phone_number':
                            $error_column = '電話番号';
                            break;
                        case 'fax_number':
                            $error_column = 'FAX番号';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } elseif ($validation->error()[$key]->rule == 'is_half_katakana') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0017'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            
            //自動採番にチェックなしかつ庸車先コード未記入なら入力エラー
            if ($conditions['carrier_radio'] == '2' && empty($conditions['text_carrier_code'])) {
                $error_msg = str_replace('XXXXX','庸車先コード',Config::get('m_CW0005'));
            }
            
            //締日が月2回or月3回かつ、2回目日付が未記入なら入力エラー
            if (($conditions['closing_category'] == '2' || $conditions['closing_category'] == '3') && empty($conditions['closing_date_2'])) {
                $error_msg = str_replace('XXXXX','締日の2回目',Config::get('m_CW0005'));
            }
            //締日が月3回かつ、3回目日付が未記入なら入力エラー
            if ($conditions['closing_category'] == '3' && empty($conditions['closing_date_3'])) {
                $error_msg = str_replace('XXXXX','締日の3回目',Config::get('m_CW0005'));
            }
            
            //締日の1回目日付が2回目日付以上なら入力エラー
            if (!empty($conditions['closing_date_1']) && !empty($conditions['closing_date_2']) && $conditions['closing_date_1'] >= $conditions['closing_date_2']) {
                $error_msg = str_replace('XXXXX','締日の1回目と2回目',Config::get('m_CW0007'));
            }
            //締日の2回目日付が3回目日付以上なら入力エラー
            if (!empty($conditions['closing_date_2']) && !empty($conditions['closing_date_3']) && $conditions['closing_date_2'] >= $conditions['closing_date_3']) {
                $error_msg = str_replace('XXXXX','締日の2回目と3回目',Config::get('m_CW0007'));
            }

            if (empty($error_msg)) {
                // 登録処理
                $error_msg = $this->create_record($conditions);
            }
            
            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0034_list');
                \Response::redirect(\Uri::create('mainte/m0030'));
            }
        } else {
            if ($cond = Session::get('m0034_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                
                if ($conditions['company_radio'] == 1) {
                    $conditions['carrier_company_name'] = $conditions['company_name'];
                } else {
                    $conditions['carrier_company_name'] = $conditions['l_carrier_company_name'];
                }
                
                if ($conditions['sales_office_radio'] == 1) {
                    $conditions['carrier_sales_office_name'] = $conditions['sales_office_name'];
                } elseif ($conditions['sales_office_radio'] == 2) {
                    $conditions['carrier_sales_office_name'] = $conditions['l_carrier_sales_office_name'];
                } else {
                    $conditions['carrier_sales_office_name'] = null;
                }
                
                if ($conditions['department_radio'] == 1) {
                    $conditions['carrier_department_name'] = $conditions['department_name'];
                } else {
                    $conditions['carrier_department_name'] = null;
                }
            }
            
            Session::set('m0034_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'             => $error_msg,
                'data'                      => $conditions,
                'closing_category_list'     => $this->closing_category_list,
                'closing_date_list1'        => $this->closing_date_list1,
                'closing_date_list2'        => $this->closing_date_list2,
                'division_list'             => $this->division_list,
            )
        );
    }

}
