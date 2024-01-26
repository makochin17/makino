<?php
/**
 * 車種検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Search\S0040;

class Controller_Search_S0041 extends Controller_Hybrid {

    protected $format = 'json';

    // テンプレート定義
    public $template    = 'template_base_popup';
    private $head       = 'head';

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

        $head                               = View::forge($this->head);
        $head->title                        = $cnf['header_title'];

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
            'search/s0040.js',
        );
        Asset::js($ary_header_js, array(), 'header_js', false);

        // テンプレートに渡す定義
        $this->template->head = $head;

        // ページング設定値取得
        $paging_config = PagingConfig::getPagingConfig("UIS0040", S0040::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];

        // 保管場所倉庫リスト
        $this->storage_warehouse_list   = GenerateList::getStorageWarehouseList(false, S0040::$db);
        // 保管場所列リスト取得
        $this->storage_column_list      = GenerateList::getStorageColumnList(false, S0040::$db);
        // 保管場所奥行リスト取得
        $this->storage_depth_list       = GenerateList::getStorageDepthList(false, S0040::$db);
        // 保管場所高さリスト取得
        $this->storage_height_list      = GenerateList::getStorageHeightList(false, S0040::$db);

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
        $location_id    = Input::param('location_id', '');
        $warehouse_id   = Input::param('warehouse_id', '');
        $total          = S0040::getTotalCnt(S0040::$db);
        $storage_list   = S0040::getLocationColumn($warehouse_id, S0040::$db);

        if (!empty(Input::param('cancel')) && Security::check_token()) {
            // キャンセルボタンが押下された場合の処理
            Session::set('select_cancel', true);
            Session::delete('s0041_list');
            echo "<script type='text/javascript'>window.opener[window.name]();</script>";
            echo "<script type='text/javascript'>window.close();</script>";
        }

        if (!empty($storage_list)) {
            foreach ($storage_list as $key => $val) {
                $list_data[] = array(
                    'column_cnt'                => $val['column_cnt'],
                    'storage_column_id'         => $val['storage_column_id'],
                    'storage_column_name'       => $val['storage_column_name'],
                    'stock_cnt'                 => S0040::getLocationDetail('count', $warehouse_id, $val['storage_column_id'], null, null, S0040::$db),
                );
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                         => $total,
                'list_data'                     => $list_data,
                'location_id'                   => $location_id,
                'warehouse_id'                  => $warehouse_id,

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
