<?php
namespace Model\Logistics;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class L0011 extends \Model {

    public static $db           = 'MAKINO';

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
            case 'logistics':
            default:
                $res = array(
                    'mode'                              => '',
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
                    'total_mileage'                     => '',
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
                    'complete_flg'                      => '',
                    'schedule_id'                       => '',
                    'update_datetime'                   => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'logistics', $conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if (isset($input_data[$key])) {
                $conditions[$key] = $input_data[$key];
            }
        }

        return $conditions;
    }

    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, &$insert_id, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録
        $insert_id = self::addLogistics($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_RE0009')."[".print_r($conditions,true)."]");
            return \Config::get('m_RE0009');
        }

        // 車両情報更新(更新失敗しても処理を止めない)
        if (self::getCar($conditions['car_id'], $db)) {
            if (!self::updCar($conditions, $db)) {
                \Log::error(\Config::get('m_RE0012')."[ID:".$conditions['logistics_id']." car_code:".$conditions['car_code']." car_id:".$conditions['car_id']."]");
            }
        }

        // 操作ログ出力
        // $result = OpeLog::addOpeLog('DI0009', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0009'), '配車登録', $db);
        // if (!$result) {
        //    \Log::error(\Config::get('m_CE0007'));
        //    return \Config::get('m_CE0007');
        // }
        return null;
    }

    /**
     * お客様登録
     */
    public static function addLogistics($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'receipt_date'                      => $data['receipt_date'],
            'receipt_time'                      => $data['receipt_time'],
            'tire_type'                         => $data['tire_type'],
            'car_id'                            => $data['car_id'],
            'car_code'                          => $data['car_code'],
            'car_name'                          => \DB::expr('HEX(AES_ENCRYPT("'.$data['car_name'].'","'.$encrypt_key.'"))'),
            'customer_code'                     => $data['customer_code'],
            'customer_name'                     => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))'),
            'owner_name'                        => \DB::expr('HEX(AES_ENCRYPT("'.$data['owner_name'].'","'.$encrypt_key.'"))'),
            'consumer_name'                     => \DB::expr('HEX(AES_ENCRYPT("'.$data['consumer_name'].'","'.$encrypt_key.'"))'),
            'location_id'                       => (!empty($data['location_id'])) ? $data['location_id']:0,
            'total_mileage'                     => (!empty($data['total_mileage'])) ? $data['total_mileage']:0,
            'tire_maker'                        => (!empty($data['summer_tire_maker'])) ? $data['summer_tire_maker']:null,
            'tire_product_name'                 => (!empty($data['summer_tire_product_name'])) ? $data['summer_tire_product_name']:null,
            'tire_size'                         => (!empty($data['summer_tire_size'])) ? $data['summer_tire_size']:null,
            'tire_pattern'                      => (!empty($data['summer_tire_pattern'])) ? $data['summer_tire_pattern']:null,
            'tire_made_date'                    => (!empty($data['summer_tire_made_date'])) ? $data['summer_tire_made_date']:null,
            'tire_remaining_groove1'            => (!empty($data['summer_tire_remaining_groove1'])) ? str_replace(',', '', $data['summer_tire_remaining_groove1']):0.00,
            'tire_remaining_groove2'            => (!empty($data['summer_tire_remaining_groove2'])) ? str_replace(',', '', $data['summer_tire_remaining_groove2']):0.00,
            'tire_remaining_groove3'            => (!empty($data['summer_tire_remaining_groove3'])) ? str_replace(',', '', $data['summer_tire_remaining_groove3']):0.00,
            'tire_remaining_groove4'            => (!empty($data['summer_tire_remaining_groove4'])) ? str_replace(',', '', $data['summer_tire_remaining_groove4']):0.00,
            'tire_punk'                         => (!empty($data['summer_tire_punk'])) ? $data['summer_tire_punk']:null,
            'nut_flg'                           => (!empty($data['nut_flg'])) ? $data['nut_flg']:'NO',
            'receipt_flg'                       => 'YES'
        );
        if (!empty($data['delivery_schedule_date'])) {
            $set['delivery_schedule_date'] = $data['delivery_schedule_date'];
        }
        if (!empty($data['delivery_schedule_time'])) {
            $set['delivery_schedule_time'] = $data['delivery_schedule_time'];
        }
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_logistics')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        $bill_number = "";

        // レコード更新
        if ($result = self::getLogisticsById(null, $conditions['logistics_id'], $db)) {
            if (!self::updLogistics($conditions, $db)) {
                \Log::error(\Config::get('m_RE0010')."[ID:".$conditions['logistics_id']." car_code:".$conditions['car_code']." customer_code:".$conditions['customer_code']."]");
                return \Config::get('m_RE0010');
            }
        }

        // 車両情報更新(更新失敗しても処理を止めない)
        if (self::getCar($conditions['car_id'], $db)) {
            if (!self::updCar($conditions, $db)) {
                \Log::error(\Config::get('m_RE0012')."[ID:".$conditions['logistics_id']." car_code:".$conditions['car_code']." car_id:".$conditions['car_id']."]");
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('RE0005', AuthConfig::getAuthConfig('user_name').\Config::get('m_RE0005'), '入庫更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 入出庫情報更新
     */
    public static function updLogistics($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'receipt_date'                      => $data['receipt_date'],
            'receipt_time'                      => $data['receipt_time'],
            'tire_type'                         => $data['tire_type'],
            'car_id'                            => $data['car_id'],
            'car_code'                          => $data['car_code'],
            'car_name'                          => \DB::expr('HEX(AES_ENCRYPT("'.$data['car_name'].'","'.$encrypt_key.'"))'),
            'customer_code'                     => $data['customer_code'],
            'customer_name'                     => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))'),
            'owner_name'                        => \DB::expr('HEX(AES_ENCRYPT("'.$data['owner_name'].'","'.$encrypt_key.'"))'),
            'consumer_name'                     => \DB::expr('HEX(AES_ENCRYPT("'.$data['consumer_name'].'","'.$encrypt_key.'"))'),
            'location_id'                       => (!empty($data['location_id'])) ? $data['location_id']:0,
            'total_mileage'                     => (!empty($data['total_mileage'])) ? $data['total_mileage']:0,
            'tire_maker'                        => (!empty($data['tire_maker'])) ? $data['tire_maker']:null,
            'tire_product_name'                 => (!empty($data['tire_product_name'])) ? $data['tire_product_name']:null,
            'tire_size'                         => (!empty($data['tire_size'])) ? $data['tire_size']:null,
            'tire_pattern'                      => (!empty($data['tire_pattern'])) ? $data['tire_pattern']:null,
            'tire_made_date'                    => (!empty($data['tire_made_date'])) ? $data['tire_made_date']:null,
            'tire_remaining_groove1'            => (!empty($data['tire_remaining_groove1'])) ? str_replace(',', '', $data['tire_remaining_groove1']):0.00,
            'tire_remaining_groove2'            => (!empty($data['tire_remaining_groove2'])) ? str_replace(',', '', $data['tire_remaining_groove2']):0.00,
            'tire_remaining_groove3'            => (!empty($data['tire_remaining_groove3'])) ? str_replace(',', '', $data['tire_remaining_groove3']):0.00,
            'tire_remaining_groove4'            => (!empty($data['tire_remaining_groove4'])) ? str_replace(',', '', $data['tire_remaining_groove4']):0.00,
            'tire_punk'                         => (!empty($data['tire_punk'])) ? $data['tire_punk']:null,
            'nut_flg'                           => (!empty($data['nut_flg'])) ? $data['nut_flg']:'NO',
            'receipt_flg'                       => 'YES'
        );
        if (!empty($data['delivery_schedule_date'])) {
            $set['delivery_schedule_date'] = $data['delivery_schedule_date'];
        }
        if (!empty($data['delivery_schedule_time'])) {
            $set['delivery_schedule_time'] = $data['delivery_schedule_time'];
        }

        // テーブル
        $stmt = \DB::update('t_logistics')->set(array_merge($set, self::getEtcData(false)));

        // 入出庫コード
        $stmt->where('id', '=', $data['logistics_id']);
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
    //==============================   対象削除   ==============================//
    //=========================================================================//
    public static function delete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        if ($result = self::getLogistics($conditions['car_id'], $db)) {
            if (!self::delLogistics($conditions['car_id'], $db)) {
                \Log::error(\Config::get('m_CAR010')."[car_code:".$conditions['car_code']."]");
                return \Config::get('m_CAR010');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('CAR006', AuthConfig::getAuthConfig('user_name').\Config::get('m_CAR006'), '車両情報削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 配車データ削除
     */
    public static function delLogistics($car_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($car_id)) {
            return false;
        }

        // 項目セット
        $set = array(
            'end_date' => \Date::forge()->format('mysql_date'),
            'del_flg' => 'YES',
        );

        // テーブル
        $stmt = \DB::update('m_car')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('id', '=', $car_id);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('start_date', '<=', \Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', \Date::forge()->format('mysql_date'));

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //============================   入出庫データ  ==============================//
    //=========================================================================//
    /**
     * ID別入出庫データ取得
     */
    public static function getLogisticsById($type, $logistics_id, $db = null) {

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
                array('t.total_mileage', 'total_mileage'),
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
        // 入庫状態
        switch ($type) {
            case 'receipt_no':
                $stmt->where('t.receipt_flg', '=', 'NO');
                $stmt->where('t.delivery_schedule_flg', '=', 'NO');
                break;
            case 'receipt_yes':
                $stmt->where('t.receipt_flg', '=', 'YES');
                $stmt->where('t.delivery_schedule_flg', '=', 'NO');
                break;
            case 'delivery_no':
                $stmt->where('t.receipt_flg', '=', 'YES');
                $stmt->where('t.delivery_flg', '=', 'NO');
                break;
            case 'delivery_yes':
                $stmt->where('t.receipt_flg', '=', 'YES');
                $stmt->where('t.delivery_flg', '=', 'YES');
                break;
            default:
                break;
        }
        $stmt->where('t.id', '=', $logistics_id);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    //=========================================================================//
    //===========================   車両情報データ  =============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getCar($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.old_car_id', 'old_car_id'),
                array('m.car_code', 'car_code'),
                array('m.customer_code', 'customer_code'),
                array(\DB::expr('(SELECT AES_DECRYPT(UNHEX(name),"'.$encrypt_key.'") FROM m_customer WHERE customer_code = m.customer_code)'), 'customer_name'),
                array('m.owner_name', 'owner_name'),
                array('m.consumer_name', 'consumer_name'),
                array('m.car_name', 'car_name'),
                array('m.work_required_time', 'work_required_time'),
                array('m.total_mileage', 'total_mileage'),
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

        // お客様コード
        $stmt->where('m.id', '=', $code);
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
     * 車両情報更新
     */
    public static function updCar($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        // タイヤタイプの判定
        $tire_type = $data['tire_type'];
        if ($data['tire_type'] == 'other') {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'total_mileage'                                 => (!empty($data['total_mileage'])) ? $data['total_mileage']:0,
            $tire_type.'_tire_maker'                        => (!empty($data['tire_maker'])) ? $data['tire_maker']:null,
            $tire_type.'_tire_product_name'                 => (!empty($data['tire_product_name'])) ? $data['tire_product_name']:null,
            $tire_type.'_tire_size'                         => (!empty($data['tire_size'])) ? $data['tire_size']:null,
            $tire_type.'_tire_pattern'                      => (!empty($data['tire_pattern'])) ? $data['tire_pattern']:null,
            $tire_type.'_tire_made_date'                    => (!empty($data['tire_made_date'])) ? $data['tire_made_date']:null,
            $tire_type.'_tire_remaining_groove1'            => (!empty($data['tire_remaining_groove1'])) ? str_replace(',', '', $data['tire_remaining_groove1']):0.00,
            $tire_type.'_tire_remaining_groove2'            => (!empty($data['tire_remaining_groove2'])) ? str_replace(',', '', $data['tire_remaining_groove2']):0.00,
            $tire_type.'_tire_remaining_groove3'            => (!empty($data['tire_remaining_groove3'])) ? str_replace(',', '', $data['tire_remaining_groove3']):0.00,
            $tire_type.'_tire_remaining_groove4'            => (!empty($data['tire_remaining_groove4'])) ? str_replace(',', '', $data['tire_remaining_groove4']):0.00,
            $tire_type.'_tire_punk'                         => (!empty($data['tire_punk'])) ? $data['tire_punk']:null,
            $tire_type.'_nut_flg'                           => (!empty($data['nut_flg'])) ? $data['nut_flg']:'NO',
            $tire_type.'_location_id'                       => (!empty($data['location_id'])) ? $data['location_id']:0,
        );
        if ($data['tire_type'] == 'summer') {
            $set['summer_class_flg'] = 'YES';
        }
        if ($data['tire_type'] == 'winter') {
            $set['winter_class_flg'] = 'YES';
        }

        // テーブル
        $stmt = \DB::update('m_car')->set(array_merge($set, self::getEtcData(false)));

        // 車両コード
        $stmt->where('id', '=', $data['car_id']);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('start_date', '<=', \Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', \Date::forge()->format('mysql_date'));

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //===========================   保管場所データ  =============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getLocation($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'location_id'),
                array(\DB::expr('CONCAT(
                    (SELECT name FROM m_storage_warehouse WHERE id = m.storage_warehouse_id),
                    " - ",
                    (SELECT name FROM m_storage_column WHERE id = m.storage_column_id),
                    " - ",
                    (SELECT name FROM m_storage_depth WHERE id = m.storage_depth_id),
                    " - ",
                    (SELECT name FROM m_storage_height WHERE id = m.storage_height_id)
                    )'), 'location')
                );

        // テーブル
        $stmt->from(array('rel_storage_location', 'm'));
        // お客様コード
        $stmt->where('m.id', '=', $code);
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        return $stmt->execute($db)->current();
    }

}