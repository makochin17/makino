<?php
/**
 * 請求情報一括登録（共配便）画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Bill\B1020;

class Controller_Bill_B1020 extends Controller_Hybrid {

    protected $format = 'xlsx';

    // テンプレート定義
    public $template    = 'template_base';
    private $head       = 'head';
    private $header     = 'header';
    private $tree       = 'tree';
    private $sidemenu   = 'sidemenu';
    private $footer     = 'footer';

    // 共配便請求情報雛形リスト
    private $file_list = array();

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
        $cnf['header_title']                = '請求情報一括登録（共配便）';
        $cnf['page_id']                     = '[B1020]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '請求情報一括登録（共配便）';
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
            'bill/b1020.js',
        );
        Asset::js($ary_footer_js, array(), 'footer_js', false);

        // テンプレートに渡す定義
        $this->template->head           = $head;
        $this->template->header         = $header;
        $this->template->tree           = $tree;
        $this->template->sidemenu       = $sidemenu;
        $this->template->footer         = $footer;

        // 請求区分取得
        $this->category_list            = GenerateList::getDispatchCategoryList(false);
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

                    $excel_data    = B1020::get($files[0]['file']);
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
                                    $data[]     = array_merge(array('請求区分コード' => $conditions['kind']), array_combine($header, $val));
                                }
                            }
                            if (!empty($data)) {
                                $importdata     = B1020::import('insert', $data, $conditions['kind'], $error_msg, B1020::$db);
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
                $title = '請求表';

                $output_data[]   = $header;
                $output_data     = array_merge($output_data, $importdata);
                // ブラウザへの指定
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$file_name.'"');
                header('Cache-Control: max-age=0');

                $content = B1020::create_boder($this->format, $title, $output_data);
                // $content = B1020::sp_create_boder($this->format, $title, $output_data);

                // 同じ画面にリダイレクト
                Response::redirect(\Uri::create('bill/b1020'));
            }
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'data'                  => $conditions,

                'category_list'         => array('1' => '未分類'),

                'list_url'              => \Uri::create(\Uri::create('bill/b1010')),
                'export_url'            => \Uri::create(\Uri::create('bill/b1020/export')),

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

                    $excel_data    = B1020::get($files[0]['file']);
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
                                    $data[]     = array_combine($header, $val);
                                }
                            }
                            if (!empty($data)) {
                                $importdata     = B1020::import('insert', $data, $kind, $error_msg, B1020::$db);
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
                $title = '請求表';

                $output_data[]   = $header;
                $output_data     = array_merge($output_data, $importdata);
                // ブラウザへの指定
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$file_name.'"');
                header('Cache-Control: max-age=0');

                $content = B1020::create_boder($this->format, $title, $output_data);
                // $content = B1020::sp_create_boder($this->format, $title, $output_data);

            }
        }
        // 同じ画面にリダイレクト
        Response::redirect(\Uri::create('bill/b1020'));
        // return $this->response(true);
    }

}
