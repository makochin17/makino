<?php
namespace Model\Mainte\M0030;
use \Model\Mainte\M0030\M0030;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Date;
use \Log;
use \Config;

class M0034 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 庸車先登録
     */
    public static function create_record($conditions, $db) {

        //存在チェック
        if ($result = M0030::getStorageLocationBySubCode($conditions['storage_column_id'], $conditions['storage_depth_id'], $conditions['storage_height_id'], $db)) {
            return Config::get('m_MW0004');
        }

        $data = array(
            'storage_column_id'     => $conditions['storage_column_id'],
            'storage_depth_id'      => $conditions['storage_depth_id'],
            'storage_height_id'     => $conditions['storage_height_id'],
            );

        //庸車先マスタ登録
        $result = self::addStorageLocation($data, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','保管場所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
            return str_replace('XXXXX','保管場所',Config::get('m_ME0006'));
        }

        return null;
    }

    /**
     * 庸車先マスタ登録
     */
    public static function addStorageLocation($items, $db) {

        // 項目セット
        $set = array(
            'storage_column_id'     => $items['storage_column_id'],
            'storage_depth_id'      => $items['storage_depth_id'],
            'storage_height_id'     => $items['storage_height_id'],
            'start_date'            => Date::forge()->format('mysql_date'),
            'end_date'              => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));

        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'storage_column_id = VALUES(storage_column_id),'
                . 'storage_depth_id = VALUES(storage_depth_id),'
                . 'storage_height_id = VALUES(storage_height_id),'
                . 'start_date = VALUES(start_date),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        // 登録実行
        $stmt = \DB::insert('rel_storage_location')->set($set);
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