<?php
/**
 * 保管場所検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Location\L0010;

class Controller_Location_L0010 extends Controller_Hybrid {

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

    // 保管場所倉庫リスト
    private $storage_warehouse_list     = array();

    // 保管場所列リスト
    private $storage_column_list        = array();

    // 保管場所奥行リスト
    private $storage_depth_list         = array();

    // 保管場所高さリスト
    private $storage_height_list        = array();

    // ユーザ情報
    private $user_authority             = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '保管場所検索';
        $cnf['page_id']                     = '[L0010]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '保管場所検索';
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
            'font-awesome/css/font-awesome.min.css'
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
            'location/l0010.js',
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

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIL0010", L0010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

        // 保管場所倉庫リスト
        $this->storage_warehouse_list   = GenerateList::getStorageWarehouseList(false, L0010::$db);
        // 保管場所列リスト取得
        $this->storage_column_list      = GenerateList::getStorageColumnList(false, L0010::$db);
        // 保管場所奥行リスト取得
        $this->storage_depth_list       = GenerateList::getStorageDepthList(false, L0010::$db);
        // 保管場所高さリスト取得
        $this->storage_height_list      = GenerateList::getStorageHeightList(false, L0010::$db);

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
        $this->initViewForge($auth_data);
    }

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $list_data      = array();
        $total          = L0010::getTotalCnt(L0010::$db);
        $storage_list   = L0010::getLocationWarehouse(L0010::$db);

        if (!empty($storage_list)) {
            foreach ($storage_list as $key => $val) {
                $list_data[] = array(
                    'warehouse_cnt'             => $val['warehouse_cnt'],
                    'storage_warehouse_id'      => $val['storage_warehouse_id'],
                    'storage_warehouse_name'    => $val['storage_warehouse_name'],
                    'stock_cnt'                 => L0010::getLocationDetail('count', $val['storage_warehouse_id'], null, null, null, L0010::$db),
                );
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                         => $total,
                'list_data'                     => $list_data,

                'userinfo'                      => AuthConfig::getAuthConfig('all'),
                // 保管場所倉庫リスト
                'storage_warehouse_list'        => $this->storage_warehouse_list,
                // 保管場所列リスト
                'storage_column_list'           => $this->storage_column_list,
                // 保管場所奥行リスト
                'storage_depth_list'            => $this->storage_depth_list,
                // 保管場所高さリスト
                'storage_height_list'           => $this->storage_height_list,

                'error_message'                 => $error_msg,
            )
        );
    }
}
