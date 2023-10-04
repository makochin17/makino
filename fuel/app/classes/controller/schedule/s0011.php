<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Schedule\S0010;

class Controller_Schedule_S0011 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_schedule2';
    private $head       = 'head_schedule2';
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

    // 会社情報リスト
    private $company_list               = array();
    // 作業所要時間リスト
    private $work_time_list             = array();
    // 作業選択時間リスト
    private $select_work_time_list      = array();
    // ユニットリスト
    private $unit_list                  = array();
    // 依頼区分リスト
    private $request_class_list         = array();

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
        $cnf['header_title']                = '予約スケジュール';
        $cnf['page_id']                     = '[S0010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '予約スケジュール';
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
            // 'common/jquery.jqplot.css',
            // 'common/jqModal.css'
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
            'schedule/s0010.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head_schedule2 = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;


        // 会社情報リスト取得
        $this->company_select_list      = GenerateList::getCompanySelectList(S0010::$db);
        // 作業所要時間リスト
        $this->work_time_list           = GenerateList::getWorkTimeList(true);
        // 作業選択時間リスト
        $this->select_work_time_list    = GenerateList::getSelectWorkTimeList(false);
        // ユニットリスト
        $this->unit_list                = GenerateList::getUnitList('all', S0010::$db);
        // 依頼区分リスト
        $this->request_class_list       = GenerateList::getRequestClassList(false);

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
    private function set_info(&$conditions) {
        $error_msg = null;

        if ($code = Session::get('select_customer_code')) {
            // 得意先の検索にてレコード選択された場合
            if ($result = S0010::getSearchCustomer($code, S0010::$db)) {
                $conditions['customer_code'] = $result['customer_code'];
                $conditions['customer_name'] = $result['customer_name'];
            } else {
                $error_msg = Config::get('m_CUS011');
            }
            Session::delete('select_customer_code');
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
            if (!S0010::chkDispatchShareDataNull($val)) {
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
            $conditions['carrier_code'] = S0010::getCarrierCode($conditions['member_code'], $conditions['driver_name'], S0010::$db);
        }
        if (empty($conditions['carrier_code'])) {
            return str_replace('XXXXX','庸車No',Config::get('m_CW0005'));
        }
        // // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
        // if (S0010::OurCompanyCheck($conditions['carrier_code'], S0010::$db)) {
        //     // 車両コードが車両マスタに登録されているかチェック
        //     if (!S0010::getNameById('car', $conditions['car_code'], S0010::$db)) {
        //         return Config::get('m_DW0021');
        //     }
        // }

        // レコード登録
        $error_msg = S0010::create_record($conditions, S0010::$db);
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
        $schedule_list      = array();

        $company            = $this->company_select_list;
        $unit_cd            = null;
        $unit               = Input::param('unit', '');
        $cboUnit            = Input::param('cboUnit', '');

        // 時刻設定
        $start_hour         = null;
        $start_time         = null;
        $end_hour           = null;
        $end_time           = null;
        // 日付設定
        $w_year             = Input::param('y', '');
        $w_month            = Input::param('m', '');
        $w_day              = Input::param('d', '01');
        $conditions         = array_fill_keys(array(
            'unit_id',
            'unit',
            'cboUnit',
            'y',
            'm',
            'd',
            'customer_code',
            'customer_name',
            'default_day',
            'start_time',
            'end_time',
            'span_min',
            'fullcalendar_key'
        ), '');
        foreach ($conditions as $key => $val) {
            if ($key == 'd') {
                $conditions[$key] = Input::param($key, '01'); // 検索項目
            } else {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
        }
        // スケジュールライセンスキー設定
        $conditions['fullcalendar_key'] = SystemConfig::getSystemConfig('fullcalendar_key',S0010::$db);
        // 会社情報設定
        if (!empty($company)) {
            $conditions['start_time']   = (!empty($company['start_time'])) ? $company['start_time']:'00:00';
            $conditions['end_time']     = (!empty($company['end_time'])) ? $company['end_time']:'24:00';
            $conditions['span_min']     = (!empty($company['span_min'])) ? $company['span_min']:'20';

            // 時刻設定
            $s          = preg_split("/[\:]/", $company['start_time']);
            $start_hour = $s[0];
            $start_time = $s[1];
            $s          = preg_split("/[\:]/", $company['end_time']);
            $end_hour   = $s[0];
            $end_time   = $s[1];
            $start_h    = (int)$start_hour;
            $end_h      = (int)$end_hour;

        }
        // 指定日付設定
        $conditions['default_day'] = date("Y-m-d");
        if (!empty($w_month)) {
            $w_month  = str_pad(strval($w_month), 2, '0', STR_PAD_LEFT);
        }
        if (!empty($w_day) && $w_day != '01') {
            $w_day  = str_pad(strval($w_day), 2, '0', STR_PAD_LEFT);
        }
        if (!empty($w_year) && !empty($w_month) && !empty($w_day)) {
            $conditions['default_day'] = $w_year."-".$w_month."-".$w_day;
        }

        if (!empty(Input::param('select_record'))) {
            // 検索項目の検索画面からコードが連携された場合の処理
            // 連携されたコードによる情報取得＆値セット
            $error_msg      = $this->set_info($conditions);
        }

        if (Input::method() == 'POST' && Security::check_token()) {
            //POST送信の場合の処理
            if (!empty($conditions['cboUnit'])) {
                $conditions['unit_id'] = $conditions['cboUnit'];
            } else {
                $conditions['unit_id'] = Session::get('schedule_unit_id', '');
            }
        } else {
            //POSTでない場合
            if (!empty($conditions['unit'])) {
                $conditions['unit_id'] = $conditions['unit'];
            } else {
                if (!$conditions['unit_id'] = Session::get('schedule_unit_id', '')) {
                    if (!empty($conditions['cboUnit'])) {
                        $conditions['unit_id'] = $conditions['cboUnit'];
                    }
                }
            }

        }
        Session::set('schedule_unit_id', $conditions['unit_id']);

        // ユニット別予約スケジュール取得(全件)
        $schedule_all_list  = S0010::getScheduleByUnit($conditions['unit_id'], S0010::$db);

        // 個別予約スケジュール取得
        $schedule_list      = S0010::getScheduleByCustomer($conditions, S0010::$db);

        // カレンダー取得
        $calendar           = S0010::CalendarView(S0010::$db);

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'getcar_url'                => \Uri::create(\Uri::create('schedule/fullcalendar/getcar')),
                'current_url'               => \Uri::create(AccessControl::getActiveController().'/detail'),
                'master_url'                => \Uri::create(AccessControl::getActiveController().'/master'),

                'data'                      => $conditions,
                // ユニット別予約スケジュール
                'schedule_all_list'         => $schedule_all_list,
                // 個別予約スケジュール
                'schedule_list'             => $schedule_list,

                // 時刻設定
                'start_hour'                => $start_hour,
                'start_time'                => $start_time,
                'end_hour'                  => $end_hour,
                'end_time'                  => $end_time,
                'start_h'                   => $start_h,
                'end_h'                     => $end_h,

                'company_list'              => $this->company_select_list,
                'work_time_list'            => $this->work_time_list,
                'select_work_time_list'     => $this->select_work_time_list,
                'unit_list'                 => $this->unit_list,
                'request_class_list'        => $this->request_class_list,

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                // ユニットリストデータ
                'unit'                      => S0010::setList('unit', S0010::getUnit(null, S0010::$db)),

                'error_message'             => $error_msg,
            )
        );
        // カレンダー
        $this->template->content->set_safe('calendar', $calendar);

    }

}
