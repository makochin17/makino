<?php
/**
 * 得意先情報編集画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0025;
use \Model\Mainte\M0020\M0020;

class Controller_Mainte_M0025 extends Controller_Hybrid {

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
    // 課リスト（保管料用）
    private $division_list_storage = array();
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
        $cnf['header_title']                = '得意先情報入力';
        $cnf['page_id']                     = '[M0025]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '得意先情報入力';
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
        $this->division_list            = GenerateList::getDivisionList(false, M0025::$db);
        // 課リスト（保管料用）取得
        $this->division_list_storage    = GenerateList::getDivisionList(true, M0025::$db);
        $this->division_list_storage['000'] = '-';
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

    private function get_info($client_code) {
        
        $client_data = array();
        try {
            //得意先データ取得
            $result = M0025::getClient($client_code, M0025::$db);
            if (empty($result))return null;
            $client_data = $result[0];
            
        } catch (Exception $ex) {
            Log::error($e->getMessage());
            return null;
        }
        
        $closing_category = "";
        switch ($client_data['closing_date']){
            case "50": //都度
                $closing_category = 4;
                break;
            case "51": //月2回
                $closing_category = 2;
                break;
            case "52": //月3回
                $closing_category = 3;
                break;
            default: //月1回
                $closing_category = 1;
                $client_data['closing_date_1'] = $client_data['closing_date'];
                break;
        }
        
        $conditions = array(
        	'company_radio'				=> 1,
            'company_name'				=> $client_data['client_company_name'],
            'sales_office_radio'		=> 1,
            'sales_office_name'			=> $client_data['client_sales_office_name'],
            'department_radio'			=> 1,
            'department_name'			=> $client_data['client_department_name'],
            'client_code'				=> $client_data['client_code'],
            'client_company_code'		=> $client_data['client_company_code'],
            'client_company_name'		=> $client_data['client_company_name'],
            'client_sales_office_code'	=> $client_data['client_sales_office_code'],
            'client_sales_office_name'	=> $client_data['client_sales_office_name'],
            'client_department_code'	=> $client_data['client_department_code'],
            'client_department_name'	=> $client_data['client_department_name'],
            'closing_category'			=> $closing_category,
            'criterion_closing_date'	=> $client_data['criterion_closing_date'],
            'closing_date_1'			=> $client_data['closing_date_1'],
            'closing_date_2'			=> $client_data['closing_date_2'],
            'closing_date_3'			=> $client_data['closing_date_3'],
            'official_name'				=> $client_data['official_name'],
            'official_name_kana'		=> $client_data['official_name_kana'],
            'postal_code'				=> $client_data['postal_code'],
            'address'					=> $client_data['address'],
            'address2'					=> $client_data['address2'],
            'phone_number'				=> $client_data['phone_number'],
            'fax_number'				=> $client_data['fax_number'],
            'person_in_charge_surname'	=> $client_data['person_in_charge_surname'],
            'person_in_charge_name'		=> $client_data['person_in_charge_name'],
            'storage_fee'				=> $client_data['storage_fee'],
            'storage_in_charge'			=> $client_data['storage_in_charge'],
        );
        
        //担当部署の項目セット
        $conditions = M0020::setDepartmentInChargeColumn($this->division_list, $conditions, $client_data['department_in_charge']);
        
        return $conditions;
    }
    
	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 得意先コードチェック
        $validation->add('client_code', '得意先コード')
            ->add_rule('required')
            ->add_rule('valid_strings', array('numeric'))
        ;
        // 会社名チェック
        $validation->add('company_name', '会社名')
            ->add_rule('required')
        ;
        // 締日チェック
        $validation->add('closing_category', '締日')
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
            ->add_rule('required')
            ->add_rule('valid_strings', array('numeric', 'dashes'))
        ;
        // 住所チェック
        $validation->add('address', '住所１')
            ->add_rule('required')
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
        // 保管料金額チェック
        $validation->add('storage_fee', '保管料金額')
            ->add_rule('valid_strings', array('numeric', 'dashes'))
        ;
        
		$validation->run();
		return $validation;
	}

    // 更新処理
    private function update_record($conditions) {

        try {
            DB::start_transaction(M0025::$db);

            // レコード存在チェック
            if (!$result = M0025::getClient($conditions['client_code'], M0025::$db)) {
                return Config::get('m_MW0003');
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
            
            $error_msg = M0025::update_record($conditions, M0025::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

            DB::commit_transaction(M0025::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0025::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        
        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
        return null;
    }
    
    // 削除処理
    private function delete_record($client_code) {
        
        try {
            DB::start_transaction(M0025::$db);
            
            // レコード存在チェック
            if (!$result = M0025::getClient($client_code, M0025::$db)) {
                return Config::get('m_MW0003');
            }

            // レコード削除（論理）
            $error_msg = M0025::delete_record($client_code, M0025::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(M0025::$db);
                return $error_msg;
            }
            
            DB::commit_transaction(M0025::$db);
        
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0025::$db);
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
        $conditions 	= array_fill_keys(array(
        	'company_radio',
            'company_name',
            'sales_office_radio',
            'sales_office_name',
            'department_radio',
            'department_name',
            'client_code',
            'client_company_code',
            'client_company_name',
            'client_sales_office_code',
            'client_sales_office_name',
            'client_department_code',
            'client_department_name',
            'closing_category',
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
            'storage_fee',
            'storage_in_charge',
        ), '');
        
        $conditions_client = array_fill_keys(array(
            'closing_category',
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
            'storage_fee',
            'storage_in_charge',
        ), '');
        
        //担当部署の項目セット
        $conditions = M0020::setDepartmentInChargeColumn($this->division_list, $conditions);
        $conditions_client = M0020::setDepartmentInChargeColumn($this->division_list, $conditions_client);

        if (!empty(Input::param('hierarchy_edit')) && Input::method() == 'POST' && Security::check_token()) {
            // 「得意先階層編集」ボタン押下
            
            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_client as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            //セッションに値を保持
            Session::set('m0025_list', $conditions);
            
            // 会社情報編集画面へリダイレクト
            \Response::redirect(\Uri::create('mainte/m0026'));
        } elseif (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 「戻る」ボタン押下
            
            // 検索画面へリダイレクト
            Session::delete('m0025_list');
            \Response::redirect(\Uri::create('mainte/m0020'));
        } elseif (!empty(Input::param('update')) && Input::method() == 'POST' && Security::check_token()) {
            // 「更新」ボタン押下

            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_client as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            //セッションに値を保持
            Session::set('m0025_list', $conditions);
            
            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'client_code':
                            $error_column = '得意先コード';
                            break;
                        case 'company_name':
                            $error_column = '会社名';
                            break;
                        case 'closing_date':
                            $error_column = '締日';
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
                        case 'storage_fee':
                            $error_column = '保管料金額';
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
            
            //保管料金額が1以上かつ保管料部署が未選択なら入力エラー
            if ($conditions['storage_fee'] > 0 && empty((int)$conditions['storage_in_charge'])) {
                $error_msg = Config::get('m_MW0023');
            }
            
            //保管料金額が未入力かつ保管料部署が選択されていれば入力エラー
            if (empty($conditions['storage_fee']) && !empty((int)$conditions['storage_in_charge'])) {
                $error_msg = Config::get('m_MW0024');
            }

            if (empty($error_msg)) {
                // 更新処理
                $error_msg = $this->update_record($conditions);
            }
            
            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0025_list');
                \Response::redirect(\Uri::create('mainte/m0020'));
            }
        } elseif (!empty(Input::param('delete')) && Input::method() == 'POST' && Security::check_token()) {
            // 「削除」ボタン押下
            
            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions_client as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            
            //セッションに値を保持
            Session::set('m0025_list', $conditions);
            
            //得意先データ削除
            $error_msg = $this->delete_record($conditions['client_code']);
            
            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0025_list');
                \Response::redirect(\Uri::create('mainte/m0020'));
            }
        } else {
            if ($cond = Session::get('m0025_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            if (!empty(Input::param('processing_division', '')) && Input::param('processing_division', '') == 2) {
                //得意先検索画面から呼び出された時
                $client_code = Input::param('client_code', '');
                if (!empty($client_code)) {
                    $result = $this->get_info($client_code);
                    if (empty($result)) {
                        $error_msg = Config::get('m_MW0003');
                    } else {
                        $conditions = $result;
                    }
                }
            }
            
            Session::set('m0025_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'             => $error_msg,
                'data'                      => $conditions,
                'closing_category_list'     => $this->closing_category_list,
                'closing_date_list1'        => $this->closing_date_list1,
                'closing_date_list2'        => $this->closing_date_list2,
                'division_list'             => $this->division_list,
                'division_list_storage'     => $this->division_list_storage,
            )
        );
    }
}
