<?php

namespace Model;

class Init extends \Model {

	public static $control_data;
	public static $auth_data;
	/**
	 * 表示系設定
	 */
	public static function getInitialize($ary_data = array()){

		// MasterCache::setRedisDB(); // Redis初期化設定
		self::$control_data = self::getControl();

		// ユーザーログイン情報
		$auth_id = \Auth::get_user_id();
		self::$auth_data = User::getLoginUserData($auth_id, '', User::$db_bengo);
		\Session::set('auth_data', self::$auth_data);

		return true;
	}

	public static function getControl() {
		return mControl::getControl();
	}

	public static function get($val) {
		return self::${$val};
	}

	public static function setSessionAuthData() {
		$auth_id 	= \Auth::get_user_id();
		$auth_data 	= User::getLoginUserData($auth_id, '', User::$db_bengo);
		\Session::set('auth_data', $auth_data);

		return true;
	}

}