<?php
namespace Model\Logistics;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Excel\Data;

class L0030 extends \Model {

    public static $db               = 'MAKINO';

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    //=========================================================================//
    //==============================   Excelデータ  =============================//
    //=========================================================================//
    /**
     * 入庫データExcelに書き込み
     */
    public static function setExcelData($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }


        return Data::setOutboundInstructions($data, $db);
    }

    //=========================================================================//
    //==============================   取得データ  =============================//
    //=========================================================================//
    /**
     * ID別入出庫データ取得(複数)
     */
    public static function getLogisticsById($logistics_ids, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($logistics_ids)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.id', 'logistics_id'),
                array(\DB::expr("DATE_FORMAT(t.delivery_schedule_date,'%Y-%m-%d')"), 'delivery_schedule_date'),
                array(\DB::expr("DATE_FORMAT(t.delivery_date,'%Y-%m-%d')"), 'delivery_date'),
                array(\DB::expr("DATE_FORMAT(t.receipt_date,'%Y-%m-%d')"), 'receipt_date'),
                array('t.delivery_schedule_time', 'delivery_schedule_time'),
                array('t.delivery_time', 'delivery_time'),
                array('t.receipt_time', 'receipt_time'),
                array('t.location_id', 'location_id'),
                array('t.car_id', 'car_id'),
                array('t.car_code', 'car_code'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.car_name),'".$encrypt_key."')"), 'car_name'),
                array('t.customer_code', 'customer_code'),
                array(\DB::expr("
                    CASE
                        WHEN t.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                        ELSE AES_DECRYPT(UNHEX(t.customer_name),'".$encrypt_key."')
                    END
                    "), 'customer_name'),
                array(\DB::expr("
                    CASE
                        WHEN t.consumer_name IS NULL THEN ca.consumer_name
                        ELSE AES_DECRYPT(UNHEX(t.consumer_name),'".$encrypt_key."')
                    END
                    "), 'consumer_name'),
                array(\DB::expr("
                    CASE
                        WHEN t.owner_name IS NULL THEN ca.owner_name
                        ELSE AES_DECRYPT(UNHEX(t.owner_name),'".$encrypt_key."')
                    END
                    "), 'owner_name'),
                array('t.tire_type', 'tire_type'),
                array('t.tire_maker', 'tire_maker'),
                array('t.tire_product_name', 'tire_product_name'),
                array('t.tire_size', 'tire_size'),
                array('t.tire_pattern', 'tire_pattern'),
                array('t.tire_made_date', 'tire_made_date'),
                array('t.tire_punk', 'tire_punk'),
                array('t.nut_flg', 'nut_flg'),
                array('t.tire_remaining_groove1', 'tire_remaining_groove1'),
                array('t.tire_remaining_groove2', 'tire_remaining_groove2'),
                array('t.tire_remaining_groove3', 'tire_remaining_groove3'),
                array('t.tire_remaining_groove4', 'tire_remaining_groove4'),
                array('t.delivery_schedule_flg', 'delivery_schedule_flg'),
                array('t.receipt_flg', 'receipt_flg'),
                array('t.delivery_flg', 'delivery_flg'),
                array('t.complete_flg', 'complete_flg'),
                array('t.schedule_id', 'schedule_id'),
                array('t.update_datetime', 'update_datetime')
                );

        // テーブル
        $stmt->from(array('t_logistics', 't'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 't.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 't.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // 入庫フラグ
        $stmt->where('t.receipt_flg', 'YES');
        // 出庫フラグ
        $stmt->where('t.delivery_flg', 'NO');
        // レコードID
        $stmt->where('t.id', 'IN', $logistics_ids);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

}