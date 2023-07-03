<?php
/**
 * 配車情報一括登録（共配便）画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Allocation\D1020;

class Controller_Allocation_D1020 extends Controller_Hybrid {

    protected $format = 'xlsx';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 共配便配車情報雛形リスト
    private $file_list = array();
    
    // 課リスト
    private $division_list = array();

    // Uploadクラスの設定
    private $upload_config = array(
        'randomize'     => true,
        'ext_whitelist' => array('xlsx', 'xls'),
    );

    public function is_restful()
    {
        /**
         * Actionが index かつ
         * GET 変数に exceldownload がある場合は
         * Restful とする
         */
        switch (Request::main()->action) {
            case 'export':
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
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '配車情報一括登録（共配便）';
        $cnf['page_id']                     = '[D1020]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '配車情報一括登録（共配便）';
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

        $ary_footer_js = array(
            'common/jquery.min.popup.js',
            'common/jqModal.js',
            'common/jquery.min.js',
            'allocation/d1020.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 配車区分取得
        $this->category_list            = GenerateList::getDispatchCategoryList(false);
        
        // 課リスト取得
        $this->division_list            = GenerateList::getDivisionList(false, D1020::$db);
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

    public function action_index() {

        Config::load('message');
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $importdata     = array();
        $file_list      = $this->file_list;
        $conditions     = array_fill_keys(array(
            'kind',
            'division',
        ), '');

        foreach ($conditions as $key => $val) {
            $conditions[$key] = Input::param($key, ''); // 検索項目
        }

        if (Input::method() == 'POST') {

            // タイムアウトを一時的に解除
            ini_set('max_execution_time', 0);
            // 最大メモリー数を増幅
            ini_set('memory_limit', '2048M');

            // ファイル名設定
            $file_name = 'no_upload.xlsx';

            // ファイルアップロード
            \Upload::process($this->upload_config);
            if (\Upload::is_valid()) {
                $files = \Upload::get_files();
                if (isset($files[0]) && $files[0]['file'] != '') {

                    // ファイル名設定
                    $file_name = '【登録結果】'.date('Ymd').'_'.$files[0]['name'];

                    $excel_data    = D1020::get($files[0]['file']);
                    $excel_type    = $excel_data['excel_type'];
                    $header        = $excel_data['header'];
                    $body          = $excel_data['data'];
                    $data          = array();
                    if (!in_array($excel_type, $this->upload_config['ext_whitelist'])) {
                        $error_msg = str_replace('XXXXX','エクセルファイル（拡張子：xlsx）',Config::get('m_CW0019'));
                    } else {
                        if (empty($header)) {
                            $error_msg = Config::get('m_CW0020');
                        } else {
                            if (!empty($body)) {
                                foreach ($body as $val) {
                                    $data[]     = array_merge(array('配車区分コード' => $conditions['kind']), array_combine($header, $val));
                                }
                            }
                            if (!empty($data)) {
                                $importdata     = D1020::import('insert', $data, $conditions['kind'],$conditions['division'], $error_msg, D1020::$db);
                            } else {
                                $error_msg = Config::get('m_CW0008');
                            }
                        }
                    }
                }
            } else {
                $error_msg = \Config::get('m_CE0009');
            }

            /**
             * Excel ファイルへの書き出し
             */
            if (empty($error_msg)) {
                $title = '配車表';

                $output_data[]   = $header;
                $output_data     = array_merge($output_data, $importdata);
                // ブラウザへの指定
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$file_name.'"');
                header('Cache-Control: max-age=0');

                $content = D1020::create_boder($this->format, $title, $output_data);
                // $content = D1020::sp_create_boder($this->format, $title, $output_data);

                // 同じ画面にリダイレクト
                Response::redirect(\Uri::create('allocation/d1020'));
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                  => $conditions,

                'category_list'         => $this->category_list,
                'division_list'         => $this->division_list,

                'list_url'              => \Uri::create(\Uri::create('allocation/d1010')),
                'export_url'            => \Uri::create(\Uri::create('allocation/d1020/export')),

                'error_message'         => $error_msg,
            )
        );
    }

    public function action_export() {

        Config::load('message');
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $importdata     = array();
        $kind           = Input::param('kind', '');
        $division       = Input::param('division', '');

        if (Input::method() == 'POST') {

            // タイムアウトを一時的に解除
            ini_set('max_execution_time', 0);
            // 最大メモリー数を増幅
            ini_set('memory_limit', '2048M');

            // ファイル名設定
            $file_name = 'no_upload.xlsx';

            // ファイルアップロード
            \Upload::process($this->upload_config);
            if (\Upload::is_valid()) {
                $files = \Upload::get_files();
                if (isset($files[0]) && $files[0]['file'] != '') {

                    // ファイル名設定
                    $file_name = date('Ymd').'_'.$files[0]['name'];

                    $excel_data    = D1020::get($files[0]['file']);
                    $excel_type    = $excel_data['excel_type'];
                    $header        = $excel_data['header'];
                    $body          = $excel_data['data'];
                    $data          = array();
                    if (!in_array($excel_type, $this->upload_config['ext_whitelist'])) {
                        $error_msg = str_replace('XXXXX','エクセルファイル（拡張子：xlsx）',Config::get('m_CW0019'));
                    } else {
                        if (empty($header)) {
                            $error_msg = Config::get('m_CW0020');
                        } else {
                            if (!empty($body)) {
                                foreach ($body as $val) {
                                    $data[]     = array_merge(array('配車区分コード' => $kind), array_combine($header, $val));
                                }
                            }
                            if (!empty($data)) {
                                $importdata     = D1020::import('insert', $data, $kind, $division, $error_msg, D1020::$db);
                            } else {
                                $error_msg = Config::get('m_CW0008');
                            }
                        }
                    }
                }
            } else {
                $error_msg = \Log::error(\Config::get('m_CE0009'));
            }

            /**
             * Excel ファイルへの書き出し
             */
            if (empty($error_msg)) {
                $title = '配車表';

                $output_data[]   = $header;
                $output_data     = array_merge($output_data, $importdata);
                // ブラウザへの指定
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$file_name.'"');
                header('Cache-Control: max-age=0');

                $content = D1020::create_boder($this->format, $title, $output_data);
                // $content = D1020::sp_create_boder($this->format, $title, $output_data);

            }
        }
        // 同じ画面にリダイレクト
        Response::redirect(\Uri::create('allocation/d1020'));
        // return $this->response(true);
    }

}
