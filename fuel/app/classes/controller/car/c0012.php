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
use \Model\Car\C0010;
use \Model\Car\C0012;

class Controller_Car_C0012 extends Controller_Hybrid {

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
        'uri_segment'   => 'p',
        'num_links'     => 2,
        'per_page'      => 50,
        'name'          => 'default',
        'show_first'    => true,
        'show_last'     => true,
    );

    // 会社マスタリスト
    private $company_list       = array();
    // タイヤ種別リスト
    private $tire_kind_list     = array();
    // 保管場所リスト
    private $location_list      = array();
    // YES/NOリスト
    private $yes_no_list        = array();
    // 作業所要時間リスト
    private $work_time_list     = array();

    // ユーザ情報
    private $user_authority     = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'detail':
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
        $cnf['header_title']                = '車両情報更新';
        $cnf['page_id']                     = '[C0012]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '車両情報更新';
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
            'car/c0012.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);
        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'car/c0012.js',
            'car/c0012_form.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 会社マスタリスト
        $this->company_list             = GenerateList::getCompanyList(true, C0010::$db);
        // タイヤ種別リスト
        $this->tire_kind_list           = GenerateList::getTireKindList(true, C0010::$db);
        // 保管場所リスト
        $this->location_list            = GenerateList::getLocationList(true, C0010::$db);
        // YES/NOリスト
        $this->yes_no_list              = GenerateList::getYesnoFlgList(false);
        // 作業所要時間リスト
        $this->work_time_list           = GenerateList::getWorkTimeList(false);

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
        //  Response::redirect(\Uri::create('top'));
        //}
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }
    }

    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = C0010::getSearchCustomer($code, C0010::$db)) {
                $conditions['customer_code']    = $result['customer_code'];
                $conditions['customer_name']    = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
        } elseif (!empty($conditions['car_id'])) {
            if ($result = C0012::getCar($conditions['car_id'], C0012::$db)) {
                $conditions                     = C0012::setForms('car', $conditions, $result);
            } else {
                $error_msg = Config::get('m_CAR011');
            }
        }

        return $error_msg;
    }

    // 入力チェック
    private function validate_info($conditions) {

        $validation = false;

        // 入力チェック
        $validation = Validation::forge('valid_car');
        $validation->add_callable('myvalidation');

        // 旧車両IDチェック
        $validation->add('old_car_id', '車両ID')
            ->add_rule('required')
            ->add_rule('is_numeric')
        ;
        // 登録番号/車両番号チェック
        $validation->add('car_code', '登録番号')
            ->add_rule('required')
        ;
        // お客様番号チェック
        $validation->add('customer_code', 'お客様番号')
            ->add_rule('required')
        ;
        // お客様名チェック
        $validation->add('customer_name', 'お客様')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 所有者名チェック
        $validation->add('owner_name', '所有者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 使用者名チェック
        $validation->add('consumer_name', '使用者')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;
        // 車種名チェック
        $validation->add('car_name', '車種')
            ->add_rule('required')
            ->add_rule('trim_max_lengths', 250)
        ;

        // 夏タイヤメーカーチェック
        if (!empty($conditions['summer_tire_maker'])) {
            $validation->add('summer_tire_maker', '夏タイヤメーカー')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ商品名チェック
        if (!empty($conditions['summer_tire_product_name'])) {
            $validation->add('summer_tire_product_name', '夏タイヤ商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤサイズチェック
        if (!empty($conditions['summer_tire_size'])) {
            $validation->add('summer_tire_size', '夏タイヤサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤタイヤパターンチェック
        if (!empty($conditions['summer_tire_pattern'])) {
            $validation->add('summer_tire_pattern', '夏タイヤタイヤパターン')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤホイール商品名チェック
        if (!empty($conditions['summer_tire_wheel_product_name'])) {
            $validation->add('summer_tire_wheel_product_name', '夏タイヤホイール商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤホイールサイズチェック
        if (!empty($conditions['summer_tire_wheel_size'])) {
            $validation->add('summer_tire_wheel_size', '夏タイヤホイールサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ製造年チェック
        if (!empty($conditions['summer_tire_made_date'])) {
            $validation->add('summer_tire_made_date', '夏タイヤ製造年')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 夏タイヤ残溝数１チェック
        if (!empty($conditions['summer_tire_remaining_groove1'])) {
            $validation->add('summer_tire_remaining_groove1', '夏タイヤ残溝数１')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数２チェック
        if (!empty($conditions['summer_tire_remaining_groove2'])) {
            $validation->add('summer_tire_remaining_groove2', '夏タイヤ残溝数２')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数３チェック
        if (!empty($conditions['summer_tire_remaining_groove3'])) {
            $validation->add('summer_tire_remaining_groove3', '夏タイヤ残溝数３')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤ残溝数４チェック
        if (!empty($conditions['summer_tire_remaining_groove4'])) {
            $validation->add('summer_tire_remaining_groove4', '夏タイヤ残溝数４')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 夏タイヤパンク、傷チェック
        if (!empty($conditions['summer_tire_punk'])) {
            $validation->add('summer_tire_punk', '夏タイヤパンク')
                ->add_rule('trim_max_lengths', 250)
            ;
        }

        // 冬タイヤメーカーチェック
        if (!empty($conditions['winter_tire_maker'])) {
            $validation->add('winter_tire_maker', '冬タイヤメーカー')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ商品名チェック
        if (!empty($conditions['winter_tire_product_name'])) {
            $validation->add('winter_tire_product_name', '冬タイヤ商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤサイズチェック
        if (!empty($conditions['winter_tire_size'])) {
            $validation->add('winter_tire_size', '冬タイヤサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤタイヤパターンチェック
        if (!empty($conditions['winter_tire_pattern'])) {
            $validation->add('winter_tire_pattern', '冬タイヤタイヤパターン')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤホイール商品名チェック
        if (!empty($conditions['winter_tire_wheel_product_name'])) {
            $validation->add('winter_tire_wheel_product_name', '冬タイヤホイール商品')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤホイールサイズチェック
        if (!empty($conditions['winter_tire_wheel_size'])) {
            $validation->add('winter_tire_wheel_size', '冬タイヤホイールサイズ')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ製造年チェック
        if (!empty($conditions['winter_tire_made_date'])) {
            $validation->add('winter_tire_made_date', '冬タイヤ製造年')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 冬タイヤ残溝数１チェック
        if (!empty($conditions['winter_tire_remaining_groove1'])) {
            $validation->add('winter_tire_remaining_groove1', '冬タイヤ残溝数１')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数２チェック
        if (!empty($conditions['winter_tire_remaining_groove2'])) {
            $validation->add('winter_tire_remaining_groove2', '冬タイヤ残溝数２')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数３チェック
        if (!empty($conditions['winter_tire_remaining_groove3'])) {
            $validation->add('winter_tire_remaining_groove3', '冬タイヤ残溝数３')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤ残溝数４チェック
        if (!empty($conditions['winter_tire_remaining_groove4'])) {
            $validation->add('winter_tire_remaining_groove4', '冬タイヤ残溝数４')
                ->add_rule('is_numeric_decimal', 2, true)
                ->add_rule('trim_max_lengths', 8)
            ;
        }
        // 冬タイヤパンク、傷チェック
        if (!empty($conditions['winter_tire_punk'])) {
            $validation->add('winter_tire_punk', '冬タイヤパンク')
                ->add_rule('trim_max_lengths', 250)
            ;
        }
        // 注意事項チェック
        if (!empty($conditions['note'])) {
            $validation->add('note', '注意事項')
                ->add_rule('trim_max_lengths', 2000)
            ;
        }
        // メッセージチェック
        if (!empty($conditions['message'])) {
            $validation->add('message', 'メッセージ')
                ->add_rule('trim_max_lengths', 2000)
            ;
        }

        $validation->run();

        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0012::$db);

            $error_msg = C0012::create_record($conditions, C0012::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(C0012::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0012::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_CAR005')."');</script>";
        return null;
    }

    // 更新処理
    private function update_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0012::$db);

            $error_msg = C0012::update_record($conditions, C0012::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(C0012::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0012::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_CAR005')."');</script>";
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
        $conditions         = C0012::getForms('car');
        $select_record      = Input::param('select_record', '');
        $car_id             = Input::param('car_id', '');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('c0012_list');
        } elseif (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            Session::delete('c0012_list');
            \Response::redirect(\Uri::create('car/c0010'));
        } elseif (!empty(Input::param('mode')) && Input::param('mode') == 'list' && Input::method() == 'POST' && Security::check_token()) {
            // 検索画面へリダイレクト
            Session::delete('c0012_list');
            if ($result = C0012::getCar($car_id, C0012::$db)) {
                $conditions = C0012::setForms('car', $conditions, $result);
                Session::set('c0012_list', $conditions);
            }
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理
            $conditions = C0012::setForms('car', $conditions, Input::param());

            // 入力値チェック
            if ($validation = $this->validate_info($conditions)){
                $errors     = $validation->error();
                $error_column = '';
                // 入力値チェックのエラー判定
                foreach($validation->error() as $key => $e) {
                    if (preg_match('/old_car_id/', $key)) {
                        $error_column = '車両ID';
                    } elseif (preg_match('/car_code/', $key)) {
                        $error_column = '登録番号';
                    } elseif (preg_match('/customer_code/', $key)) {
                        $error_column = 'お客様番号';
                    } elseif (preg_match('/customer_name/', $key)) {
                        $error_column = 'お客様名';
                    } elseif (preg_match('/owner_name/', $key)) {
                        $error_column = '所有者';
                    } elseif (preg_match('/consumer_name/', $key)) {
                        $error_column = '使用者';
                    } elseif (preg_match('/car_name/', $key)) {
                        $error_column = '車種';
                    } elseif (preg_match('/summer_tire_maker/', $key)) {
                        $error_column = '夏タイヤメーカー';
                    } elseif (preg_match('/summer_tire_product_name/', $key)) {
                        $error_column = '夏タイヤ商品';
                    } elseif (preg_match('/summer_tire_size/', $key)) {
                        $error_column = '夏タイヤサイズ';
                    } elseif (preg_match('/summer_tire_pattern/', $key)) {
                        $error_column = '夏タイヤタイヤパターン';
                    } elseif (preg_match('/summer_tire_wheel_product_name/', $key)) {
                        $error_column = '夏タイヤホイール商品';
                    } elseif (preg_match('/summer_tire_wheel_size/', $key)) {
                        $error_column = '夏タイヤホイールサイズ';
                    } elseif (preg_match('/summer_tire_made_date/', $key)) {
                        $error_column = '夏タイヤ製造年';
                    } elseif (preg_match('/summer_tire_remaining_groove1/', $key)) {
                        $error_column = '夏タイヤ残溝数１';
                    } elseif (preg_match('/summer_tire_remaining_groove2/', $key)) {
                        $error_column = '夏タイヤ残溝数２';
                    } elseif (preg_match('/summer_tire_remaining_groove3/', $key)) {
                        $error_column = '夏タイヤ残溝数３';
                    } elseif (preg_match('/summer_tire_remaining_groove4/', $key)) {
                        $error_column = '夏タイヤ残溝数４';
                    } elseif (preg_match('/summer_tire_punk/', $key)) {
                        $error_column = '夏タイヤパンク';
                    } elseif (preg_match('/winter_tire_maker/', $key)) {
                        $error_column = '冬タイヤメーカー';
                    } elseif (preg_match('/winter_tire_product_name/', $key)) {
                        $error_column = '冬タイヤ商品';
                    } elseif (preg_match('/winter_tire_size/', $key)) {
                        $error_column = '冬タイヤサイズ';
                    } elseif (preg_match('/winter_tire_pattern/', $key)) {
                        $error_column = '冬タイヤタイヤパターン';
                    } elseif (preg_match('/winter_tire_wheel_product_name/', $key)) {
                        $error_column = '冬タイヤホイール商品';
                    } elseif (preg_match('/winter_tire_wheel_size/', $key)) {
                        $error_column = '冬タイヤホイールサイズ';
                    } elseif (preg_match('/winter_tire_made_date/', $key)) {
                        $error_column = '冬タイヤ製造年';
                    } elseif (preg_match('/winter_tire_remaining_groove1/', $key)) {
                        $error_column = '冬タイヤ残溝数１';
                    } elseif (preg_match('/winter_tire_remaining_groove2/', $key)) {
                        $error_column = '冬タイヤ残溝数２';
                    } elseif (preg_match('/winter_tire_remaining_groove3/', $key)) {
                        $error_column = '冬タイヤ残溝数３';
                    } elseif (preg_match('/winter_tire_remaining_groove4/', $key)) {
                        $error_column = '冬タイヤ残溝数４';
                    } elseif (preg_match('/winter_tire_punk/', $key)) {
                        $error_column = '冬タイヤパンク';
                    } elseif (preg_match('/note/', $key)) {
                        $error_column = '注意事項';
                    } elseif (preg_match('/message/', $key)) {
                        $error_column = 'メッセージ';
                    }
                    if ($validation->error()[$key]->rule == 'required' || $validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_date_format') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0018'));
                    } elseif ($validation->error()[$key]->rule == 'is_numeric' || $validation->error()[$key]->rule == 'is_numeric_decimal') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0013'));
                    } elseif ($validation->error()[$key]->rule == 'trim_max_lengths') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0014'));
                    } elseif ($validation->error()[$key]->rule == 'valid_zip') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0026'));
                    } elseif ($validation->error()[$key]->rule == 'valid_phone_no') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_DW0027'));
                    } elseif ($validation->error()[$key]->rule == 'valid_mail') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0023'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }
            if (empty($error_msg)) {
                // 登録処理
                try {
                    DB::start_transaction(C0012::$db);
                    switch ($conditions['mode']){
                        case '1':
                            // 登録処理
                            $error_msg = $this->create_record($conditions);
                            break;
                        case '2':
                            // 更新処理
                            $error_msg = $this->update_record($conditions);
                            break;
                        case '3':
                            // 削除処理
                            $error_msg = $this->delete_record($conditions);
                            break;
                    }

                    if (empty($error_msg)) {
                        DB::commit_transaction(C0012::$db);
                    } else {
                        throw new Exception($error_msg, 1);
                    }
                    // 成功したらフォーム情報を初期化
                    // $conditions = C0012::getForms();
                    // Session::delete('c0012_list');
                    $redirect_flag = true;
                    // var_dump($conditions['mode']);

                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    DB::rollback_transaction(C0012::$db);
                    // return $e->getMessage();
                    Log::error($e->getMessage());
                    // var_dump($conditions['mode']);
                    // var_dump($e->getMessage());
                    $error_msg = $e->getMessage();
                    // $error_msg = Config::get('m_CE0001');
                }
            }

            /**
             * セッションに検索条件を設定
             */
            Session::delete('c0012_list');
            Session::set('c0012_list', $conditions);
        } else {
            $conditions = C0012::setForms('car', $conditions, Input::param());
            if ($cond = Session::get('c0012_list', array())) {
                $conditions = $cond;
                Session::delete('c0012_list');
            }

            if (!empty($select_record)) {
                // 検索画面からコードが連携された場合の処理
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
            }

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'list_url'                  => \Uri::create(\Uri::create('car/c0010')),
                'upload_url'                => \Uri::create('file/fileupload'),
                'check_url'                 => \Uri::create('car/c0012/chcarid'),

                'data'                      => $conditions,
                'docroot'                   => DOCROOT,
                'car_id'                    => $car_id,

                'company_list'              => $this->company_list,
                'location_list'             => $this->location_list,
                'yes_no_list'               => $this->yes_no_list,
                'work_time_list'            => $this->work_time_list,
                'user_authority'            => $this->user_authority,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),

                'error_message'             => $error_msg,
                'redirect_flag'             => $redirect_flag
            )
        );

    }

    /**
    * 車両IDチェック
    **/
    public function action_chcarid() {

        $res        = 1;
        $car_id     = Input::param('car_id', '');

        if (!C0012::getCar($car_id, C0012::$db)) {
            $res    = 0;
        }

        $this->response->set_header('Access-Control-Allow-Origin', '*');
        return $this->response($res);
    }

}
