<?php
namespace Model\Logistics;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Excel\Data;

class L0020 extends \Model {

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
    //===============================   検索処理  ==============================//
    //=========================================================================//
    /**
     * 入出庫情報レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(l.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                        array('l.id', 'logistics_id'),
                        array(\DB::expr("DATE_FORMAT(l.delivery_schedule_date,'%Y-%m-%d')"), 'delivery_schedule_date'),
                        array(\DB::expr("DATE_FORMAT(l.delivery_date,'%Y-%m-%d')"), 'delivery_date'),
                        array(\DB::expr("DATE_FORMAT(l.receipt_date,'%Y-%m-%d')"), 'receipt_date'),
                        array('l.delivery_schedule_time', 'delivery_schedule_time'),
                        array('l.delivery_time', 'delivery_time'),
                        array('l.receipt_time', 'receipt_time'),
                        array('l.location_id', 'location_id'),
                        array('l.car_id', 'car_id'),
                        array('l.car_code', 'car_code'),
                        array(\DB::expr("AES_DECRYPT(UNHEX(l.car_name),'".$encrypt_key."')"), 'car_name'),
                        array('l.customer_code', 'customer_code'),
                        array(\DB::expr("
                            CASE
                                WHEN l.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                                ELSE AES_DECRYPT(UNHEX(l.customer_name),'".$encrypt_key."')
                            END
                            "), 'customer_name'),
                        array(\DB::expr("
                            CASE
                                WHEN l.consumer_name IS NULL THEN ca.consumer_name
                                ELSE AES_DECRYPT(UNHEX(l.consumer_name),'".$encrypt_key."')
                            END
                            "), 'consumer_name'),
                        array(\DB::expr("
                            CASE
                                WHEN l.owner_name IS NULL THEN ca.owner_name
                                ELSE AES_DECRYPT(UNHEX(l.owner_name),'".$encrypt_key."')
                            END
                            "), 'owner_name'),
                        array('l.tire_type', 'tire_type'),
                        array('l.tire_maker', 'tire_maker'),
                        array('l.tire_product_name', 'tire_product_name'),
                        array('l.tire_size', 'tire_size'),
                        array('l.tire_pattern', 'tire_pattern'),
                        array('l.tire_made_date', 'tire_made_date'),
                        array('l.tire_punk', 'tire_punk'),
                        array('l.nut_flg', 'nut_flg'),
                        array('l.tire_remaining_groove1', 'tire_remaining_groove1'),
                        array('l.tire_remaining_groove2', 'tire_remaining_groove2'),
                        array('l.tire_remaining_groove3', 'tire_remaining_groove3'),
                        array('l.tire_remaining_groove4', 'tire_remaining_groove4'),
                        array('l.delivery_schedule_flg', 'delivery_schedule_flg'),
                        array('l.receipt_flg', 'receipt_flg'),
                        array('l.delivery_flg', 'delivery_flg'),
                        array('l.complete_flg', 'complete_flg'),
                        array('l.schedule_id', 'schedule_id'),
                        array('l.update_datetime', 'update_datetime')
                        );
            break;
        }

        // テーブル
        $stmt->from(array('t_logistics', 'l'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 'l.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 'l.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('l.del_flg', '=', 'NO');
        // 完了フラグ
        $stmt->where('l.complete_flg', '=', 'NO');
        // 予約ID
        if (!empty($conditions['schedule_id'])) {
            $stmt->where('l.schedule_id', '=', $conditions['schedule_id']);
        }
        // 出庫指示日／入庫予定日
        if (!empty($conditions['delivery_schedule_date_from']) && trim($conditions['delivery_schedule_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['delivery_schedule_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['delivery_schedule_date_to'])))->format('mysql_date');
            $stmt->where('l.delivery_schedule_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['delivery_schedule_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_schedule_date_from'])))->format('mysql_date');
                $stmt->where('l.delivery_schedule_date', '>=', $date);
            }
            if (!empty($conditions['delivery_schedule_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_schedule_date_to'])))->format('mysql_date');
                $stmt->where('l.delivery_schedule_date', '<=', $date);
            }
        }
        // 入庫日
        if (!empty($conditions['receipt_date_from']) && trim($conditions['receipt_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['receipt_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['receipt_date_to'])))->format('mysql_date');
            $stmt->where('l.receipt_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['receipt_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['receipt_date_from'])))->format('mysql_date');
                $stmt->where('l.receipt_date', '>=', $date);
            }
            if (!empty($conditions['receipt_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['receipt_date_to'])))->format('mysql_date');
                $stmt->where('l.receipt_date', '<=', $date);
            }
        }
        // 出庫日
        if (!empty($conditions['delivery_date_from']) && trim($conditions['delivery_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
            $stmt->where('l.delivery_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['delivery_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
                $stmt->where('l.delivery_date', '>=', $date);
            }
            if (!empty($conditions['delivery_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
                $stmt->where('l.delivery_date', '<=', $date);
            }
        }
        // お客様番号
        if (!empty($conditions['customer_code'])) {
            // $stmt->where(\DB::expr('CAST(l.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(l.customer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // 車両番号
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->where('l.car_code', 'LIKE', \DB::expr("'%".$conditions['car_code']."%'"));
        }
        // 車種
        if (!empty($conditions['car_name']) && trim($conditions['car_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(l.car_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
        }
        // 使用者
        if (!empty($conditions['consumer_name']) && trim($conditions['consumer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(l.consumer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['consumer_name']."%'"));
        }
        // タイヤ種別
        if (!empty($conditions['tire_type'])) {
            $stmt->where('l.tire_type', '=', $conditions['tire_type']);
        }
        // 入庫フラグ
        if (!empty($conditions['receipt_flg'])) {
            $stmt->where('l.receipt_flg', '=', $conditions['receipt_flg']);
        }
        // 出庫フラグ
        if (!empty($conditions['delivery_flg'])) {
            $stmt->where('l.delivery_flg', '=', $conditions['delivery_flg']);
        }
        // 出庫指示フラグ
        if (!empty($conditions['delivery_schedule_flg'])) {
            $stmt->where('l.delivery_schedule_flg', '=', $conditions['delivery_schedule_flg']);
        }
        // 完了フラグ
        if (!empty($conditions['complete_flg'])) {
            $stmt->where('l.complete_flg', '=', $conditions['complete_flg']);
        }
        // 保管場所
        if (!empty($conditions['location_id'])) {
            $stmt->where('l.location_id', '=', $conditions['location_id']);
        }
        // 保管場所フラグ
        if (!empty($conditions['location_flg'])) {
            $stmt->where('l.location_id', '!=', 0);
        }

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('l.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('l.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
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


        return Data::setReceiptSticker($data, $db);
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