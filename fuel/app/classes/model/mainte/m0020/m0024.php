<?php
namespace Model\Mainte\M0020;
use \Model\Mainte\M0020\M0020;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Date;
use \Log;
use \Config;

class M0024 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 得意先登録
     */
    public static function create_record($conditions, $db) {

        $data = array(
            'schedule_type' => $conditions['schedule_type'],
            'disp_flg'      => $conditions['disp_flg'],
            'unit_name'     => $conditions['unit_name'],
            );

        //得意先マスタ登録
        $result = self::addUnit($data, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','ユニット',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
            return str_replace('XXXXX','ユニット',Config::get('m_ME0006'));
        }

        return null;
    }

    /**
     * ユニットマスタ登録
     */
    public static function addUnit($items, $db) {

        // 項目セット
        $set = array(
            'schedule_type' => $items['schedule_type'],
            'disp_flg'      => $items['disp_flg'],
            'name'          => $items['unit_name'],
            'start_date'    => Date::forge()->format('mysql_date'),
            'end_date'      => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));

        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'name = VALUES(name)';

        // 登録実行
        $stmt = \DB::insert('m_unit')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {

        $user_master_id   = AuthConfig::getAuthConfig('user_id');
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