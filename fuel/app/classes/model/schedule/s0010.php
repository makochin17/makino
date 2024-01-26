<?php
namespace Model\Schedule;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class S0010 extends \Model {

    public static $db               = 'MAKINO';

    // 予約タイプ
    public static $schedule_type    = null;

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
            default:
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
            case 'set':
                $res = array(
                    'id'                                => '',
                    'schedule_type'                     => '',
                    'start_date'                        => '',
                    'start_time'                        => '',
                    'end_date'                          => '',
                    'end_time'                          => '',
                    'car_id'                            => '',
                    'car_code'                          => '',
                    'car_name'                          => '',
                    'customer_code'                     => '',
                    'customer_name'                     => '',
                    'consumer_name'                     => '',
                    'unit_id'                           => '',
                    'request_class'                     => '',
                    'request_memo'                      => '',
                    'text_color'                        => '',
                    'back_color'                        => '',
                    'memo'                              => '',
                    'cancel'                            => '',
                    'commit'                            => '',
                    'cancel_flg'                        => '',
                    'commit_flg'                        => '',
                    'carry_flg'                         => '',
                    'title'                             => '',
                );
                break;
            case 'car':
            default:
                $res = array(
                    'mode'                              => '',
                    'car_id'                            => '',
                    'old_car_id'                        => '',
                    'car_code'                          => '',
                    'customer_code'                     => '',
                    'customer_name'                     => '',
                    'owner_name'                        => '',
                    'consumer_name'                     => '',
                    'car_name'                          => '',
                    'work_required_time'                => '',
                    'summer_tire_maker'                 => '',
                    'summer_tire_product_name'          => '',
                    'summer_tire_size'                  => '',
                    'summer_tire_size2'                 => '',
                    'summer_tire_pattern'               => '',
                    'summer_tire_wheel_product_name'    => '',
                    'summer_tire_wheel_size'            => '',
                    'summer_tire_wheel_size2'           => '',
                    'summer_tire_made_date'             => '',
                    'summer_tire_remaining_groove1'     => '',
                    'summer_tire_remaining_groove2'     => '',
                    'summer_tire_remaining_groove3'     => '',
                    'summer_tire_remaining_groove4'     => '',
                    'summer_tire_punk'                  => '',
                    'summer_nut_flg'                    => '',
                    'summer_location_id'                => '',
                    'winter_tire_maker'                 => '',
                    'winter_tire_product_name'          => '',
                    'winter_tire_size'                  => '',
                    'winter_tire_size2'                 => '',
                    'winter_tire_pattern'               => '',
                    'winter_tire_wheel_product_name'    => '',
                    'winter_tire_wheel_size'            => '',
                    'winter_tire_wheel_size2'           => '',
                    'winter_tire_made_date'             => '',
                    'winter_tire_remaining_groove1'     => '',
                    'winter_tire_remaining_groove2'     => '',
                    'winter_tire_remaining_groove3'     => '',
                    'winter_tire_remaining_groove4'     => '',
                    'winter_tire_punk'                  => '',
                    'winter_nut_flg'                    => '',
                    'winter_location_id'                => '',
                    'summer_class_flg'                  => '',
                    'winter_class_flg'                  => '',
                    'summer_tire_img_path1'             => '',
                    'summer_tire_img_path2'             => '',
                    'summer_tire_img_path3'             => '',
                    'summer_tire_img_path4'             => '',
                    'winter_tire_img_path1'             => '',
                    'winter_tire_img_path2'             => '',
                    'winter_tire_img_path3'             => '',
                    'winter_tire_img_path4'             => '',
                    'note'                              => '',
                    'message'                           => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'car', $conditions, $input_data) {

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
    //==============================   予約情報   ==============================//
    //=========================================================================//
    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, &$item, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録
        if (!$item['schedule_id'] = self::addSchedule($conditions, $db)) {
            \Log::error(\Config::get('m_SC0025')."[".print_r($conditions,true)."]");
            $item['return'] = \Config::get('m_SC0025');
            return $item['return'];
        } else {
            if ($data = self::getScheduleById($item['schedule_id'], $db)) {
                $item       = array(
                    'schedule_id'       => $data['id'],
                    'unit_id'           => $data['unit_id'],
                    'car_id'            => $data['car_id'],
                    'car_code'          => $data['car_code'],
                    'car_name'          => $data['car_name'],
                    'customer_code'     => $data['customer_code'],
                    'customer_name'     => $data['customer_name'],
                    'consumer_name'     => $data['consumer_name'],
                    'cancel'            => $data['cancel'],
                    'commit'            => $data['commit'],
                    'back_color'        => $data['back_color'],
                    'text_color'        => $data['text_color'],
                    'request_class'     => $data['request_class'],
                    'request_memo'      => $data['request_memo'],
                    'memo'              => $data['memo'],
                );

            }
        }
        return null;
    }

