<?php
namespace Model\Auth;

use \Model\Init;
use \Model\AccessControl;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Common\SystemConfig;

class C0010 extends \Model {

    public static $db       = 'MAKINO';

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
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'full_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                array('m.user_id', 'user_id'),
                array('m.user_authority', 'user_authority'),
                array('m.lock_status', 'lock_status')
                )

        // テーブル
        ->from(array('m_member', 'm'))
        // ログインユーザーID
        ->where('m.user_id', $user_id)
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 社員マスタレコード取得
     */
    public static function getMemberByCode($member_code, $db) {

        // テーブル
        return \DB::select()
        ->from(array('m_member', 'm'))
        // ログインユーザーID
        ->where('m.member_code', $member_code)
        // 適用開始日
        ->where('m.start_date', '<=', date("Y-m-d"))
        // 適用終了日
        ->where('m.end_date', '>', date("Y-m-d"))
        // 検索実行
        ->execute($db)->current();
    }

    /**
     * 社員マスタパスワード誤り回数カウントアップ
     */
    public static function updPasswordErrorCountUp($member_code, $db) {

        $data                   = self::getMemberByCode($member_code, $db);
        $password_error_count   = ($data['password_error_count'] + 1);

        // テーブル
        $stmt = \DB::update(array('m_member', 'm'))
        // 項目
        ->set(array('m.password_error_count' => $password_error_count))
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

        if (!$user_name = AuthConfig::getAuthConfig('user_id')) {
            $user_name = AuthConfig::getAuthConfig('user_name');
        }

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => $user_name,
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => $user_name
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => $user_name
            );
            break;
        }
        return $data;
    }

}