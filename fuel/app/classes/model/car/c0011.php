<?php
namespace Model\Car;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class C0011 extends \Model {

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

    // ヘッダーデータ
    public static function getHeaders($type = 'csv') {

        $res = array();
        switch ($type) {
            case 'car':
            case 'csv':
            default:
                $res = array(
                    'car_id'                            => '車両情報ID',
                    'old_car_id'                        => '車両ID',
                    'car_code'                          => '登録番号',
                    'customer_code'                     => 'お客様番号',
                    'customer_name'                     => 'お客様名',
                    'owner_name'                        => '所有者',
                    'consumer_name'                     => '使用者',
                    'car_name'                          => '車種',
                    'work_required_time'                => '作業所要時間',
                    'total_mileage'                     => '総走行距離',
                    'summer_tire_maker'                 => '夏タイヤメーカー',
                    'summer_tire_product_name'          => '夏タイヤ商品名',
                    'summer_tire_size'                  => '夏タイヤサイズ',
                    'summer_tire_size2'                 => '夏タイヤサイズ２',
                    'summer_tire_pattern'               => '夏タイヤタイヤパターン',
                    'summer_tire_wheel_product_name'    => '夏タイヤホイール商品名',
                    'summer_tire_wheel_size'            => '夏タイヤホイールサイズ',
                    'summer_tire_wheel_size2'           => '夏タイヤホイールサイズ２',
                    'summer_tire_made_date'             => '夏タイヤ製造年',
                    'summer_tire_remaining_groove1'     => '夏タイヤ残溝数１',
                    'summer_tire_remaining_groove2'     => '夏タイヤ残溝数２',
                    'summer_tire_remaining_groove3'     => '夏タイヤ残溝数３',
                    'summer_tire_remaining_groove4'     => '夏タイヤ残溝数４',
                    'summer_tire_punk'                  => '夏タイヤパンク',
                    'summer_nut_flg'                    => '夏タイヤナット',
                    'summer_location_id'                => '夏タイヤ保管場所',
                    'winter_tire_maker'                 => '冬タイヤメーカー',
                    'winter_tire_product_name'          => '冬タイヤ商品名',
                    'winter_tire_size'                  => '冬タイヤサイズ',
                    'winter_tire_size2'                 => '冬タイヤサイズ２',
                    'winter_tire_pattern'               => '冬タイヤタイヤパターン',
                    'winter_tire_wheel_product_name'    => '冬タイヤホイール商品名',
                    'winter_tire_wheel_size'            => '冬タイヤホイールサイズ',
                    'winter_tire_wheel_size2'           => '冬タイヤホイールサイズ２',
                    'winter_tire_made_date'             => '冬タイヤ製造年',
                    'winter_tire_remaining_groove1'     => '冬タイヤ残溝数１',
                    'winter_tire_remaining_groove2'     => '冬タイヤ残溝数２',
                    'winter_tire_remaining_groove3'     => '冬タイヤ残溝数３',
                    'winter_tire_remaining_groove4'     => '冬タイヤ残溝数４',
                    'winter_tire_punk'                  => '冬タイヤパンク',
                    'winter_nut_flg'                    => '冬タイヤナット',
                    'winter_location_id'                => '冬タイヤ保管場所',
                    'summer_class_flg'                  => '保管区分夏',
                    'winter_class_flg'                  => '保管区分冬',
                    'summer_tire_img_path1'             => '夏タイヤ写真①',
                    'summer_tire_img_path2'             => '夏タイヤ写真②',
                    'summer_tire_img_path3'             => '夏タイヤ写真③',
                    'summer_tire_img_path4'             => '夏タイヤ写真④',
                    'winter_tire_img_path1'             => '冬タイヤ写真①',
                    'winter_tire_img_path2'             => '冬タイヤ写真②',
                    'winter_tire_img_path3'             => '冬タイヤ写真③',
                    'winter_tire_img_path4'             => '冬タイヤ写真④',
                    'note'                              => '注意事項',
                    'message'                           => 'メッセージ',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
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
                    'total_mileage'                     => '',
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
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録
        $insert_id = self::addCar($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_CAR008')."[".print_r($conditions,true)."]");
            return \Config::get('m_CAR008');
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
    public static function addCar($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'old_car_id'                        => $data['old_car_id'],
            'car_code'                          => $data['car_code'],
            'customer_code'                     => $data['customer_code'],
            'owner_name'                        => $data['owner_name'],
            'consumer_name'                     => $data['consumer_name'],
            'car_name'                          => $data['car_name'],
            'work_required_time'                => $data['work_required_time'],
            'total_mileage'                     => (!empty($data['total_mileage'])) ? $data['total_mileage']:0,
            'summer_tire_maker'                 => (!empty($data['summer_tire_maker'])) ? $data['summer_tire_maker']:null,
            'summer_tire_product_name'          => (!empty($data['summer_tire_product_name'])) ? $data['summer_tire_product_name']:null,
            'summer_tire_size'                  => (!empty($data['summer_tire_size'])) ? $data['summer_tire_size']:null,
            'summer_tire_size2'                 => (!empty($data['summer_tire_size2'])) ? $data['summer_tire_size2']:null,
            'summer_tire_pattern'               => (!empty($data['summer_tire_pattern'])) ? $data['summer_tire_pattern']:null,
            'summer_tire_wheel_product_name'    => (!empty($data['summer_tire_wheel_product_name'])) ? $data['summer_tire_wheel_product_name']:null,
            'summer_tire_wheel_size'            => (!empty($data['summer_tire_wheel_size'])) ? $data['summer_tire_wheel_size']:null,
            'summer_tire_wheel_size2'           => (!empty($data['summer_tire_wheel_size2'])) ? $data['summer_tire_wheel_size2']:null,
            'summer_tire_made_date'             => (!empty($data['summer_tire_made_date'])) ? $data['summer_tire_made_date']:null,
            'summer_tire_remaining_groove1'     => (!empty($data['summer_tire_remaining_groove1'])) ? str_replace(',', '', $data['summer_tire_remaining_groove1']):0.00,
            'summer_tire_remaining_groove2'     => (!empty($data['summer_tire_remaining_groove2'])) ? str_replace(',', '', $data['summer_tire_remaining_groove2']):0.00,
            'summer_tire_remaining_groove3'     => (!empty($data['summer_tire_remaining_groove3'])) ? str_replace(',', '', $data['summer_tire_remaining_groove3']):0.00,
            'summer_tire_remaining_groove4'     => (!empty($data['summer_tire_remaining_groove4'])) ? str_replace(',', '', $data['summer_tire_remaining_groove4']):0.00,
            'summer_tire_punk'                  => (!empty($data['summer_tire_punk'])) ? $data['summer_tire_punk']:null,
            'summer_nut_flg'                    => $data['summer_nut_flg'],
            'summer_location_id'                => (!empty($data['summer_location_id'])) ? $data['summer_location_id']:0,
            'winter_tire_maker'                 => (!empty($data['winter_tire_maker'])) ? $data['winter_tire_maker']:null,
            'winter_tire_product_name'          => (!empty($data['winter_tire_product_name'])) ? $data['winter_tire_product_name']:null,
            'winter_tire_size'                  => (!empty($data['winter_tire_size'])) ? $data['winter_tire_size']:null,
            'winter_tire_size2'                 => (!empty($data['winter_tire_size2'])) ? $data['winter_tire_size2']:null,
            'winter_tire_pattern'               => (!empty($data['winter_tire_pattern'])) ? $data['winter_tire_pattern']:null,
            'winter_tire_wheel_product_name'    => (!empty($data['winter_tire_wheel_product_name'])) ? $data['winter_tire_wheel_product_name']:null,
            'winter_tire_wheel_size'            => (!empty($data['winter_tire_wheel_size'])) ? $data['winter_tire_wheel_size']:null,
            'winter_tire_wheel_size2'           => (!empty($data['winter_tire_wheel_size2'])) ? $data['winter_tire_wheel_size2']:null,
            'winter_tire_made_date'             => (!empty($data['winter_tire_made_date'])) ? $data['winter_tire_made_date']:null,
            'winter_tire_remaining_groove1'     => (!empty($data['winter_tire_remaining_groove1'])) ? str_replace(',', '', $data['winter_tire_remaining_groove1']):0.00,
            'winter_tire_remaining_groove2'     => (!empty($data['winter_tire_remaining_groove2'])) ? str_replace(',', '', $data['winter_tire_remaining_groove2']):0.00,
            'winter_tire_remaining_groove3'     => (!empty($data['winter_tire_remaining_groove3'])) ? str_replace(',', '', $data['winter_tire_remaining_groove3']):0.00,
            'winter_tire_remaining_groove4'     => (!empty($data['winter_tire_remaining_groove4'])) ? str_replace(',', '', $data['winter_tire_remaining_groove4']):0.00,
            'winter_tire_punk'                  => (!empty($data['winter_tire_punk'])) ? $data['winter_tire_punk']:null,
            'winter_nut_flg'                    => $data['winter_nut_flg'],
            'winter_location_id'                => (!empty($data['winter_location_id'])) ? $data['winter_location_id']:0,
            'summer_class_flg'                  => $data['summer_class_flg'],
            'winter_class_flg'                  => $data['winter_class_flg'],
            'note'                              => $data['note'],
            'message'                           => $data['message'],
            'start_date'                        => \Date::forge()->format('mysql_date'),
            'end_date'                          => \Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('m_car')->set($set)->execute($db);

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
        if ($result = self::getCar($conditions['car_id'], $db)) {
            if (!self::updCar($conditions, $db)) {
                \Log::error(\Config::get('m_CAR008')."[customer_code:".$conditions['car_code']."]");
                return \Config::get('m_CAR008');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('CAR005', AuthConfig::getAuthConfig('user_name').\Config::get('m_CAR005'), '車両更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * お客様情報更新
     */
    public static function updCar($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'old_car_id'                        => $data['old_car_id'],
            'car_code'                          => $data['car_code'],
            'customer_code'                     => $data['customer_code'],
            'owner_name'                        => $data['owner_name'],
            'consumer_name'                     => $data['consumer_name'],
            'car_name'                          => $data['car_name'],
            'work_required_time'                => $data['work_required_time'],
            'total_mileage'                     => (!empty($data['total_mileage'])) ? $data['total_mileage']:0,
            'summer_tire_maker'                 => (!empty($data['summer_tire_maker'])) ? $data['summer_tire_maker']:null,
            'summer_tire_product_name'          => (!empty($data['summer_tire_product_name'])) ? $data['summer_tire_product_name']:null,
            'summer_tire_size'                  => (!empty($data['summer_tire_size'])) ? $data['summer_tire_size']:null,
            'summer_tire_size2'                 => (!empty($data['summer_tire_size2'])) ? $data['summer_tire_size2']:null,
            'summer_tire_pattern'               => (!empty($data['summer_tire_pattern'])) ? $data['summer_tire_pattern']:null,
            'summer_tire_wheel_product_name'    => (!empty($data['summer_tire_wheel_product_name'])) ? $data['summer_tire_wheel_product_name']:null,
            'summer_tire_wheel_size'            => (!empty($data['summer_tire_wheel_size'])) ? $data['summer_tire_wheel_size']:null,
            'summer_tire_wheel_size2'           => (!empty($data['summer_tire_wheel_size2'])) ? $data['summer_tire_wheel_size2']:null,
            'summer_tire_made_date'             => (!empty($data['summer_tire_made_date'])) ? $data['summer_tire_made_date']:null,
            'summer_tire_remaining_groove1'     => (!empty($data['summer_tire_remaining_groove1'])) ? str_replace(',', '', $data['summer_tire_remaining_groove1']):0.00,
            'summer_tire_remaining_groove2'     => (!empty($data['summer_tire_remaining_groove2'])) ? str_replace(',', '', $data['summer_tire_remaining_groove2']):0.00,
            'summer_tire_remaining_groove3'     => (!empty($data['summer_tire_remaining_groove3'])) ? str_replace(',', '', $data['summer_tire_remaining_groove3']):0.00,
            'summer_tire_remaining_groove4'     => (!empty($data['summer_tire_remaining_groove4'])) ? str_replace(',', '', $data['summer_tire_remaining_groove4']):0.00,
            'summer_tire_punk'                  => (!empty($data['summer_tire_punk'])) ? $data['summer_tire_punk']:null,
            'summer_nut_flg'                    => $data['summer_nut_flg'],
            'summer_location_id'                => (!empty($data['summer_location_id'])) ? $data['summer_location_id']:0,
            'winter_tire_maker'                 => (!empty($data['winter_tire_maker'])) ? $data['winter_tire_maker']:null,
            'winter_tire_product_name'          => (!empty($data['winter_tire_product_name'])) ? $data['winter_tire_product_name']:null,
            'winter_tire_size'                  => (!empty($data['winter_tire_size'])) ? $data['winter_tire_size']:null,
            'winter_tire_size2'                 => (!empty($data['winter_tire_size2'])) ? $data['winter_tire_size2']:null,
            'winter_tire_pattern'               => (!empty($data['winter_tire_pattern'])) ? $data['winter_tire_pattern']:null,
            'winter_tire_wheel_product_name'    => (!empty($data['winter_tire_wheel_product_name'])) ? $data['winter_tire_wheel_product_name']:null,
            'winter_tire_wheel_size'            => (!empty($data['winter_tire_wheel_size'])) ? $data['winter_tire_wheel_size']:null,
            'winter_tire_wheel_size2'           => (!empty($data['winter_tire_wheel_size2'])) ? $data['winter_tire_wheel_size2']:null,
            'winter_tire_made_date'             => (!empty($data['winter_tire_made_date'])) ? $data['winter_tire_made_date']:null,
            'winter_tire_remaining_groove1'     => (!empty($data['winter_tire_remaining_groove1'])) ? str_replace(',', '', $data['winter_tire_remaining_groove1']):0.00,
            'winter_tire_remaining_groove2'     => (!empty($data['winter_tire_remaining_groove2'])) ? str_replace(',', '', $data['winter_tire_remaining_groove2']):0.00,
            'winter_tire_remaining_groove3'     => (!empty($data['winter_tire_remaining_groove3'])) ? str_replace(',', '', $data['winter_tire_remaining_groove3']):0.00,
            'winter_tire_remaining_groove4'     => (!empty($data['winter_tire_remaining_groove4'])) ? str_replace(',', '', $data['winter_tire_remaining_groove4']):0.00,
            'winter_tire_punk'                  => (!empty($data['winter_tire_punk'])) ? $data['winter_tire_punk']:null,
            'winter_nut_flg'                    => $data['winter_nut_flg'],
            'winter_location_id'                => (!empty($data['winter_location_id'])) ? $data['winter_location_id']:0,
            'summer_class_flg'                  => $data['summer_class_flg'],
            'winter_class_flg'                  => $data['winter_class_flg'],
            'note'                              => $data['note'],
            'message'                           => $data['message'],
        );

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
    //==============================   対象削除   ==============================//
    //=========================================================================//
    public static function delete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        if ($result = self::getCar($conditions['car_id'], $db)) {
            if (!self::delCar($conditions['car_id'], $db)) {
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
    public static function delCar($car_id, $db = null) {

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

}