    /**
     * 予約登録
     */
    public static function addSchedule($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'schedule_type'                     => $data['schedule_type'],
            'start_date'                        => (!empty($data['start_date'])) ? date('Y-m-d', strtotime($data['start_date'])):null,
            'start_time'                        => (!empty($data['start_time'])) ? $data['start_time']:'00:00',
            'end_date'                          => (!empty($data['end_date'])) ? date('Y-m-d', strtotime($data['end_date'])):null,
            'end_time'                          => (!empty($data['end_time'])) ? $data['end_time']:'00:00',
            'car_id'                            => (!empty($data['car_id'])) ? $data['car_id']:null,
            'car_code'                          => (!empty($data['car_code'])) ? $data['car_code']:null,
            'car_name'                          => (!empty($data['car_name'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['car_name'].'","'.$encrypt_key.'"))'):null,
            'customer_code'                     => (!empty($data['customer_code'])) ? $data['customer_code']:null,
            'customer_name'                     => (!empty($data['customer_name'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))'):null,
            'consumer_name'                     => (!empty($data['consumer_name'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['consumer_name'].'","'.$encrypt_key.'"))'):null,
            'unit_id'                           => $data['unit_id'],
            'request_class'                     => (!empty($data['request_class'])) ? $data['request_class']:'other',
            'request_memo'                      => (!empty($data['request_memo'])) ? $data['request_memo']:null,
            'memo'                              => (!empty($data['memo'])) ? $data['memo']:null,
            'text_color'                        => (!empty($data['text_color'])) ? $data['text_color']:'#000000',
            'back_color'                        => (!empty($data['back_color'])) ? $data['back_color']:'#FFFFFF',
            'cancel'                            => (!empty($data['cancel'])) ? $data['cancel']:null,
            'cancel_flg'                        => (!empty($data['cancel'])) ? 'YES':'NO',
            'carry_flg'                         => (!empty($data['carry_flg'])) ? $data['carry_flg']:'NO',
            'title'                             => (!empty($data['title'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['title'].'","'.$encrypt_key.'"))'):null,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_schedule')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, &$item, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        // レコード更新
        if ($result = self::getScheduleById($conditions['id'], $db)) {
            if (!self::updSchedule($conditions, $db)) {
                \Log::error(\Config::get('m_SC0026')."[customer_code:".$conditions['customer_code']."][car_code:".$conditions['car_code']."]");
                return \Config::get('m_SC0026');
            } else {
                if ($data = self::getScheduleById($result['id'], $db)) {
                    $item       = array(
                        'schedule_id'       => $data['id'],
                        'unit_id'           => $data['unit_id'],
                        'car_id'            => $data['car_id'],
                        'car_code'          => $data['car_code'],
                        'car_name'          => $data['car_name'],
                        'customer_code'     => $data['customer_code'],
                        'customer_name'     => $data['customer_name'],
                        'consumer_name'     => $data['consumer_name'],
                        'cancel'            => $data['cancel'],
                        'commit'            => $data['commit'],
                        'back_color'        => $data['back_color'],
                        'text_color'        => $data['text_color'],
                        'request_class'     => $data['request_class'],
                        'request_memo'      => $data['request_memo'],
                        'memo'              => $data['memo'],
                    );
                }
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('SC0006', AuthConfig::getAuthConfig('user_name').\Config::get('m_SC0006'), '予約スケジュール更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 予約情報更新
     */
    public static function updSchedule($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array();
        if (!empty($data['schedule_type'])) {
            $set['schedule_type'] = $data['schedule_type'];
        }
        if (!empty($data['start_date'])) {
            $set['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        }
        if (!empty($data['start_time'])) {
            $set['start_time'] = $data['start_time'];
        }
        if (!empty($data['end_date'])) {
            $set['end_date'] = date('Y-m-d', strtotime($data['end_date']));
        }
        if (!empty($data['end_time'])) {
            $set['end_time'] = $data['end_time'];
        }
        if (!empty($data['car_id'])) {
            $set['car_id'] = $data['car_id'];
        }
        if (!empty($data['car_code'])) {
            $set['car_code'] = $data['car_code'];
        }
        if (!empty($data['car_name'])) {
            $set['car_name'] = \DB::expr('HEX(AES_ENCRYPT("'.$data['car_name'].'","'.$encrypt_key.'"))');
        }
        if (!empty($data['customer_code'])) {
            $set['customer_code'] = $data['customer_code'];
        }
        if (!empty($data['customer_name'])) {
            $set['customer_name'] = \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))');
        }
        if (!empty($data['consumer_name'])) {
            $set['consumer_name'] = \DB::expr('HEX(AES_ENCRYPT("'.$data['consumer_name'].'","'.$encrypt_key.'"))');
        }
        if (!empty($data['unit_id'])) {
            $set['unit_id'] = $data['unit_id'];
        }
        if (!empty($data['request_class'])) {
            $set['request_class'] = $data['request_class'];
        }
        if (!empty($data['request_memo'])) {
            $set['request_memo'] = $data['request_memo'];
        }
        if (!empty($data['memo'])) {
            $set['memo'] = $data['memo'];
        }
        if (!empty($data['text_color'])) {
            $set['text_color'] = $data['text_color'];
        }
        if (!empty($data['back_color'])) {
            $set['back_color'] = $data['back_color'];
        }
        if (!empty($data['cancel'])) {
            $set['cancel']      = $data['cancel'];
            $set['cancel_flg']  = 'YES';
        }
        if (!empty($data['carry_flg'])) {
            $set['carry_flg']   = $data['carry_flg'];
        }
        if (!empty($data['title'])) {
            $set['title'] = \DB::expr('HEX(AES_ENCRYPT("'.$data['title'].'","'.$encrypt_key.'"))');
        }

        // テーブル
        $stmt = \DB::update('t_schedule')->set(array_merge($set, self::getEtcData(false)));

        // 予約ID
        $stmt->where('id', '=', $data['id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return $data['id'];
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
        if ($result = self::getScheduleById($conditions['id'], $db)) {
            if (!self::delSchedule($conditions, $db)) {
                \Log::error(\Config::get('m_SC0031')."[schedule_id:".$conditions['id']."]");
                return \Config::get('m_SC0031');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('SC0031', AuthConfig::getAuthConfig('user_name').\Config::get('m_SC0031'), '予約情報キャンセル', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 配車データ削除
     */
    public static function delSchedule($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        // 項目セット
        if (!empty($data['cancel']) && !empty($data['cancel_flg'])) {
            $set['cancel']      = $data['cancel'];
            $set['cancel_flg']  = $data['cancel_flg'];
            $set['text_color']  = '#FFFFFF';
            $set['back_color']  = '#7A7A7A';
            $set['del_flg']     = 'YES';
        }

        // テーブル
        $stmt = \DB::update('t_schedule')->set(array_merge($set, self::getEtcData(false)));

        // 予約ID
        $stmt->where('id', '=', $data['id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //===========================   対象予約完了   ==============================//
    //=========================================================================//
    public static function complete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        if ($result = self::getScheduleById($conditions['id'], $db)) {
            if (!self::commitSchedule($conditions, $db)) {
                \Log::error(\Config::get('m_SC0033')."[schedule_id:".$conditions['id']."]");
                return \Config::get('m_SC0033');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('SC0033', AuthConfig::getAuthConfig('user_name').\Config::get('m_SC0033'), '予約情報完了', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 配車データ削除
     */
    public static function commitSchedule($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        // 項目セット
        if (!empty($data['commit']) && !empty($data['commit_flg'])) {
            $set['commit']      = $data['commit'];
            $set['commit_flg']  = $data['commit_flg'];
        }

        // テーブル
        $stmt = \DB::update('t_schedule')->set(array_merge($set, self::getEtcData(false)));

        // 予約ID
        $stmt->where('id', '=', $data['id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //======================   対象更新（日付or時間変更）   ======================//
    //=========================================================================//
    public static function change_datetime_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // 対象検索
        if ($result = self::getScheduleById($conditions['id'], $db)) {
            // 対象更新
            if (!self::changeSchedule($conditions, $db)) {
                \Log::error(\Config::get('m_SC0028')."[schedule_id:".$conditions['id']."][unit_id:".$conditions['unit_id']."]");
                return \Config::get('m_SC0028');
            }
        } else {
            return \Config::get('m_SC0029');
        }

        return null;
    }

    /**
     * 予約日時情報更新
     */
    public static function changeSchedule($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        if (!empty($data['unit_id'])) {
            $set['unit_id'] = $data['unit_id'];
        }
        if (!empty($data['start_date'])) {
            $set['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        }
        if (!empty($data['start_time'])) {
            $set['start_time'] = $data['start_time'];
        }
        if (!empty($data['end_date'])) {
            $set['end_date'] = date('Y-m-d', strtotime($data['end_date']));
        }
        if (!empty($data['end_time'])) {
            $set['end_time'] = $data['end_time'];
        }

        // テーブル
        $stmt = \DB::update('t_schedule')->set(array_merge($set, self::getEtcData(false)));

        // 車両コード
        $stmt->where('id', '=', $data['id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return $data['id'];
        }
        return false;
    }

    //=========================================================================//
    //==============================   取得データ  =============================//
    //=========================================================================//
    /**
     * ID別予約スケジュール取得
     */
    public static function getScheduleById($id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.id', 'id'),
                array('t.unit_id', 'unit_id'),
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
                array(\DB::expr("DATE_FORMAT(t.start_date,'%Y-%m-%d')"), 'start_date'),
                array(\DB::expr("DATE_FORMAT(t.end_date,'%Y-%m-%d')"), 'end_date'),
                array('t.start_time', 'start_time'),
                array('t.end_time', 'end_time'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.title),'".$encrypt_key."')"), 'title'),
                array(\DB::expr("REPLACE(REPLACE(REPLACE(t.request_memo, '\r\n', ''), '\r', ''), '\n', '') "), 'request_memo'),
                array(\DB::expr("REPLACE(REPLACE(REPLACE(t.memo, '\r\n', ''), '\r', ''), '\n', '') "), 'memo'),
                array('t.car_id', 'car_id'),
                array('t.car_code', 'car_code'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.car_name),'".$encrypt_key."')"), 'car_name'),
                array('t.request_class', 'request_class_id'),
                array(\DB::expr("
                    CASE
                        WHEN t.request_class = 'delivery' THEN '配達'
                        WHEN t.request_class = 'pick_up' THEN '引取り'
                        WHEN t.request_class = 'extradition' THEN '引渡し'
                        WHEN t.request_class = 'business_trip' THEN '出張'
                        WHEN t.request_class = 'shipping' THEN '発送'
                        WHEN t.request_class = 'inspection' THEN '点検'
                        ELSE 'その他'
                    END
                    "), 'request_class'),
                array(\DB::expr("CASE WHEN t.cancel_flg = 'YES' THEN 1 ELSE 0 END"), 'cancel_flg'),
                array(\DB::expr("CASE WHEN t.commit_flg = 'YES' THEN 1 ELSE 0 END"), 'commit_flg'),
                array(\DB::expr("IFNULL(t.cancel,'0')"), 'cancel'),
                array(\DB::expr("IFNULL(t.commit,'0')"), 'commit'),
                array('t.back_color', 'back_color'),
                array('t.text_color', 'text_color'),
                array('t.carry_flg', 'carry_flg')
                // array(\DB::expr("IFNULL(m_menu.back_color,m_menu_class.back_color)"), 'back_color'),
                // array(\DB::expr("IFNULL(m_menu.fore_color,m_menu_class.fore_color)"), 'fore_color')
                );

        // テーブル
        $stmt->from(array('t_schedule', 't'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 't.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 't.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ->join(array('m_system_config', 'mc'), 'LEFT')
            ->on('mc.system_number', '=', \DB::expr("1"))
        ;
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // レコードID
        $stmt->where('t.id', '=', $id);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * ユニット別予約スケジュール取得
     */
    public static function getScheduleByUnit($item = array(), $schedule_type = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.id', 'id'),
                array('t.unit_id', 'unit_id'),
                array('t.customer_code', 'customer_code'),
                array(\DB::expr("
                    CASE
                        WHEN t.customer_name IS NULL THEN AES_DECRYPT(UNHEX(m.name),'".$encrypt_key."')
                        ELSE AES_DECRYPT(UNHEX(t.customer_name),'".$encrypt_key."')
                    END
                    "), 'customer_name'),
                array(\DB::expr("DATE_FORMAT(t.start_date,'%Y-%m-%d')"), 'start_date'),
                array(\DB::expr("DATE_FORMAT(t.end_date,'%Y-%m-%d')"), 'end_date'),
                array('t.start_time', 'start_time'),
                array('t.end_time', 'end_time'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.title),'".$encrypt_key."')"), 'title'),
                array(\DB::expr("REPLACE(REPLACE(REPLACE(AES_DECRYPT(UNHEX(t.memo),'".$encrypt_key."'), '\r\n', ''), '\r', ''), '\n', '') "), 'memo'),
                array('t.car_id', 'car_id'),
                array('t.car_code', 'car_code'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.car_name),'".$encrypt_key."')"), 'car_name'),
                array(\DB::expr("IFNULL(t.cancel,'0')"), 'cancel'),
                array(\DB::expr("IFNULL(t.commit,'0')"), 'commit'),
                array('t.back_color', 'back_color'),
                array('t.text_color', 'text_color'),
                array('t.carry_flg', 'carry_flg')
                // array(\DB::expr("IFNULL(m_menu.back_color,m_menu_class.back_color)"), 'back_color'),
                // array(\DB::expr("IFNULL(m_menu.fore_color,m_menu_class.fore_color)"), 'fore_color')
                );

        // テーブル
        $stmt->from(array('t_schedule', 't'))
        ->join(array('m_unit', 'mu'), 'INNER')
            ->on('mu.id', '=', 't.unit_id')
            ->on('mu.schedule_type', '=', \DB::expr("'".$schedule_type."'"))
            ->on('mu.del_flg', '=', \DB::expr("'NO'"))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 't.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 't.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // ユニットID
        if (!empty($item['unit_id'])) {
            $stmt->where('t.unit_id', '=', $item['unit_id']);
        }
        // お客様番号
        if (!empty($item['customer_code'])) {
            // $stmt->where('t.customer_code', '=', $item['customer_code']);
        }
        // ソート
        $stmt->order_by('t.id', 'ASC');
        // 検索実行
        $res = $stmt->execute($db)->as_array();

        if (!empty($res)) {
            if (!empty($item['customer_code'])) {
                foreach ($res as $key => $val) {
                    if ($val['customer_code'] == $item['customer_code']) {
                        $val['back_color'] = '#006400';
                        $val['text_color'] = '#FFFFFF';
                    } else {
                        $val['back_color'] = '#DCDCDC';
                        $val['text_color'] = '#000000';
                    }
                    $data[] = $val;
                }
                return $data;
            } else {
                return $res;
            }
        }
        return false;
    }

    /**
     * 個別予約スケジュール取得
     */
    public static function getScheduleByCustomer($item = array(), $schedule_type = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($item)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.id', 'id'),
                array(\DB::expr("DATE_FORMAT(t.start_date,'%Y-%m-%d')"), 'schedule_day'),
                array(\DB::expr("DATE_FORMAT(IFNULL(t.end_date,t.start_date), '%Y%m%d')"), 'schedule_day_to'),
                array('t.start_time', 'start_time'),
                array('t.end_time', 'end_time'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.title),'".$encrypt_key."')"), 'title')
                // array(\DB::expr("IFNULL(t.color,'0')"), 'color'),
                // array(\DB::expr("IFNULL(t.save_flg,'1')"), 'save_flg')
                );

        // テーブル
        $stmt->from(array('t_schedule', 't'))
        ->join(array('m_unit', 'mu'), 'INNER')
            ->on('mu.id', '=', 't.unit_id')
            ->on('mu.schedule_type', '=', \DB::expr("'".$schedule_type."'"))
            ->on('mu.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // お客様コード
        $stmt->where('t.customer_code', '=', $item['customer_code']);
        // if (!empty($item['customer_code'])) {
        //     $stmt->where('t.customer_code', '=', $item['customer_code']);
        // }
        // 期間
        $stmt->where('t.start_date', 'BETWEEN', array($item['default_day'], $item['default_day']));

        // ソート
        $stmt->order_by('t.start_date', 'ASC')->order_by(\DB::expr("CAST(REPLACE(IFNULL(t.start_time,'00:00'),':','') AS DECIMAL)"), 'ASC')->order_by(\DB::expr("AES_DECRYPT(UNHEX(t.title),'".$encrypt_key."')"), 'ASC');

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * ユニットマスタ取得
     */
    public static function getUnit($code = null, $schedule_type = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('m.id', 'unit_id'),
                array('m.schedule_type', 'schedule_type'),
                array('m.name', 'unit_name')
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));

        // 予約タイプ
        if (!empty($schedule_type)) {
            $stmt->where('m.schedule_type', '=', $schedule_type);
        }
        // ユニットコード
        if (!empty($code)) {
            $stmt->where('m.id', '=', $code);
        }
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
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

    //=========================================================================//
    //=========================   カレンダーデータ  =============================//
    //=========================================================================//
    /**
     * カレンダー表示処理
     */
    // カレンダー表示処理
    public static function CalendarView($url, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $html  = "";
        $html .= "<table>";
        $html .= "<tr>";
        $html .= "<td>";
        $current_year  = date('Y');
        $current_month = date('n');

        $today1 = $current_year."/".$current_month."/1";

            // カレンダー表示
        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+1 month ".$today1));
        $current_month = date("n",strtotime("+1 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+2 month ".$today1));
        $current_month = date("n",strtotime("+2 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td>";
        $current_year  = date("Y",strtotime("+3 month ".$today1));
        $current_month = date("n",strtotime("+3 month ".$today1));

            // カレンダー表示
        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+4 month ".$today1));
        $current_month = date("n",strtotime("+4 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+5 month ".$today1));
        $current_month = date("n",strtotime("+5 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td>";
        $current_year  = date("Y",strtotime("+6 month ".$today1));
        $current_month = date("n",strtotime("+6 month ".$today1));

            // カレンダー表示
        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+7 month ".$today1));
        $current_month = date("n",strtotime("+7 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+8 month ".$today1));
        $current_month = date("n",strtotime("+8 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td>";
        $current_year  = date("Y",strtotime("+9 month ".$today1));
        $current_month = date("n",strtotime("+9 month ".$today1));

            // カレンダー表示
        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+10 month ".$today1));
        $current_month = date("n",strtotime("+10 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "<td>";

        $current_year  = date("Y",strtotime("+11 month ".$today1));
        $current_month = date("n",strtotime("+11 month ".$today1));

        $html .= self::calendar($url, $current_year,$current_month,$db);
        $html .= "</td>";
        $html .= "</tr>";
        $html .= "</table>";

        return $html;
    }

    // カレンダー表示処理
    public static function calendar($url, $year = '', $month = '', $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($year) && empty($month)) {
            $year = date('Y');
            $month = date('n');
        }

        //月末の取得
        $l_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));

        $html = "<table class=\"calendar\" style=\"border-collapse: collapse;\">";
        $html .= "<caption style=\"text-align:center;font-weight:bold;\">\n";
        $html .= $year."年".$month."月\n";
        $html .= "</caption>\n";
        $html .= "<tr>\n";
        $html .= "<th class=\"sun\">".\Html::anchor('#', '日', array('class'=>'sun', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'0'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '月', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'1'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '火', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'2'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '水', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'3'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '木', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'4'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '金', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'5'))."</th>\n";
        $html .= "<th class=\"sat\">".\Html::anchor('#', '土', array('class'=>'sat', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'6'))."</th>\n";
        $html .= "</tr>\n";

        // カレンダーデータ取得
        $list = self::getCalendar($year, $month, self::$db);

        $holidays   = array();
        $holidays2  = array();
        if (!empty($list)) {
            foreach ($list as $key => $row) {
                array_push($holidays, $row['holiday']);
            }
        }
        $lc = 0;
        $tab = '';

        // 月末まで繰り返す
        for ($i = 1; $i < $l_day + 1;$i++) {
            $classes = array();
            $class   = '';

            // 曜日の取得
            $week = date('w', mktime(0, 0, 0, $month, $i, $year));

            // 曜日が日曜日の場合
            if ($week == 0) {
                $html .= $tab."\t\t<tr>\n";
                $lc++;
            }

            // 1日の場合、それよりも前のブランクを生成
            if ($i == 1) {
                if($week != 0) {
                    $html .= $tab."\t\t<tr>\n";
                    $lc++;
                }
                $html .= str_repeat("\t\t<td> </td>\n", $week);
            }

            //土曜と日曜を設定
            $classes[] = 'no';
            if ($week == 6) {
                $classes[] = 'sat';
            } else if ($week == 0) {
                $classes[] = 'sun';
            }
            // 「今日」の日付の場合
            if ($i == date('j') && $year == date('Y') && $month == date('n')) {
                $classes[] = 'today';
            }

            //cssクラスを設定
            if (count($classes) > 0) {
                $class = ' class="'.implode(' ', $classes).'"';
            }

            //休日かどうかを設定
            $style  = '';
            $today  = date("Y-m-d",mktime(0, 0, 0, $month , $i, $year));
            $mode   = '0';    //0：平日 1：休日 2：出荷お休み
            if (in_array($today, $holidays)) {
                if (in_array($today, $holidays2)) {
                    $mode = '2';
                    $style = ' style="background-color:#FFFFCC;"';
                } else {
                    $mode = '1';
                    $style = ' style="background-color:#FFD2E1;"';
                }
            }

            //日付をひとつ作成
            $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="'.$url."?y=".$year."&m=".sprintf('%02d', $month)."&d=".sprintf('%02d', $i).'">'.$i.'</a></td>'."\n";
            // switch ($mode) {
            //     case '1':   //休日
            //         $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="0" >'.$i.'</a></td>'."\n";
            //     break;
            //     case '2':   //出荷お休み
            //         $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="2" >'.$i.'</a></td>'."\n";
            //     break;
            //     default:    //平日
            //         $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="1" >'.$i.'</a></td>'."\n";
            //     break;
            // }

            // 月末の場合、週の残りをブランクにする
            if ($i == $l_day) {
                $html .= str_repeat("\t\t<td> </td>\n", (6 - $week));
            }

            // 土曜日の場合
            if ($week == 6) {
                $html .= $tab."\t\t</tr>\n";
            }
        }

        if ($lc < 6) {
            $html .= "\t<tr>\n";
            $html .= str_repeat("\t\t<td>　</td>\n", 7);
            $html .= "\t</tr>\n";
        }

        if ($lc == 4) {
            $html .= "\t<tr>\n";
            $html .= str_repeat("\t\t<td>　</td>\n", 7);
            $html .= "\t</tr>\n";
        }

        $html .= "</table>\n";

        return $html;
    }

    /**
     * カレンダーデータ取得
     */
    public static function getCalendar($year, $month, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        $start_day  = date("Y/m/d",mktime(0, 0, 0, $month, 1, $year));
        $end_day    = date("Y/m/d",mktime(0, 0, 0, $month + 1, 0, $year));

        return \DB::select(
            array('m.holiday', 'holiday'),
            array('m.comment', 'comment')
        )
        ->from(array('calendar_holiday', 'm'))
        ->where('m.del_flg', 'NO')
        ->where('m.holiday', 'BETWEEN', array($start_day, $end_day))
        ->order_by('m.holiday')
        ->execute($db)
        ->as_array();
        ;
    }

    /**
     * カレンダーデータ全件取得
     */
    public static function getCalendarAll($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $list   = array();
        $res    = \DB::select(
            array('m.holiday', 'holiday'),
            array('m.comment', 'comment')
        )
        ->from(array('calendar_holiday', 'm'))
        ->where('m.del_flg', 'NO')
        ->order_by('m.holiday')
        ->execute($db)
        ->as_array();
        ;

        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $list[] = $val['holiday'];
            }
        }

        return $list;
    }

    //=========================================================================//
    //===========================   車両情報データ  =============================//
    //=========================================================================//
    /**
     * 車両情報取得
     */
    public static function getCar($code = null, $name = null, $db = null) {

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
                array(\DB::expr('(SELECT AES_DECRYPT(UNHEX(name), "'.$encrypt_key.'") FROM m_customer WHERE customer_code = m.customer_code)'), 'customer_name'),
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
        // 結合テーブル
        $stmt->join(array('m_system_config', 'ms'), 'LEFT')
                ->on('ms.system_number', '=', \DB::expr("'1'"))
        ;

        // 車両番号
        if (!empty($code)) {
            $stmt->where('m.car_code', '=', $code);
        }
        // 車種
        if (!empty($name)) {
            $stmt->where('m.car_name', 'LIKE', '%'.$name.'%');
        }
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    //=========================================================================//
    //======================   対象予約ユーザーチェック   ========================//
    //=========================================================================//
    public static function check_user_schedule($conditions, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.id', 'id'),
                array('t.unit_id', 'unit_id'),
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
                array(\DB::expr("DATE_FORMAT(t.start_date,'%Y-%m-%d')"), 'start_date'),
                array(\DB::expr("DATE_FORMAT(t.end_date,'%Y-%m-%d')"), 'end_date'),
                array('t.start_time', 'start_time'),
                array('t.end_time', 'end_time'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.title),'".$encrypt_key."')"), 'title'),
                array(\DB::expr("REPLACE(REPLACE(REPLACE(t.request_memo, '\r\n', ''), '\r', ''), '\n', '') "), 'request_memo'),
                array(\DB::expr("REPLACE(REPLACE(REPLACE(t.memo, '\r\n', ''), '\r', ''), '\n', '') "), 'memo'),
                array('t.car_id', 'car_id'),
                array('t.car_code', 'car_code'),
                array(\DB::expr("AES_DECRYPT(UNHEX(t.car_name),'".$encrypt_key."')"), 'car_name'),
                array('t.request_class', 'request_class_id'),
                array(\DB::expr("
                    CASE
                        WHEN t.request_class = 'delivery' THEN '配達'
                        WHEN t.request_class = 'pick_up' THEN '引取り'
                        WHEN t.request_class = 'extradition' THEN '引渡し'
                        WHEN t.request_class = 'business_trip' THEN '出張'
                        WHEN t.request_class = 'shipping' THEN '発送'
                        WHEN t.request_class = 'inspection' THEN '点検'
                        ELSE 'その他'
                    END
                    "), 'request_class'),
                array(\DB::expr("CASE WHEN t.cancel_flg = 'YES' THEN 1 ELSE 0 END"), 'cancel_flg'),
                array(\DB::expr("CASE WHEN t.commit_flg = 'YES' THEN 1 ELSE 0 END"), 'commit_flg'),
                array(\DB::expr("IFNULL(t.cancel,'0')"), 'cancel'),
                array(\DB::expr("IFNULL(t.commit,'0')"), 'commit'),
                array('t.back_color', 'back_color'),
                array('t.text_color', 'text_color'),
                array('t.carry_flg', 'carry_flg')
                // array(\DB::expr("IFNULL(m_menu.back_color,m_menu_class.back_color)"), 'back_color'),
                // array(\DB::expr("IFNULL(m_menu.fore_color,m_menu_class.fore_color)"), 'fore_color')
                );

        // テーブル
        $stmt->from(array('t_schedule', 't'))
        ->join(array('m_customer', 'm'), 'LEFT')
            ->on('m.customer_code', '=', 't.customer_code')
        ->join(array('m_car', 'ca'), 'LEFT')
            ->on('ca.id', '=', 't.car_id')
            ->on('ca.del_flg', '=', \DB::expr("'NO'"))
        ->join(array('m_system_config', 'mc'), 'LEFT')
            ->on('mc.system_number', '=', \DB::expr("1"))
        ;
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // レコードID
        $stmt->where('t.id', '=', $conditions['id']);
        // お客様コード
        $stmt->where('t.customer_code', '=', $conditions['customer_code']);

        // 検索実行
        return $stmt->execute($db)->current();
    }

}