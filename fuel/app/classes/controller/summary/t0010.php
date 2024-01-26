<?php
/**
 * 課別売上集計画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Summary\T0010;

class Controller_Summary_T0010 extends Controller_Hybrid {

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
    // 配送区分リスト
    private $delivery_category_list = array();
    // 集計項目リスト
    private $graph_item_list = array();
    // 集計項目リスト（入出庫料・保管料用）
    private $graph_item_list_sc = array();
    // 集計単位日付リスト
    private $aggregation_unit_date_list = array();
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
		$cnf['header_title'] 				= '課別売上集計';
        $cnf['page_id'] 				    = '[T0010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '課別売上集計';
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

        // 配送区分リスト取得
        $this->summary_category_list = array(1 => "チャーター便",2 => "共配便",3 => "入出庫料・保管料",);
        // 配送区分リスト取得
        $this->delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        // 集計項目リスト
        $this->graph_item_list = GenerateList::getAggregationItemList();
        // 集計項目リスト（入出庫料・保管料用）
        $this->graph_item_list_sc = array(1 => "入庫料",2 => "出庫料",3 => "保管料",);
        // 集計単位日付リスト
        $this->aggregation_unit_date_list = GenerateList::getAggregationUnitDateList();
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
        
    public function action_index() {
        
        Config::load('message');
        
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg = null;
        $summary_flag = false;
        $sales_category_list = array();
        $caption_list = array();
        $summary_data_dispatch = array();
        $summary_data_sales_correction = array();
        $summary_data_dispatch_share = array();
        $summary_data_stock = array();
        $conditions 	= array_fill_keys(array(
            'summary_category',
        	'delivery_category',
        	'aggregation_unit_date',
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
            
            Session::set('t0010_list', $conditions);
            
            //集計開始・終了の入力チェック
            $result = T0010::checkDate();
            if (!empty($result)) {
                $error_msg = $result;
            } else {
                // 帳票出力
                //T0010::createExcel();
                T0010::createTsv();
            }
            
        } elseif (!empty(Input::param('summary'))) {
            
            Session::set('t0010_list', $conditions);
            
            //集計開始・終了の入力チェック
            $result = T0010::checkDate();
            if (!empty($result)) {
                $error_msg = $result;
            } else {
                $summary_flag = true;
                $sales_category_list = GenerateList::getSalesCategoryList(false, T0010::$db);
                $caption_list = T0010::getCaption();
                
                if ($conditions['summary_category'] == 1) {
                    $summary_data_dispatch = T0010::getSummaryDataDispatch();
                } elseif ($conditions['summary_category'] == 2) {
                    $summary_data_dispatch_share = T0010::getSummaryDataDispatchShare();
                } elseif ($conditions['summary_category'] == 3) {
                    $summary_data_stock = T0010::getSummaryDataStock();
                }
                
                if ($conditions['summary_category'] == 1 || $conditions['summary_category'] == 2) {
                    $summary_data_sales_correction = T0010::getSummaryDataSalesCorrection();
                }
            }
            
        } else {
            if ($cond = Session::get('t0010_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
                Session::delete('t0010_list');
            }

        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                          => $conditions,
                'summary_category_list'         => $this->summary_category_list,
                'delivery_category_list'        => $this->delivery_category_list,
                'graph_item_list'               => $this->graph_item_list,
                'graph_item_list_sc'            => $this->graph_item_list_sc,
                'sales_category_list'           => $sales_category_list,
                'aggregation_unit_date_list'    => $this->aggregation_unit_date_list,
                'year_list'                     => $this->year_list,
                'month_list'                    => $this->month_list,
                'day_list'                      => $this->day_list,
                'graph_output'                  => $summary_flag,
                'caption_list'                  => $caption_list,
                'summary_data_dispatch'         => $summary_data_dispatch,
                'summary_data_sales_correction' => $summary_data_sales_correction,
                'summary_data_dispatch_share'   => $summary_data_dispatch_share,
                'summary_data_stock'            => $summary_data_stock,
                'error_message'                 => $error_msg
            )
        );
        
    }
}
