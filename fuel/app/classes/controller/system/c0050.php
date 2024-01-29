<?php
/**
 * ユーザーマスタ削除画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\System\C0050;

class Controller_System_C0050 extends Controller_Hybrid {

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

    public function is_restful()
    {
        /**
         * Actionが list かつ
         * GET 変数にexceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'upload':
                return true;
        }
        return false;
    }

    // ユーザ情報
    private $user_authority             = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = 'システム設定';
        $cnf['page_id']                     = '[C0050]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'カレンダー休日設定';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

        $head                               = View::forge($this->head);
        $tree                               = View::forge($this->tree);
        $header                             = View::forge($this->header);
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
			'system/c0050.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
        );
        Asset::js($ary_header_js, array(), 'header_js', false);

        $ary_footer_js = array(
			// 'jquery.min.js',
			// 'common/skel.min.js',
			// 'common/util.js',
			// 'common/main.js',
			'system/c0050.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // ページング設定値取得
        // $paging_config = PagingConfig::getPagingConfig("UIL0010", L0010::$db);
        // $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        // $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        // $this->pagenation_config['per_page'] = 50;

        // ユーザ権限取得
        $this->user_authority               = $auth_data['user_authority'];
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

		// Rest処理判定
        if (!$this->is_restful()) {
            $this->initViewForge($auth_data);
        }

    }

	public function action_index() {

        /**
         * 初期設定
         */
		$error_msg 		= array();
		$calendar 		= null;
		$upWeek			= Input::param('upWeek', '');
		$upDay			= Input::param('upDay', '');
		$holidayMode	= Input::param('holidayMode', '');
		$y				= Input::param('y', '');

		//曜日指定で更新する
		if ($upWeek != '' && is_numeric($upWeek)) {

			if (!empty($upDay)) {
				$firstDay 	= date('Y/m/d', strtotime($upDay.' 00:00:00'));
			    $w_year 	= date('Y', strtotime($upDay.' 00:00:00'));
			    $w_month 	= date('m', strtotime($upDay.' 00:00:00'));

			    //月末の取得
			    $l_day 		= date('j', mktime(0, 0, 0, $w_month + 1, 0, $w_year));

				//更新対象の曜日を取得
				$upWeek 	= $upWeek;

			    // 月末まで繰り返す
			    for ($i = 1; $i < $l_day + 1;$i++) {
			        // 曜日の取得
			        $week = date('w', mktime(0, 0, 0, $w_month, $i, $w_year));
					//更新対象の曜日の場合は休日に設定する
					if ($upWeek == $week) {
						// 休日データ削除
						C0050::delCalendar($w_year.'/'.$w_month.'/'.$i, C0050::$db);
						// 休日データ追加
						C0050::setCalendar($w_year.'/'.$w_month.'/'.$i, C0050::$db);
					}
			    }
			}
		} else {
			//日付指定で更新する
			if (!empty($upDay)) {
				// 休日データ削除
				C0050::delCalendar($upDay, C0050::$db);
				if ($holidayMode == "1") {
					// 休日データ追加
					C0050::setCalendar($upDay, C0050::$db);
				}
			}
		}

		$current_year 	= date('Y');
		$current_month 	= 4;
        $month 			= date('n');

		if ($month == 1 || $month == 2 || $month == 3){
	        $current_year--;
		}

		//セットされていればURL引数を取得
		if (!empty($y)) {
			$current_year = $y;
		}
		//指定年度を保持
		$nendo = $current_year;

		// カレンダー表示
		for($i = 0; $i < 12; $i++){

			$calendar .= "<li style=\"display:inline-block;\">\n";
			$calendar .= C0050::calendar($current_year,$current_month, C0050::$db);
			$calendar .= "<br />\n";
			$calendar .= "</li>\n";

			//翌月に設定する
			if ($current_month == 12) {
				$current_year +=1;
				$current_month = 1;
			} else {
				$current_month += 1;
			}
		}

		$this->template->content = View::forge(AccessControl::getActiveController(),
												array(
                                                    'error_msg'						=> $error_msg,
                                                    'current_year'					=> $nendo,
                                                    'current_month'					=> $current_month,
												)
		);
		$this->template->content->set_safe('calendar', $calendar);

	}

}
