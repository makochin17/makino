<?php
namespace Model\Logistics;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030;

class L0013 extends \Model {

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

    // データ加工
    public static function setList($type, $item) {

        $res = array();
        if (empty($item)) {
            return false;
        }

        switch ($type) {
            case 'unit':
                foreach ($item as $key => $val) {
                    $res[$val['unit_id']] = $val['unit_name'];
                }
                break;
            case 'location':
                foreach ($item as $key => $val) {
                    $res[$val['storage_location_id']] = $val['storage_location_name'];
                }
                break;
            default:
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
            case 'search':
                $res = array(
                    'select_id'                         => '',
                    'logistics_id'                      => '',
                    'delivery_schedule_date'            => '',
                    'delivery_schedule_date_from'       => '',
                    'delivery_schedule_date_to'         => '',
                    'car_id'                            => '',
                    'car_code'                          => '',
                    'car_name'                          => '',
                    'customer_code'                     => '',
                    'customer_name'                     => '',
                    'owner_name'                        => '',
                    'consumer_name'                     => '',
                    'location_id'                       => '',
                    'location_name'                     => '',
                    'delivery_schedule_flg'             => '',
                    'receipt_flg'                       => '',
                    'delivery_flg'                      => '',
                    'complete_flg'                      => '',
                    'schedule_id'                       => '',
                    'search_mode'                       => '',
                    'location_flg'                      => '',
                );
                break;
            case 'set':
            default:
                $res = array(
                    'select_id'                         => '',
                    'logistics_id'                      => '',
                    'delivery_schedule_date'            => '',
                    'delivery_schedule_time'            => '',
                    'delivery_date'                     => '',
                    'delivery_time'                     => '',
                    'receipt_date'                      => '',
                    'receipt_time'                      => '',
                    'car_id'                            => '',
                    'car_code'                          => '',
                    'car_name'                          => '',
                    'customer_code'                     => '',
                    'customer_name'                     => '',
                    'owner_name'                        => '',
                    'consumer_name'                     => '',
                    'location_id'                       => '',
                    'tire_type'                         => '',
                    'tire_maker'                        => '',
                    'tire_product_name'                 => '',
                    'tire_size'                         => '',
                    'tire_pattern'                      => '',
                    'tire_made_date'                    => '',
                    'tire_punk'                         => '',
                    'nut_flg'                           => '',
                    'tire_remaining_groove1'            => '',
                    'tire_remaining_groove2'            => '',
                    'tire_remaining_groove3'            => '',
                    'tire_remaining_groove4'            => '',
                    'delivery_schedule_flg'             => '',
                    'receipt_flg'                       => '',
                    'delivery_flg'                      => '',
                    'complete_flg'                      => '',
                    'schedule_id'                       => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'logistics', $conditions, $input_data) {

        if (empty($conditions)) {
            $conditions = self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if (isset($input_data[$key])) {
                $conditions[$key] = $input_data[$key];
            }
        }

        return $conditions;
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
        // 出庫指示フラグ
        $stmt->where('l.receipt_flg', '=', 'YES');
        // 出庫指示フラグ
        // $stmt->where('l.delivery_schedule_flg', '=', 'NO');
        // 出庫指示日／出庫予定日
        if (!empty($conditions['delivery_schedule_date'])) {
            $date = \Date::forge(strtotime(trim($conditions['delivery_schedule_date'])))->format('mysql_date');
            $stmt->where('l.delivery_schedule_date', $date);
        } elseif (!empty($conditions['delivery_schedule_date_from']) && trim($conditions['delivery_schedule_date_to']) != '') {
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
    //=========================   対象更新(出庫指示確定)   ==========================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        // レコード更新
        if ($result = self::getLogisticsById($conditions['logistics_id'], $db)) {
            if (!self::updLogistics($conditions, $db)) {
                \Log::error(\Config::get('m_DS0010')."[ID:".$conditions['logistics_id']."]");
                return \Config::get('m_DS0010');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DS0005', AuthConfig::getAuthConfig('user_name').\Config::get('m_DS0005'), '出庫指示更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 出庫情報更新
     */
    public static function updLogistics($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data['logistics_id'])) {
            return false;
        }

        // 項目セット
        $set = array();
        if (!empty($data['delivery_schedule_flg'])) {
            $set['delivery_schedule_flg']   = $data['delivery_schedule_flg'];
        } else {
            $set['delivery_schedule_flg']   = 'YES';
        }

        // テーブル
        $stmt = \DB::update('t_logistics')->set(array_merge($set, self::getEtcData(false)));

        // 予約ID
        $stmt->where('id', '=', $data['logistics_id']);
        // 入庫フラグ
        $stmt->where('receipt_flg', '=', 'YES');
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return $data['logistics_id'];
        }
        return false;
    }

    //=========================================================================//
    //=======================   対象削除(キャンセル)   ==========================//
    //=========================================================================//
    public static function delete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        if ($result = self::getLogisticsById($conditions['logistics_id'], $db)) {
            if (!self::delLogistics($conditions, $db)) {
                \Log::error(\Config::get('m_DS0015')."[logistics_id:".$conditions['logistics_id']."]");
                return \Config::get('m_DS0015');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DS0014', AuthConfig::getAuthConfig('user_name').\Config::get('m_DS0014'), '出庫指示情報取下', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 入出庫データ削除
     */
    public static function delLogistics($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        // 項目セット
        $set['delivery_schedule_flg'] = 'NO';

        // テーブル
        $stmt = \DB::update('t_logistics')->set(array_merge($set, self::getEtcData(false)));

        // 予約ID
        $stmt->where('id', '=', $data['logistics_id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');
        // 入庫フラグ
        $stmt->where('receipt_flg', '=', 'YES');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //==============================   取得データ  =============================//
    //=========================================================================//
    /**
     * 保管場所リレーションレコード取得
     */
    public static function getLocationData($code, $db) {
        return M0030::getStorageLocation($code, $db);
    }

    /**
     * 保管場所情報データの取得
     */
    public static function getSearchLocation($location_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        // 検索実行
        return self::getLocationData($location_id, $db);
    }

    /**
     * 保管場所リストの設定(入庫済の保管場所のみ取得)
     */
    public static function getLocationList($type, $location_list, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($location_list)) {
            return false;
        }

        // 入庫済みの保管場所を取得
        $stmt = \DB::select(
                array('l.location_id', 'location_id')
                );

        // テーブル
        $stmt->from(array('t_logistics', 'l'));
        // 条件
        $stmt->where('l.del_flg', '=', 'NO');
        // 入庫フラグ
        $stmt->where('l.receipt_flg', 'YES');
        // 出庫フラグ
        $stmt->where('l.delivery_flg', 'NO');
        // 保管場所ID
        $stmt->where('l.location_id', '!=', 0);
        // 検索実行
        $logistics = $stmt->execute($db)->as_array();

        $list = array();
        switch ($type) {
            case 'logistics':
                // 入出庫情報に存在している保管場所を取得
                if (!empty($logistics)) {
                    foreach ($logistics as $key => $val) {
                        if (isset($location_list[$val['location_id']])) {
                            $list[$val['location_id']] = $location_list[$val['location_id']];
                        }
                    }
                }
                break;
            default:
                // 入出庫情報に存在していない保管場所を取得
                $list = $location_list;
                if (!empty($logistics)) {
                    foreach ($logistics as $key => $val) {
                        if (isset($location_list[$val['location_id']])) {
                            unset($list[$val['location_id']]);
                        }
                    }
                }
                break;
        }

        return $list;
    }

    /**
     * ID別入出庫データ取得
     */
    public static function getLogisticsById($logistics_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
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
        // レコードID
        $stmt->where('t.id', '=', $logistics_id);
        // 入庫フラグ
        $stmt->where('t.receipt_flg', 'YES');
        // 出庫フラグ
        $stmt->where('t.delivery_flg', 'NO');

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 予約別入出庫情報取得
     */
    public static function getLogisticsBySchedule($item = array(), $db = null) {

        if (is_null($db)) {
            $db = self::$db;
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
        // 予約ID
        if (!empty($item['schedule_id'])) {
            $stmt->where('t.schedule_id', '=', $item['schedule_id']);
        }
        // 出庫指示日／入庫予定日
        if (!empty($item['delivery_schedule_date'])) {
            // $stmt->where('t.delivery_schedule_date', '=', $item['delivery_schedule_date']);
        }
        // 出庫指示時間／入庫予定時間
        if (!empty($item['delivery_schedule_time'])) {
            // $stmt->where('t.delivery_schedule_time', '=', $item['delivery_schedule_time']);
        }

        // ソート
        $stmt->order_by('t.id', 'ASC');
        // 検索実行
        $res = $stmt->execute($db)->current();

        if (!empty($res)) {
            return $res;
        }
        return false;
    }

    /**
     * お客様情報データの取得
     */
    public static function getSearchCustomer($customer_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                    array('mc.customer_code', 'customer_code'),
                    array(\DB::expr("
                        CASE
                            WHEN mc.customer_type = 'individual' THEN '個人'
                            WHEN mc.customer_type = 'corporation' THEN '法人'
                            WHEN mc.customer_type = 'dealer' THEN 'ディーラー'
                            ELSE ''
                        END
                        "), 'customer_type'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'customer_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.name_kana),"'.$encrypt_key.'")'), 'customer_name_kana'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.zip),"'.$encrypt_key.'")'), 'zip'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr1),"'.$encrypt_key.'")'), 'addr1'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr2),"'.$encrypt_key.'")'), 'addr2'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.tel),"'.$encrypt_key.'")'), 'tel'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax),"'.$encrypt_key.'")'), 'fax'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.mobile),"'.$encrypt_key.'")'), 'mobile'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.office_name),"'.$encrypt_key.'")'), 'office_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.manager_name),"'.$encrypt_key.'")'), 'manager_name'),
                    array('mc.birth_date', 'birth_date'),
                    array(\DB::expr("
                        CASE
                            WHEN mc.sex = 'Man' THEN '男性'
                            WHEN mc.sex = 'Woman' THEN '女性'
                            ELSE ''
                        END
                        "), 'sex'),
                    array('mc.resign_flg', 'resign_flg'),
                    array('mc.resign_date', 'resign_date'),
                    array('mc.resign_reason', 'resign_reason'),
                    array('mc.start_date', 'start_date'),
                    array('mc.end_date', 'end_date')
                );

        // テーブル
        $stmt->from(array('m_customer', 'mc'));

        //削除フラグ
        $stmt->where('mc.del_flg', '=', 'NO');
        // お客様番号
        $stmt->where('mc.customer_code', '=', $customer_code);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 車両情報データの取得(car_id)
     */
    public static function getSearchCar($car_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.id', 'car_id'),
                array('m.old_car_id', 'old_car_id'),
                array('m.car_code', 'car_code'),
                array('m.customer_code', 'customer_code'),
                array(\DB::expr('(SELECT AES_DECRYPT(UNHEX(name),"'.$encrypt_key.'") FROM m_customer WHERE customer_code = m.customer_code)'), 'customer_name'),
                array('m.owner_name', 'owner_name'),
                array('m.consumer_name', 'consumer_name'),
                array('m.car_name', 'car_name'),
                array('m.work_required_time', 'work_required_time'),
                array('m.summer_tire_maker', 'summer_tire_maker'),
                array('m.summer_tire_product_name', 'summer_tire_product_name'),
                array('m.summer_tire_size', 'summer_tire_size'),
                array('m.summer_tire_size2', 'summer_tire_size2'),
                array('m.summer_tire_pattern', 'summer_tire_pattern'),
                array('m.summer_tire_wheel_product_name', 'summer_tire_wheel_product_name'),
                array('m.summer_tire_wheel_size', 'summer_tire_wheel_size'),
                array('m.summer_tire_wheel_size2', 'summer_tire_wheel_size2'),
                array('m.summer_tire_made_date', 'summer_tire_made_date'),
                array('m.summer_tire_remaining_groove1', 'summer_tire_remaining_groove1'),
                array('m.summer_tire_remaining_groove2', 'summer_tire_remaining_groove2'),
                array('m.summer_tire_remaining_groove3', 'summer_tire_remaining_groove3'),
                array('m.summer_tire_remaining_groove4', 'summer_tire_remaining_groove4'),
                array('m.summer_tire_punk', 'summer_tire_punk'),
                array('m.summer_nut_flg', 'summer_nut_flg'),
                array('m.summer_location_id', 'summer_location_id'),
                array('m.winter_tire_maker', 'winter_tire_maker'),
                array('m.winter_tire_product_name', 'winter_tire_product_name'),
                array('m.winter_tire_size', 'winter_tire_size'),
                array('m.winter_tire_size2', 'winter_tire_size2'),
                array('m.winter_tire_pattern', 'winter_tire_pattern'),
                array('m.winter_tire_wheel_product_name', 'winter_tire_wheel_product_name'),
                array('m.winter_tire_wheel_size', 'winter_tire_wheel_size'),
                array('m.winter_tire_wheel_size2', 'winter_tire_wheel_size2'),
                array('m.winter_tire_made_date', 'winter_tire_made_date'),
                array('m.winter_tire_remaining_groove1', 'winter_tire_remaining_groove1'),
                array('m.winter_tire_remaining_groove2', 'winter_tire_remaining_groove2'),
                array('m.winter_tire_remaining_groove3', 'winter_tire_remaining_groove3'),
                array('m.winter_tire_remaining_groove4', 'winter_tire_remaining_groove4'),
                array('m.winter_tire_punk', 'winter_tire_punk'),
                array('m.winter_nut_flg', 'winter_nut_flg'),
                array('m.winter_location_id', 'winter_location_id'),
                array('m.summer_class_flg', 'summer_class_flg'),
                array('m.winter_class_flg', 'winter_class_flg'),
                array('m.note', 'note'),
                array('m.message', 'message')
                );

        // テーブル
        $stmt->from(array('m_car', 'm'));

        // 車両ID
        $stmt->where('m.id', '=', $car_id);
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 車両情報データの取得(car_code)
     */
    public static function getSearchCarByCode($car_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.id', 'car_id'),
                array('m.old_car_id', 'old_car_id'),
                array('m.car_code', 'car_code'),
                array('m.customer_code', 'customer_code'),
                array(\DB::expr('(SELECT AES_DECRYPT(UNHEX(name),"'.$encrypt_key.'") FROM m_customer WHERE customer_code = m.customer_code)'), 'customer_name'),
                array('m.owner_name', 'owner_name'),
                array('m.consumer_name', 'consumer_name'),
                array('m.car_name', 'car_name'),
                array('m.work_required_time', 'work_required_time'),
                array('m.summer_tire_maker', 'summer_tire_maker'),
                array('m.summer_tire_product_name', 'summer_tire_product_name'),
                array('m.summer_tire_size', 'summer_tire_size'),
                array('m.summer_tire_size2', 'summer_tire_size2'),
                array('m.summer_tire_pattern', 'summer_tire_pattern'),
                array('m.summer_tire_wheel_product_name', 'summer_tire_wheel_product_name'),
                array('m.summer_tire_wheel_size', 'summer_tire_wheel_size'),
                array('m.summer_tire_wheel_size2', 'summer_tire_wheel_size2'),
                array('m.summer_tire_made_date', 'summer_tire_made_date'),
                array('m.summer_tire_remaining_groove1', 'summer_tire_remaining_groove1'),
                array('m.summer_tire_remaining_groove2', 'summer_tire_remaining_groove2'),
                array('m.summer_tire_remaining_groove3', 'summer_tire_remaining_groove3'),
                array('m.summer_tire_remaining_groove4', 'summer_tire_remaining_groove4'),
                array('m.summer_tire_punk', 'summer_tire_punk'),
                array('m.summer_nut_flg', 'summer_nut_flg'),
                array('m.summer_location_id', 'summer_location_id'),
                array('m.winter_tire_maker', 'winter_tire_maker'),
                array('m.winter_tire_product_name', 'winter_tire_product_name'),
                array('m.winter_tire_size', 'winter_tire_size'),
                array('m.winter_tire_size2', 'winter_tire_size2'),
                array('m.winter_tire_pattern', 'winter_tire_pattern'),
                array('m.winter_tire_wheel_product_name', 'winter_tire_wheel_product_name'),
                array('m.winter_tire_wheel_size', 'winter_tire_wheel_size'),
                array('m.winter_tire_wheel_size2', 'winter_tire_wheel_size2'),
                array('m.winter_tire_made_date', 'winter_tire_made_date'),
                array('m.winter_tire_remaining_groove1', 'winter_tire_remaining_groove1'),
                array('m.winter_tire_remaining_groove2', 'winter_tire_remaining_groove2'),
                array('m.winter_tire_remaining_groove3', 'winter_tire_remaining_groove3'),
                array('m.winter_tire_remaining_groove4', 'winter_tire_remaining_groove4'),
                array('m.winter_tire_punk', 'winter_tire_punk'),
                array('m.winter_nut_flg', 'winter_nut_flg'),
                array('m.winter_location_id', 'winter_location_id'),
                array('m.summer_class_flg', 'summer_class_flg'),
                array('m.winter_class_flg', 'winter_class_flg'),
                array('m.note', 'note'),
                array('m.message', 'message')
                );

        // テーブル
        $stmt->from(array('m_car', 'm'));

        // 車両番号
        $stmt->where('m.car_code', '=', $car_code);
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }

}