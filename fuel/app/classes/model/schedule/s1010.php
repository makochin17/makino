<?php
namespace Model\Schedule;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030;

class S1010 extends \Model {

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

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
            case 'search':
            default:
                $res = array(
                    'schedule_id'                       => '',
                    'schedule_type'                     => '',
                    'start_date_from'                   => '',
                    'start_date_to'                     => '',
                    'start_time_from'                   => '',
                    'start_time_to'                     => '',
                    'car_id'                            => '',
                    'car_code'                          => '',
                    'car_name'                          => '',
                    'customer_code'                     => '',
                    'customer_name'                     => '',
                    'consumer_name'                     => '',
                    'cancel_flg'                        => '',
                    'carry_flg'                         => '',
                    'search_mode'                       => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'schedule', $conditions, $input_data) {

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
     * レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(s.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                        array('s.id', 'schedule_id'),
                        array(\DB::expr("DATE_FORMAT(s.start_date,'%Y-%m-%d')"), 'start_date'),
                        array('s.start_time', 'start_time'),
                        array('s.car_id', 'car_id'),
                        array('s.car_code', 'car_code'),
                        array(\DB::expr("AES_DECRYPT(UNHEX(s.car_name),'".$encrypt_key."')"), 'car_name'),
                        array('s.customer_code', 'customer_code'),
                        array(\DB::expr("
                            CASE
                                WHEN s.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                                ELSE AES_DECRYPT(UNHEX(s.customer_name),'".$encrypt_key."')
                            END
                            "), 'customer_name'),
                        array(\DB::expr("
                            CASE
                                WHEN s.consumer_name IS NULL THEN ca.consumer_name
                                ELSE AES_DECRYPT(UNHEX(s.consumer_name),'".$encrypt_key."')
                            END
                            "), 'consumer_name'),
                        array('m.customer_type', 'customer_type'),
                        array('s.schedule_type', 'schedule_type'),
                        array('s.request_memo', 'request_memo'),
                        array('s.memo', 'memo'),
                        array(\DB::expr("
                            CASE
                                WHEN s.request_class = 'delivery' THEN '配達'
                                WHEN s.request_class = 'pick_up' THEN '引取り'
                                WHEN s.request_class = 'extradition' THEN '引渡し'
                                WHEN s.request_class = 'business_trip' THEN '出張'
                                WHEN s.request_class = 'shipping' THEN '発送'
                                WHEN s.request_class = 'inspection' THEN '点検'
                                ELSE 'その他'
                            END
                            "), 'request_class'),
                        array('s.cancel_flg', 'cancel_flg'),
                        array('s.carry_flg', 'carry_flg'),
                        array('s.update_datetime', 'update_datetime')
                        );
            break;
        }

        // テーブル
        $stmt->from(array('t_schedule', 's'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 's.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 's.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('s.del_flg', '=', 'NO');
        // 予約ID
        if (!empty($conditions['schedule_id'])) {
            $stmt->where('s.id', '=', $conditions['schedule_id']);
        }
        // 予約日／希望日
        if (!empty($conditions['start_date_from']) && trim($conditions['start_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
            $stmt->where('s.start_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['start_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
                $stmt->where('s.start_date', '>=', $date);
            }
            if (!empty($conditions['start_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
                $stmt->where('s.start_date', '<=', $date);
            }
        }
        // お客様番号
        if (!empty($conditions['customer_code'])) {
            // $stmt->where(\DB::expr('CAST(s.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.customer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // 車両番号
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->where('s.car_code', 'LIKE', \DB::expr("'%".$conditions['car_code']."%'"));
        }
        // 車種
        if (!empty($conditions['car_name']) && trim($conditions['car_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.car_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
        }
        // 使用者
        if (!empty($conditions['consumer_name']) && trim($conditions['consumer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.consumer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['consumer_name']."%'"));
        }
        // 予約タイプ
        if (!empty($conditions['schedule_type']) && $conditions['schedule_type'] != 'all') {
            $stmt->where('s.schedule_type', '=', $conditions['schedule_type']);
        }
        // キャンセルフラグ
        if (empty($conditions['cancel_flg']) || $conditions['cancel_flg'] == 'NO') {
            $stmt->where('s.cancel_flg', '=', 'NO');
        }
        // 持込みフラグ
        if (empty($conditions['carry_flg']) || $conditions['carry_flg'] == 'NO') {
            $stmt->where('s.carry_flg', '=', 'NO');
        }

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('s.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('s.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    /**
     * レコード検索件数取得(お客様用＜個人・法人＞)
     */
    public static function getCustomerSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(s.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                        array('s.id', 'schedule_id'),
                        array(\DB::expr("DATE_FORMAT(s.start_date,'%Y-%m-%d')"), 'start_date'),
                        array('s.start_time', 'start_time'),
                        array('s.car_id', 'car_id'),
                        array('s.car_code', 'car_code'),
                        array(\DB::expr("AES_DECRYPT(UNHEX(s.car_name),'".$encrypt_key."')"), 'car_name'),
                        array('s.customer_code', 'customer_code'),
                        array(\DB::expr("
                            CASE
                                WHEN s.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                                ELSE AES_DECRYPT(UNHEX(s.customer_name),'".$encrypt_key."')
                            END
                            "), 'customer_name'),
                        array(\DB::expr("
                            CASE
                                WHEN s.consumer_name IS NULL THEN ca.consumer_name
                                ELSE AES_DECRYPT(UNHEX(s.consumer_name),'".$encrypt_key."')
                            END
                            "), 'consumer_name'),
                        array('m.customer_type', 'customer_type'),
                        array('s.schedule_type', 'schedule_type'),
                        array('s.request_memo', 'request_memo'),
                        array('s.memo', 'memo'),
                        array(\DB::expr("
                            CASE
                                WHEN s.request_class = 'delivery' THEN '配達'
                                WHEN s.request_class = 'pick_up' THEN '引取り'
                                WHEN s.request_class = 'extradition' THEN '引渡し'
                                WHEN s.request_class = 'business_trip' THEN '出張'
                                WHEN s.request_class = 'shipping' THEN '発送'
                                WHEN s.request_class = 'inspection' THEN '点検'
                                ELSE 'その他'
                            END
                            "), 'request_class'),
                        array('s.cancel_flg', 'cancel_flg'),
                        array('s.carry_flg', 'carry_flg'),
                        array('s.update_datetime', 'update_datetime')
                        );
            break;
        }

        // テーブル
        $stmt->from(array('t_schedule', 's'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 's.customer_code')
            ->on('m.del_flg', '=', \DB::expr("'NO'"))
            ->on('m.customer_type', '!=', \DB::expr("'dealer'"))
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 's.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('s.del_flg', '=', 'NO');
        // 予約ID
        if (!empty($conditions['schedule_id'])) {
            $stmt->where('s.id', '=', $conditions['schedule_id']);
        }
        // 予約日／希望日
        if (!empty($conditions['start_date_from']) && trim($conditions['start_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
            $stmt->where('s.start_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['start_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
                $stmt->where('s.start_date', '>=', $date);
            }
            if (!empty($conditions['start_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
                $stmt->where('s.start_date', '<=', $date);
            }
        }
        // お客様番号
        if (!empty($conditions['customer_code'])) {
            $stmt->where(\DB::expr('CAST(s.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.customer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // 車両番号
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->where('s.car_code', 'LIKE', \DB::expr("'%".$conditions['car_code']."%'"));
        }
        // 車種
        if (!empty($conditions['car_name']) && trim($conditions['car_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.car_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
        }
        // 使用者
        if (!empty($conditions['consumer_name']) && trim($conditions['consumer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.consumer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['consumer_name']."%'"));
        }
        // 予約タイプ
        if (!empty($conditions['schedule_type']) && $conditions['schedule_type'] != 'all') {
            $stmt->where('s.schedule_type', '=', $conditions['schedule_type']);
        }
        // キャンセルフラグ
        if (empty($conditions['cancel_flg']) || $conditions['cancel_flg'] == 'NO') {
            $stmt->where('s.cancel_flg', '=', 'NO');
        }
        // 持込みフラグ
        if (empty($conditions['carry_flg']) || $conditions['carry_flg'] == 'NO') {
            $stmt->where('s.carry_flg', '=', 'NO');
        }

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('s.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('s.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    /**
     * レコード検索件数取得(お客様用＜配達＞)
     */
    public static function getCustomerDealerSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(s.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                        array('s.id', 'schedule_id'),
                        array(\DB::expr("DATE_FORMAT(s.start_date,'%Y-%m-%d')"), 'start_date'),
                        array('s.start_time', 'start_time'),
                        array('s.car_id', 'car_id'),
                        array('s.car_code', 'car_code'),
                        array(\DB::expr("AES_DECRYPT(UNHEX(s.car_name),'".$encrypt_key."')"), 'car_name'),
                        array('s.customer_code', 'customer_code'),
                        array(\DB::expr("
                            CASE
                                WHEN s.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                                ELSE AES_DECRYPT(UNHEX(s.customer_name),'".$encrypt_key."')
                            END
                            "), 'customer_name'),
                        array(\DB::expr("
                            CASE
                                WHEN s.consumer_name IS NULL THEN ca.consumer_name
                                ELSE AES_DECRYPT(UNHEX(s.consumer_name),'".$encrypt_key."')
                            END
                            "), 'consumer_name'),
                        array('m.customer_type', 'customer_type'),
                        array('s.schedule_type', 'schedule_type'),
                        array('s.request_memo', 'request_memo'),
                        array('s.memo', 'memo'),
                        array(\DB::expr("
                            CASE
                                WHEN s.request_class = 'delivery' THEN '配達'
                                WHEN s.request_class = 'pick_up' THEN '引取り'
                                WHEN s.request_class = 'extradition' THEN '引渡し'
                                WHEN s.request_class = 'business_trip' THEN '出張'
                                WHEN s.request_class = 'shipping' THEN '発送'
                                WHEN s.request_class = 'inspection' THEN '点検'
                                ELSE 'その他'
                            END
                            "), 'request_class'),
                        array('s.cancel_flg', 'cancel_flg'),
                        array('s.carry_flg', 'carry_flg'),
                        array('s.update_datetime', 'update_datetime')
                        );
            break;
        }

        // テーブル
        $stmt->from(array('t_schedule', 's'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 's.customer_code')
            ->on('m.del_flg', '=', \DB::expr("'NO'"))
            ->on('m.customer_type', '=', \DB::expr("'dealer'"))
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 's.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('s.del_flg', '=', 'NO');
        // 予約ID
        if (!empty($conditions['schedule_id'])) {
            $stmt->where('s.id', '=', $conditions['schedule_id']);
        }
        // 予約日／希望日
        if (!empty($conditions['start_date_from']) && trim($conditions['start_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
            $stmt->where('s.start_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['start_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_from'])))->format('mysql_date');
                $stmt->where('s.start_date', '>=', $date);
            }
            if (!empty($conditions['start_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['start_date_to'])))->format('mysql_date');
                $stmt->where('s.start_date', '<=', $date);
            }
        }
        // お客様番号
        if (!empty($conditions['customer_code'])) {
            $stmt->where(\DB::expr('CAST(s.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.customer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // 車両番号
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->where('s.car_code', 'LIKE', \DB::expr("'%".$conditions['car_code']."%'"));
        }
        // 車種
        if (!empty($conditions['car_name']) && trim($conditions['car_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.car_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
        }
        // 使用者
        if (!empty($conditions['consumer_name']) && trim($conditions['consumer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(s.consumer_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['consumer_name']."%'"));
        }
        // 予約タイプ
        if (!empty($conditions['schedule_type']) && $conditions['schedule_type'] != 'all') {
            $stmt->where('s.schedule_type', '=', $conditions['schedule_type']);
        }
        // キャンセルフラグ
        if (empty($conditions['cancel_flg']) || $conditions['cancel_flg'] == 'NO') {
            $stmt->where('s.cancel_flg', '=', 'NO');
        }
        // 持込みフラグ
        if (empty($conditions['carry_flg']) || $conditions['carry_flg'] == 'NO') {
            $stmt->where('s.carry_flg', '=', 'NO');
        }

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('s.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('s.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    //=========================================================================//
    //==============================   取得データ  =============================//
    //=========================================================================//
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
        // お客様番号
        if (!empty($item['customer_code'])) {
            $stmt->where('t.customer_code', '=', $item['customer_code']);
        }
        // 車両ID
        if (!empty($item['car_id'])) {
            $stmt->where('t.car_id', '=', $item['car_id']);
        }
        // 車両番号
        if (!empty($item['car_code'])) {
            $stmt->where('t.car_code', '=', $item['car_code']);
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

}