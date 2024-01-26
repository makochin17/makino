<?php
/**
 * 在庫情報登録画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Stock\D1111;

class Controller_Stock_D1111 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 課リスト
    private $division_list          = array();
    // 単位リスト
    private $unit_list              = array();

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
        $cnf['header_title']                = '在庫情報登録';
        $cnf['page_id']                     = '[D1111]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '在庫情報登録';
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
            'stock/d1111.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;


        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, D1111::$db);
        // 単位リスト取得
        $this->unit_list                = GenerateList::getUnitList(false, D1111::$db);

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
            if ($result = D1111::getSearchClient($code, D1111::$db)) {
                $conditions['list'][$list_no]['client_code'] = $result[0]['client_code'];
                $conditions['list'][$list_no]['client_name'] = $result[0]['client_name'];
            } else {
                $error_msg = Config::get('m_DW0002');
            }
            Session::delete('select_client_code');
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;
        $item = D1111::getValidateItems();

        // 入力チェック
        foreach ($conditions['list'] as $key => $val) {
            // ２レコード目以降で処理区分が更新または削除の場合はスルー
            if ($key > 0 && $conditions['processing_division'] != 1) {
                continue;
            }
            // バリデーション対象チェック
            // ２レコード目以降で指定項目が全て未入力の場合はスルー
            if (!D1111::chkStockDataNull($val) && $key > 0) {
                continue;
            }
            $validation = Validation::forge('list_'.$key);
            $validation->add_callable('myvalidation');

            // 得意先Noチェック
            $validation->add('list['.$key.'][client_code]', $item['client_code']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['client_code']['max_lengths'])
                ->add_rule('is_numeric');
            // 保管場所チェック
            $validation->add('list['.$key.'][storage_location]', $item['storage_location']['name'])
                ->add_rule('trim_max_lengths', $item['storage_location']['max_lengths']);
            // 商品名チェック
            $validation->add('list['.$key.'][product_name]', $item['product_name']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['product_name']['max_lengths']);
            // 数量チェック
            $validation->add('list['.$key.'][total_volume]', $item['total_volume']['name'])
                ->add_rule('required')
                ->add_rule('trim_max_lengths', $item['total_volume']['max_lengths'] + 6)
                ->add_rule('is_numeric_decimal', 6, true);
            // メーカーチェック
            $validation->add('list['.$key.'][maker_name]', $item['maker_name']['name'])
                ->add_rule('trim_max_lengths', $item['maker_name']['max_lengths']);
            // 品番チェック
            $validation->add('list['.$key.'][part_number]', $item['part_number']['name'])
                ->add_rule('trim_max_lengths', $item['part_number']['max_lengths']);
            // 型番チェック
            $validation->add('list['.$key.'][model_number]', $item['model_number']['name'])
                ->add_rule('trim_max_lengths', $item['model_number']['max_lengths']);
            // 備考チェック
            $validation->add('list['.$key.'][remarks]', $item['remarks']['name'])
                ->add_rule('trim_max_lengths', $item['remarks']['max_lengths']);
            $validation->run();
        }
        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        Config::load('message');
        $error_msg = null;

        // レコード登録
        $error_msg = D1111::create_record($conditions, D1111::$db);
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
        $conditions         = D1111::getForms('stock');
        $select_record      = Input::param('select_record', '');
        $list_no            = Input::param('list_no', '');
        $select_cancel      = Session::get('select_cancel');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('d1111_list');
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = D1111::setForms('stock', $conditions, Input::param());

            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_item = '';
                $item = D1111::getValidateItems();
                
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/client_code/', $key)) {
                        $error_item = 'client_code';
                    } elseif (preg_match('/storage_location/', $key)) {
                        $error_item = 'storage_location';
                    } elseif (preg_match('/total_volume/', $key)) {
                        $error_item = 'total_volume';
                    } elseif (preg_match('/product_name/', $key)) {
                        $error_item = 'product_name';
                    } elseif (preg_match('/maker_name/', $key)) {
                        $error_item = 'maker_name';
                    } elseif (preg_match('/part_number/', $key)) {
                        $error_item = 'part_number';
                    } elseif (preg_match('/model_number/', $key)) {
                        $error_item = 'model_number';
                    } elseif (preg_match('/remarks/', $key)) {
                        $error_item = 'remarks';
                    }
                    $error_column = $item[$error_item]['name'];
                    $column_length = $item[$error_item]['max_lengths'];
                    
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                        $error_msg = str_replace('xxxxx',$column_length,$error_msg);
                    } elseif ($validation->error()[$key]->rule == 'delivery_and_pickup_required_date') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0027'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(D1111::$db);

                    foreach ($conditions['list'] as $key => $val) {
                        // ２レコード目以降で処理区分が更新または削除の場合はスルー
                        if ($key > 0 && $conditions['processing_division'] != 1) {
                            continue;
                        }
                        // 指定項目が全て未入力の場合はスルー
                        if (!D1111::chkStockDataNull($val)) {
                            continue;
                        }

                        $val['division_code'] = $conditions['division_code'];
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                $error_msg = $this->create_record($val);
                                break;
                            case '2':
                                break;
                            case '3':
                                break;
                        }
                    }
                    if (empty($error_msg)) {
                        DB::commit_transaction(D1111::$db);
                        switch ($conditions['processing_division']){
                            case '1':
                                // 登録処理
                                echo "<script type='text/javascript'>alert('".Config::get('m_DI0027')."');</script>";
                                break;
                            case '2':
                                break;
                            case '3':
                                break;
                        }
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    $conditions = D1111::getForms();
                    Session::delete('d1111_list');
                    $redirect_flag = true;

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(D1111::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('d1111_list');
            Session::set('d1111_list', $conditions);
        } else {
            $conditions = D1111::setForms('stock', $conditions, Input::param());
            if ($cond = Session::get('d1111_list', array())) {
                $conditions = $cond;
                Session::delete('d1111_list');
            }

            if (!empty($select_record) && empty($select_cancel)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions, $list_no);
            }
            Session::delete('select_client_code');
            Session::delete('select_cancel');
            //初期表示もエクスポートに備えて条件保存する
            // Session::set('d1111_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('stock/d1110')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,

                'division_list'             => $this->division_list,
                'unit_list'                 => $this->unit_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

}
