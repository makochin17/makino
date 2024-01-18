<?php
namespace Model\Car;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class C0021 extends \Model {

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
    //===========================   車両情報データ  =============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getCar($code, $customer_code = null, $db = null) {

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

        // 車両コード
        $stmt->where('m.id', '=', $code);
        // お客様コード
        if (!empty($customer_code)) {
            $stmt->where('m.customer_code', '=', $customer_code);
        }
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