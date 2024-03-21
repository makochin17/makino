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

class Controller_Schedule_S0012 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_schedule';
    private $head       = 'head_schedule';
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
    // 予約権限設定
    private $schedule_authority         = array();

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'detail':
            case 'chhourtime':
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
        $cnf['page_id']                     = '[S0012]';
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
            'schedule/s0010.css',
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
        $this->unit_list                = GenerateList::getUnitList('all', S0010::$schedule_type, false, S0010::$db);
        // 依頼区分リスト
        $this->request_class_list       = GenerateList::getRequestClassList(false);
        // 予約権限設定
        $this->schedule_authority       = GenerateList::$schedule_authority;

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

        // 予約タイプ
        S0010::$schedule_type = 'usually';

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

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $company            = $this->company_select_list;

        $cnt                = 0;
        $error_msg          = null;
        $schedule_all_list  = array();
        $schedule_list      = array();
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
            'txtCustomerCode',
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
        // $conditions['default_day'] = date("Y-m-d");
        $d              = new \DateTime();
        $default_day    = $d->modify('+3 day')->format("Y-m-d");
        // $default_day    = $d->format("Y-m-d");
        $conditions['default_day'] = $default_day;
        if (!empty($w_month)) {
            $w_month    = str_pad(strval($w_month), 2, '0', STR_PAD_LEFT);
        }
        if (!empty($w_day) && $w_day != '01') {
            $w_day      = str_pad(strval($w_day), 2, '0', STR_PAD_LEFT);
        }
        if (!empty($w_year) && !empty($w_month) && !empty($w_day)) {
            $conditions['default_day'] = $w_year."-".$w_month."-".$w_day;
        }
        // デフォルト日よりも過去日の場合は予約スケジュールの編集ができないようにする
        $today          = $default_day;
        $target_day     = $conditions['default_day'];
        $editable       = true;
        if(strtotime($today) > strtotime($target_day)){
            $editable   = false;
        }

        if (!empty(Input::param('select_record'))) {
            // 検索項目の検索画面からコードが連携された場合の処理
            // 連携されたコードによる情報取得＆値セット
            $error_msg      = $this->set_info($conditions);
        }

        if (Input::method() == 'POST' && Security::check_token()) {
            //POST送信の場合の処理
            if (!empty($conditions['cboUnit']) || $conditions['cboUnit'] == 0) {
                $conditions['unit_id'] = $conditions['cboUnit'];
            } else {
                $conditions['unit_id'] = Session::get('schedule_unit_id', '');
            }
            if (!empty($conditions['txtCustomerCode'])) {
                $conditions['customer_code'] = $conditions['txtCustomerCode'];
            }
            if (!empty(Input::param('reset')) && Security::check_token()) {
                $conditions['customer_code'] = null;
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
        $schedule_all_list  = S0010::getScheduleByUnit($conditions, S0010::$schedule_type, S0010::$db);

        // 個別予約スケジュール取得
        $schedule_list      = S0010::getScheduleByCustomer($conditions, S0010::$schedule_type, S0010::$db);
        // カレンダー取得
        $calendar           = S0010::CalendarView(\Uri::create(AccessControl::getActiveController()), S0010::$db);

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'customer_check_url'        => \Uri::create(\Uri::create('schedule/fullcalendar/cuscheck')),

                'getcar_url'                => \Uri::create(\Uri::create('schedule/fullcalendar/getcar')),
                'addschedule_url'           => \Uri::create(\Uri::create('schedule/fullcalendar/addschedule')),
                'geteventinfo_url'          => \Uri::create(\Uri::create('schedule/fullcalendar/geteventinfo')),
                'changedateschedule_url'    => \Uri::create(\Uri::create('schedule/fullcalendar/changedateschedule')),
                'cancelschedule_url'        => \Uri::create(\Uri::create('schedule/fullcalendar/cancelschedule')),
                'commitschedule_url'        => \Uri::create(\Uri::create('schedule/fullcalendar/commitschedule')),
                'chhourtime_url'            => \Uri::create(\Uri::create('schedule/fullcalendar/changehourtime')),
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
                // 画面編集フラグ
                'editable'                  => $editable,
                // 初期設定日
                'default_day'               => $default_day,
                'default_y'                 => date('Y', strtotime($default_day)),
                'default_m'                 => date('m', strtotime($default_day)),
                'default_d'                 => date('d', strtotime($default_day)),

                'company_list'              => $this->company_select_list,
                'work_time_list'            => $this->work_time_list,
                'select_work_time_list'     => $this->select_work_time_list,
                'unit_list'                 => $this->unit_list,
                'request_class_list'        => $this->request_class_list,
                'schedule_authority'        => $this->schedule_authority,
                'holiday_list'              => S0010::getCalendarAll(S0010::$db),

                // 社員情報
                'userinfo'                  => AuthConfig::getAuthConfig('all'),
                // ユニットリストデータ
                'unit'                      => S0010::setList('unit', S0010::getUnit(null, S0010::$schedule_type, false, S0010::$db)),

                'error_message'             => $error_msg,
            )
        );
        // カレンダー
        $this->template->content->set_safe('calendar', $calendar);

    }

}
