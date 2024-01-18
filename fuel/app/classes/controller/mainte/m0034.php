<?php
/**
 * 保管場所情報入力画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030\M0034;
use \Model\Mainte\M0030\M0030;

class Controller_Mainte_M0034 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    // 保管場所倉庫リスト
    private $storage_warehouse_list = array();

    // 保管場所列リスト
    private $storage_column_list = array();

    // 保管場所奥行リスト
    private $storage_depth_list = array();

    // 保管場所高さリスト
    private $storage_height_list = array();

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = '保管場所情報入力';
        $cnf['page_id']                     = '[M0034]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = '保管場所情報入力';
        $cnf['tree']['page_url']            = \Uri::create(AccessControl::getActiveController());
        $cnf['tree']['page_title']          = '';

        $header                             = View::forge($this->header);
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
            ''
        );
        Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

        //PCorスマホで読み込むCSSを変更
        $ary_style_css = array(
            'font-awesome/css/font-awesome.min.css',
            'modal/dialog.css',
        );
        Asset::css($ary_style_css, array(), 'style_css', false);

        $ary_header_js = array(
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

        // 保管場所倉庫リスト取得
        $this->storage_warehouse_list   = GenerateList::getStorageWarehouseList(true, M0034::$db);
        // 保管場所列リスト取得
        $this->storage_column_list      = GenerateList::getStorageColumnList(true, M0034::$db);
        // 保管場所奥行リスト取得
        $this->storage_depth_list       = GenerateList::getStorageDepthList(true, M0034::$db);
        // 保管場所高さリスト取得
        $this->storage_height_list      = GenerateList::getStorageHeightList(true, M0034::$db);
    }

	public function before() {
		parent::before();
		// ログインチェック
		if(!Auth::check()) {
			Response::redirect(\Uri::base(false));
		}

		// 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');;

		// ページアクセス権判定
		//if (!AccessControl::isPagePermission($auth_data['permission_level'])) {
		//	Response::redirect(\Uri::create('top'));
		//}
		$this->initViewForge($auth_data);
	}

	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // 保管場所倉庫チェック
        $validation->add('storage_warehouse_id', '保管場所倉庫')
            ->add_rule('required_select')
        ;
        // 保管場所列チェック
        $validation->add('storage_column_id', '保管場所列')
            ->add_rule('required_select')
        ;
        // 保管場所奥行チェック
        $validation->add('storage_depth_id', '保管場所奥行')
            ->add_rule('required_select')
        ;
        // 保管場所高さチェック
        $validation->add('storage_height_id', '保管場所高さ')
            ->add_rule('required_select')
        ;

		$validation->run();
		return $validation;
	}

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(M0034::$db);

            $error_msg = M0034::create_record($conditions, M0034::$db);
            if (!is_null($error_msg)) {
                // トランザクションクエリをロールバックする
                DB::rollback_transaction(M0034::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0034::$db);

            $end_msg = Config::get('m_MI0005');
            echo "<script type='text/javascript'>alert('".$end_msg."');</script>";
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0034::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        return null;
    }

//    // 更新処理
//    private function update_record($conditions) {
//
//        try {
//            DB::start_transaction(M0034::$db);
//
//            $error_msg = M0034::update_record($conditions, M0034::$db);
//            if (!is_null($error_msg)) {
//                return $error_msg;
//            }
//
//            DB::commit_transaction(M0034::$db);
//        } catch (Exception $e) {
//            // トランザクションクエリをロールバックする
//            DB::rollback_transaction(M0034::$db);
//            // return $e->getMessage();
//            return Config::get('m_CE0001');
//        }
//        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
//        return null;
//    }
//
//    // 削除処理
//    private function delete_record($conditions) {
//
//        try {
//            DB::start_transaction(M0034::$db);
//
//            $error_msg = M0034::delete_record($conditions, M0034::$db);
//            if (!is_null($error_msg)) {
//                return $error_msg;
//            }
//
//            DB::commit_transaction(M0034::$db);
//        } catch (Exception $e) {
//            // トランザクションクエリをロールバックする
//            DB::rollback_transaction(M0034::$db);
//            // return $e->getMessage();
//            return Config::get('m_CE0001');
//        }
//
//        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
//        return null;
//    }

    public function action_index() {

        Config::load('message');

        /**
         * 初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
            'storage_warehouse_id',
        	'storage_column_id',
            'storage_depth_id',
        	'storage_height_id',
        ), '');

        if (!empty(Input::param('input_clear')) && Input::method() == 'POST' && Security::check_token()) {
            // 入力項目クリアボタンが押下された場合の処理
            Session::delete('m0034_list');
            \Response::redirect(\Uri::create('mainte/m0034'));
        } elseif (!empty(Input::param('cancel')) && Input::method() == 'POST' && Security::check_token()) {
            // 「キャンセル」ボタン押下
            // 検索画面へリダイレクト
            Session::delete('m0034_list');
            \Response::redirect(\Uri::create('mainte/m0030'));
        } elseif (!empty(Input::param('execution')) && Input::method() == 'POST' && Security::check_token()) {
            // 確定ボタンが押下された場合の処理

            if ($cond = Session::get('m0034_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            // セッションに検索条件を設定
            Session::set('m0034_list', $conditions);

            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'storage_warehouse_id':
                            $error_column = '保管場所倉庫';
                            break;
                        case 'storage_column_id':
                            $error_column = '保管場所列';
                            break;
                        case 'storage_depth_id':
                            $error_column = '保管場所奥行';
                            break;
                        case 'storage_height_id':
                            $error_column = '保管場所高さ';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'valid_strings') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0006'));
                    } elseif ($validation->error()[$key]->rule == 'required_select') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0025'));
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
                Session::delete('m0034_list');
                \Response::redirect(\Uri::create('mainte/m0030'));
            }
        } else {
            if ($cond = Session::get('m0034_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }

            Session::set('m0034_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'             => $error_msg,
                'data'                      => $conditions,
                'storage_warehouse_list'    => $this->storage_warehouse_list,
                'storage_column_list'       => $this->storage_column_list,
                'storage_depth_list'        => $this->storage_depth_list,
                'storage_height_list'       => $this->storage_height_list,
            )
        );
    }

}
