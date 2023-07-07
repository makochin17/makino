<?php
/**
 * @author M.Komine
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;

class Controller_ControlUser extends Controller_Rest {

	// テンプレート定義
	public function before() {
		parent::before();

	}

    // 登録処理
    private function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = M0010::$db;
        }

        try {
            DB::start_transaction(M0010::$db);

	        // レコード存在チェック
	        $result = M0010::getMember($conditions['member_code'], $db);
	        if (count($result) == 1) {
	            return \Config::get('m_MW0004');
	        }

	        // システム設定取得
	        $password = 'system';

	        if (!empty($conditions['user_id'])) {
	            // ログインユーザ名重複チェック
	            $result = M0010::getMemberLoginUser($conditions['user_id'], $db);

	            if (count($result) == 1) {
	                return \Config::get('m_MW0012');
	            }

	            // Authログインユーザ登録
	            if (!AuthConfig::CreateLoginUser($conditions['user_id'], $password, $conditions)) {
	                return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0001'));
	            }
	        }

	        // レコード登録
	        $result = M0010::addMember($conditions, $db);
	        if (!$result) {
	            return \Config::get('m_ME0003');
	        }

            DB::commit_transaction(M0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(M0010::$db);
            return $e->getMessage();
            return Config::get('m_CE0001');
        }
        return null;
    }

	public function action_index() {

        $conditions 	= array(
        	'member_code'			=> '999999999',
            'full_name'				=> '小峰誠',
            'name_furigana'			=> 'こみねまこと',
            'mail_address'			=> 'komine@golf-force.net',
            'user_id'				=> 'system',
            'user_authority'		=> '1',
            'password_limit'		=> '9999/12/31',
            'password_error_count'	=> '0',
            'lock_status'			=> '0',
            'start_date'			=> '1900/01/01',
            'end_date'				=> '9999/12/31',
        );

		$error_msg = $this->create_record($conditions, M0010::$db);
		if (!empty($error_msg)) {
	        echo "<script type='text/javascript'>alert('".$error_msg."');</script>";
		} else {
	        echo "<script type='text/javascript'>alert('システム管理者を作成しました');</script>";
		}

		// \Response::redirect(\Uri::base(false));
	}

}
