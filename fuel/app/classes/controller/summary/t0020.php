<?php
/**
 * 得意先別売上集計画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Summary\T0020;
use \Model\Search\S0080;

class Controller_Summary_T0020 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 集計対象リスト
    private $summary_category_list = array();
    // 課リスト
    private $division_list = array();
    // 配送区分リスト
    private $delivery_category_list = array();
    // 集計単位日付リスト
    private $aggregation_unit_date_list = array();
    // 集計単位会社リスト
    private $aggregation_unit_company_list = array();
    // 年リスト
    private $year_list = array();
    // 月リスト
    private $month_list = array();
    // 日リスト
    private $day_list = array();

    /**
    * 画面共通初期設定
    **/
	private function initViewForge($auth_data){
		// サイト設定
		$cnf                                = \Config::load('siteinfo', true);
		$cnf['header_title'] 				= '得意先別売上集計';
        $cnf['page_id'] 				    = '[T0020]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '得意先別売上集計';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

		$head   							= View::forge($this->head);
        $tree                               = View::forge($this->tree);
		$header 							= View::forge($this->header);
		$sidemenu 							= View::forge($this->sidemenu);
		$footer 							= View::forge($this->footer);
		$head->title			  			= $cnf['system_title'];
		$header->header_title				= $cnf['header_title'];
        $header->page_id				    = $cnf['page_id'];
        $tree->tree                         = $cnf['tree'];
        $tree->tree                         = '';
		$sidemenu->login_user_name          = AuthConfig::getAuthConfig('name');
		$sidemenu->copyright				= $cnf['copyright'];

		// テンプレートに定義するCSS・JS
		$ary_jquery_ui_css = array(
			''
		);
		Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

		//PCorスマホで読み込むCSSを変更
		$ary_style_css = array(
			'font-awesome/css/font-awesome.min.css'
		);
		Asset::css($ary_style_css, array(), 'style_css', false);

		// テンプレートに渡す定義
		$this->template->head = $head;
        $this->template->header 	= $header;
		$this->template->sidemenu 	= $sidemenu;
		$this->template->footer 	= $footer;
        $this->template->tree       = $tree;

        // 集計対象リスト取得
        $this->summary_category_list = array(1 => "チャーター便",2 => "共配便",3 => "入出庫料・保管料");
        // 課リスト取得
        $this->division_list = GenerateList::getDivisionList(true, T0020::$db);
        // 配送区分リスト取得
        $this->delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        // 集計単位日付リスト
        $this->aggregation_unit_date_list = GenerateList::getAggregationUnitDateList();
        // 集計単位会社リスト
        $this->aggregation_unit_company_list = GenerateList::getAggregationUnitCompanyList();
        // 年リスト
        $this->year_list = GenerateList::getYearList();
        // 月リスト
        $this->month_list = GenerateList::getMonthList();
        // 日リスト
        $this->day_list = GenerateList::getDayList();
	}

	public function before() {
		parent::before();
		// ログインチェック
		if(!Auth::check()) {
			Response::redirect(\Uri::base(false));
		}

		// 担当者情報を設定
		$auth_data = AuthConfig::getAuthConfig('all');

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}
    
    // 検索画面にてレコード選択された場合の処理
    private function set_info(&$conditions) {
        $error_msg = null;
        
		if ($code = Session::get('select_client_code')) {
            // 得意先の検索にてレコード選択された場合
            $result = S0080::getSearchClient($code, T0020::$db);
            if (count($result) > 0) {
                $conditions['client_code'] = $result[0]['client_code'];
            } else {
                $error_msg = Config::get('m_TW0002');
            }
            Session::delete('select_client_code');
        }
        
        return $error_msg;
	}
    
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
            'summary_category',
        	'division',
            'client_radio',
        	'client_code',
        	'delivery_category',
        	'aggregation_unit_date',
        	'aggregation_unit_company',
            'start_year',
            'start_month',
            'start_day',
            'end_year',
            'end_month',
            'end_day',
        ), '');
        
        foreach ($conditions as $key => $val) {
            $conditions[$key] = Input::param($key, ''); // 検索項目
        }
        
        if (!empty(Input::param('output'))) {
            
            Session::set('t0020_list', $conditions);
            
            //集計開始・終了の入力チェック
            $result = T0020::checkDate();
            if (!empty($result)) {
                $error_msg = $result;
            } else {
                // 帳票出力
                //T0020::createExcel();
                T0020::createTsv();
            }
            
        } else {
            if ($cond = Session::get('t0020_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('t0020_list');
            }
            
            if (!empty(Input::param('select_record'))) {
                // 検索画面からコードが連携された場合の処理
                
                // 連携されたコードによる情報取得＆値セット
                $error_msg = $this->set_info($conditions);
                
                Session::set('t0020_list', $conditions);
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'summary_category_list'         => $this->summary_category_list,
                'division_list'                 => $this->division_list,
                'delivery_category_list'        => $this->delivery_category_list,
                'aggregation_unit_date_list'    => $this->aggregation_unit_date_list,
                'aggregation_unit_company_list' => $this->aggregation_unit_company_list,
                'year_list'                     => $this->year_list,
                'month_list'                    => $this->month_list,
                'day_list'                      => $this->day_list,
                'error_message'                 => $error_msg
            )
        );
        
    }
}
