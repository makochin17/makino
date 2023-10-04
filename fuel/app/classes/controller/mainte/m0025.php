<?php
/**
 * 得意先情報編集画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0025;
use \Model\Mainte\M0020\M0020;

class Controller_Mainte_M0025 extends Controller_Hybrid {

    protected $format = 'csv';

    // テンプレート定義
    public $template  	= 'template_base';
    private $head     	= 'head';
	private $header   	= 'header';
	private $tree 		= 'tree';
	private $sidemenu 	= 'sidemenu';
	private $footer   	= 'footer';

    /**
    * 画面共通初期設定
    **/
    private function initViewForge($auth_data){

        // サイト設定
        $cnf                                = \Config::load('siteinfo', true);
        $cnf['header_title']                = 'ユニットマスタ更新';
        $cnf['page_id']                     = '[M0025]';
        $cnf['tree']['top']                 = \Uri::base(false);
        $cnf['tree']['management_function'] = 'ユニットマスタ更新';
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

    private function get_info($unit_code) {

        $unit_data = array();
        try {
            //ユニットデータ取得
            $result = M0025::getUnit($unit_code, M0025::$db);
            if (empty($result))return null;
            $unit_data = $result[0];

        } catch (Exception $ex) {
            Log::error($e->getMessage());
            return null;
        }

        $conditions = array(
            'unit_code' => $unit_data['unit_code'],
            'unit_name' => $unit_data['unit_name'],
        );

        return $conditions;
    }

	private function validate_info() {

		// 入力チェック
		$validation = Validation::forge('valid_master');
        $validation->add_callable('myvalidation');
        // ユニット名チェック
        $validation->add('unit_name', 'ユニット名')
            ->add_rule('required')
            // ->add_rule('master_duplicate', 'name', 'm_unit')
        ;
		$validation->run();
		return $validation;
	}

    // 更新処理
    private function update_record($conditions) {

        try {
            DB::start_transaction(M0025::$db);

            // レコード存在チェック
            if (!$result = M0025::getUnit($conditions['unit_code'], M0025::$db)) {
                return Config::get('m_MW0003');
            }

            $error_msg = M0025::update_record($conditions, M0025::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }

            DB::commit_transaction(M0025::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0025::$db);
            var_dump($e->getMessage());
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0006')."');</script>";
        return null;
    }

    // 削除処理
    private function delete_record($unit_code) {

        try {
            DB::start_transaction(M0025::$db);

            // レコード存在チェック
            if (!$result = M0025::getUnit($unit_code, M0025::$db)) {
                return Config::get('m_MW0003');
            }

            // レコード削除（論理）
            $error_msg = M0025::delete_record($unit_code, M0025::$db);
            if (!is_null($error_msg)) {
                DB::rollback_transaction(M0025::$db);
                return $error_msg;
            }

            DB::commit_transaction(M0025::$db);

        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0025::$db);
            Log::error($e->getMessage());
            return Config::get('m_CE0001');
        }

        echo "<script type='text/javascript'>alert('".Config::get('m_MI0007')."');</script>";
        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 初期設定
         */
        $error_msg      = null;
        $conditions 	= array_fill_keys(array(
            'unit_code',
            'unit_name',
        ), '');

        if (!empty(Input::param('back')) && Input::method() == 'POST' && Security::check_token()) {
            // 「戻る」ボタン押下
            // 検索画面へリダイレクト
            Session::delete('m0025_list');
            \Response::redirect(\Uri::create('mainte/m0020'));
        } elseif (!empty(Input::param('update')) && Input::method() == 'POST' && Security::check_token()) {
            // 「更新」ボタン押下

            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            //セッションに値を保持
            Session::set('m0025_list', $conditions);
            // 入力必須項目チェック
            $validation = $this->validate_info();
            $errors     = $validation->error();
            // 入力値チェックのエラー判定
            if (!empty($errors)) {
                foreach($validation->error() as $key => $e) {
                    switch ($key){
                        case 'unit_name':
                            $error_column = 'ユニット名';
                            break;
                    }
                    if ($validation->error()[$key]->rule == 'required') {
                        $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0005'));
                    } elseif ($validation->error()[$key]->rule == 'master_duplicate') {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0024'));
                    } else {
                        // $error_msg = str_replace('XXXXX',$error_column,Config::get('m_CW0007'));
                    }
                    break;
                }
            }

            if (empty($error_msg)) {
                // 更新処理
                $error_msg = $this->update_record($conditions);
            }

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0025_list');
                \Response::redirect(\Uri::create('mainte/m0020'));
            }
        } elseif (!empty(Input::param('delete')) && Input::method() == 'POST' && Security::check_token()) {
            // 「削除」ボタン押下
            if ($cond = Session::get('m0025_list', array())) {
                //セッションの値を設定
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            foreach ($conditions as $key => $val) {
                $conditions[$key] = Input::param($key, ''); // 検索項目
            }

            //セッションに値を保持
            Session::set('m0025_list', $conditions);
            //得意先データ削除
            $error_msg = $this->delete_record($conditions['unit_code']);

            if (empty($error_msg)) {
                // 検索画面へリダイレクト
                Session::delete('m0025_list');
                \Response::redirect(\Uri::create('mainte/m0020'));
            }
        } else {
            if ($cond = Session::get('m0025_list', array())) {
                foreach ($cond as $key => $val) {
                    $conditions[$key] = $val;
                }
            }
            if (!empty(Input::param('processing_division', '')) && Input::param('processing_division', '') != 1) {
                //ユニット検索画面から呼び出された時
                $unit_code = Input::param('unit_code', '');
                if (!empty($unit_code)) {
                    $result = $this->get_info($unit_code);
                    if (empty($result)) {
                        $error_msg = Config::get('m_MW0003');
                    } else {
                        $conditions = $result;
                    }
                }
            }

            Session::set('m0025_list', $conditions);
        }

        $this->template->content = View::forge(AccessControl::getActiveController(),
            array(
                'error_message'             => $error_msg,
                'data'                      => $conditions,
            )
        );
    }
}
