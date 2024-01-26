<?php
/**
 * 保管場所高さ情報入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0030\M0033;
use \Model\Search\S0031;

class Controller_Mainte_M0033 extends Controller_Hybrid {

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

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){
        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '保管場所高さ情報入力';
        $cnf['page_id']                     = '[M0033]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '保管場所高さ情報入力';
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
        $sidemenu->login_user_name          = AuthConfig::getAuthConfig('name');
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

        // テンプレートに渡す定義
        $this->template->head         = $head;
        $this->template->header       = $header;
        $this->template->sidemenu     = $sidemenu;
        $this->template->footer       = $footer;
        $this->template->tree         = $tree;

        $paging_config = PagingConfig::getPagingConfig("UIM0011", C0010::$db);
        $this->pagenation_config['num_links'] = $paging_config['display_link_number'];
        $this->pagenation_config['per_page'] = $paging_config['display_record_number'];
        $this->pagenation_config['per_page'] = 50;

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
        //  Response::redirect(\Uri::create('top'));
        //}
        $this->initViewForge($auth_data);
    }

    private function validate_info() {

        // 入力チェック
        $validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 保管場所高さチェック
        $validation->add('storage_height_name', '保管場所高さ名')
            ->add_rule('required')
        ;
        $validation->run();
        return $validation;
    }

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(M0033::$db);

            $error_msg = M0033::create_record($conditions, M0033::$db);
            if (!is_null($error_msg)) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(M0033::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0033::$db);

            $end_msg = Config::get('m_MI0005');
            echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0033::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        return null;
    }

   // 更新処理
   private function update_record($conditions) {

       try {
           DB::start_transaction(M0033::$db);

           $error_msg = M0033::update_record($conditions, M0033::$db);
           if (!is_null($error_msg)) {
               return $error_msg;
           }

           DB::commit_transaction(M0033::$db);
       } catch (Exception $e) {
           // トランザクションクエリをロールバックする
           DB::rollback_transaction(M0033::$db);
           // return $e->getMessage();
           return Config::get('m_CE0001');
       }
       echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
       return null;
   }

   // 削除処理
   private function delete_record($conditions) {

       try {
           DB::start_transaction(M0033::$db);

           $error_msg = M0033::delete_record($conditions, M0033::$db);
           if (!is_null($error_msg)) {
               return $error_msg;
           }

           DB::commit_transaction(M0033::$db);
       } catch (Exception $e) {
           // トランザクションクエリをロールバックする
           DB::rollback_transaction(M0033::$db);
           // return $e->getMessage();
           return Config::get('m_CE0001');
       }

       echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
       return null;
   }

    public function action_index() {

        Config::load('message');
        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg      = null;
        $conditions     = array_fill_keys(array(
            'storage_height_id',
            'storage_height_name',
        ), '');

        if (!empty(Input::param('back')) && Security::check_token()) {
            // 「戻る」ボタン押下
            // 検索画面へリダイレクト
            Session::delete('m0033_list');
            \Response::redirect(\Uri::create('mainte/m0030'));
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            if ($cond = Session::get('m0033_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }
            // セッションに検索条件を設定
            Session::set('m0033_list', $conditions);

            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'storage_height_name':
                            $error_column = '保管場所高さ名';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } elseif ($validation->error()[$key]->rule == 'is_half_katakana') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0017'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }

            if (empty($error_msg)) {
                // 登録処理
                $error_msg = $this->create_record($conditions);
            }

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0033_list');
                \Response::redirect(\Uri::create('mainte/m0033'));
            }
        } elseif (Input::param('processing_division') == 3 && Input::method() == 'POST' && Security::check_token()) {
            // 「削除」ボタン押下
            if ($cond = Session::get('m0033_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            //セッションに値を保持
            Session::set('m0033_list', $conditions);

            //庸車先データ削除
            $error_msg = $this->delete_record($conditions['storage_height_id']);

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0033_list');
                \Response::redirect(\Uri::create('mainte/m0033'));
            }
        } else {
            if ($cond = Session::get('m0033_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }

            //セッションに値を保持
            Session::set('m0033_list', $conditions);
        }

        /**
         * ページング設定&検索実行
         */
        if (!$total = M0033::getSearch(true, 'all', $conditions, null, null, M0033::$db)) {
            $total = 0;
        }
        //初期表示かつ前回表示時のページ数を保持していれば、ページネーションのカレントページを設定
        $page = Session::get('m0033_page');
        if (empty(Input::get('p')) && !empty($page)) {
            $this->pagenation_config += array('current_page' => $page);
        }

        $this->pagenation_config        += array('uri' => \Uri::create(AccessControl::getActiveController()), 'total_items' => $total);
        $pagination                     = Pagination::forge('mypagination', $this->pagenation_config);
        $limit                          = $pagination->per_page;
        $offset                         = $pagination->offset;
        $list_data                      = array();

        //ページネーションのページ数をセッションに保存
        Session::set('m0033_page', Input::get('p'));

        $list_data                      = array();
        if ($total > 0) {
            $list_data                  = M0033::getSearch(false, 'all', $conditions, $offset, $limit, M0033::$db);
        } elseif (Input::method() == 'POST' && Security::check_token() && !isset($error_msg)) {
            $error_msg = Config::get('m_CI0003');
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'total'                 => $total,
                'data'                  => $conditions,
                'list_data'             => $list_data,
                'offset'                => $offset,
                'error_message'         => $error_msg,
            )
        );
        $this->template->content->set_safe('pager', $pagination->render());

    }
}
