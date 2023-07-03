<?php
namespace Model\Common;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Date;

class OpeLog extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 操作ログ出力
     * $message_id メッセージID
     * $message メッセージ本文
     * $supplement_info 補足情報
     */
    public static function addOpeLog($message_id, $message, $supplement_info, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        $user_master_id   = AuthConfig::getAuthConfig('user_id');

        // 項目セット
        $set = array(
            'output_datetime'   => Date::forge()->format('mysql'),
            'user_id'           => $user_master_id,
            'message_id'        => \DB::expr('HEX(AES_ENCRYPT("'.$message_id.'","'.$encrypt_key.'"))'),
            'message'           => \DB::expr('HEX(AES_ENCRYPT("'.$message.'","'.$encrypt_key.'"))'),
            'supplement_info'   => \DB::expr('HEX(AES_ENCRYPT("'.$supplement_info.'","'.$encrypt_key.'"))')
            );

        // 登録実行
        $stmt = \DB::insert('t_operation_log')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            return true;
        }
        return false;
    }

}