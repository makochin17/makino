<?php
namespace Model\Auth;

use \Model\Init;
use \Model\AccessControl;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;

class C0030 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * ログインユーザー名取得
     */
    public static function getLoginUserData($type = 'all') {

        return AuthConfig::getAuthConfig($type);

    }

    /**
     * パスワード有効日数取得
     */
    public static function getPasswordLimit($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return SystemConfig::getSystemConfig('password_limit', $db);

    }

    /**
     * ユーザパスワード有効期限更新
     */
    public static function updPasswordLimit($member_code, $password_limit, $db) {

        // テーブル
        $stmt = \DB::update(array('m_member', 'm'))
        // 項目
        ->set(array('password_limit' => $password_limit))
        // ログインユーザーID
        ->where('m.member_code', $member_code)
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"));
        // 検索実行
        $res = $stmt->execute($db);

        if ($res === false) {
            return false;
        }
        return true;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 社員マスタレコード取得
     */
    public static function getMemberByUserId($user_id, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.member_code', 'member_code'),
                array('m.division_code', 'division_code'),
                array('m.position_code', 'position_code'),
                array('m.car_code', 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'full_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array('m.user_id', 'user_id'),
                array('m.user_authority', 'user_authority'),
                array('m.lock_status', 'lock_status')
                )

        // テーブル
        ->from(array('m_member', 'm'))
        // ログインユーザーID
        ->where(\DB::expr('AES_DECRYPT(UNHEX(m.user_id),"'.$encrypt_key.'")'), $user_id)
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }


    /**
     * 社員マスタパスワード誤り回数リセット
     */
    public static function updPasswordErrorCountReset($member_code, $db) {

        // テーブル
        $stmt = \DB::update(array('m_member', 'm'))
        // 項目
        ->set(array('m.password_error_count' => '0'))
        // ログインユーザーID
        ->where('m.member_code', $member_code)
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"));
        // 検索実行
        $res = $stmt->execute($db);

        if ($res === false) {
            return false;
        }
        return true;
    }

    /**
     * 社員マスタパスワード有効期限判定
     * 戻り値： データ存在:有効 データ無し:無効
     */
    public static function getMemberPasswordDateActive($member_code, $db) {

        // テーブル
        return \DB::select()
        ->from(array('m_member', 'm'))
        // ログインユーザーID
        ->where('m.member_code', $member_code)
        // パスワード有効期限
        ->where('m.password_limit', '>=', date("Y-m-d"))
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"))
        // 検索実行
        ->execute($db)->current();
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {

        //$auth_data        = Init::get('auth_data');
        //$user_master_id   = $auth_data['id'];
        $user_master_id   = '00000';
        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => Date::forge()->format('mysql'),
                'create_user'       => $user_master_id,
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        }
        return $data;
    }

}