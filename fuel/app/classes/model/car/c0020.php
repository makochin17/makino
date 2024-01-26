<?php
namespace Model\Car;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Common\GenerateList;

class C0020 extends \Model {

    public static $db       = 'MAKINO';

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

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 車両情報レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 会社マスタリスト
        $company_list = GenerateList::getCompanyList(true, $db);

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(mca.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('mca.id', 'car_id'),
                            array('mca.car_code', 'car_code'),
                            array('mca.customer_code', 'customer_code'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'customer_name'),
                            array('mca.owner_name', 'owner_name'),
                            array('mca.consumer_name', 'consumer_name'),
                            array('mca.car_name', 'car_name'),
                            array('mca.work_required_time', 'work_required_time'),

                            array('mca.summer_tire_maker', 'summer_tire_maker'),
                            array('mca.summer_tire_product_name', 'summer_tire_product_name'),
                            array('mca.summer_tire_size', 'summer_tire_size'),
                            array('mca.summer_tire_size2', 'summer_tire_size2'),
                            array('mca.summer_tire_pattern', 'summer_tire_pattern'),
                            array('mca.summer_tire_wheel_product_name', 'summer_tire_wheel_product_name'),
                            array('mca.summer_tire_wheel_size', 'summer_tire_wheel_size'),
                            array('mca.summer_tire_wheel_size2', 'summer_tire_wheel_size2'),
                            array('mca.summer_tire_made_date', 'summer_tire_made_date'),
                            array('mca.summer_tire_remaining_groove1', 'summer_tire_remaining_groove1'),
                            array('mca.summer_tire_remaining_groove2', 'summer_tire_remaining_groove2'),
                            array('mca.summer_tire_remaining_groove3', 'summer_tire_remaining_groove3'),
                            array('mca.summer_tire_remaining_groove4', 'summer_tire_remaining_groove4'),
                            array('mca.summer_tire_punk', 'summer_tire_punk'),
                            array('mca.summer_nut_flg', 'summer_nut_flg'),
                            array('mca.summer_location_id', 'summer_location_id'),

                            array('mca.winter_tire_maker', 'winter_tire_maker'),
                            array('mca.winter_tire_product_name', 'winter_tire_product_name'),
                            array('mca.winter_tire_size', 'winter_tire_size'),
                            array('mca.winter_tire_size2', 'winter_tire_size2'),
                            array('mca.winter_tire_pattern', 'winter_tire_pattern'),
                            array('mca.winter_tire_wheel_product_name', 'winter_tire_wheel_product_name'),
                            array('mca.winter_tire_wheel_size', 'winter_tire_wheel_size'),
                            array('mca.winter_tire_wheel_size2', 'winter_tire_wheel_size2'),
                            array('mca.winter_tire_made_date', 'winter_tire_made_date'),
                            array('mca.winter_tire_remaining_groove1', 'winter_tire_remaining_groove1'),
                            array('mca.winter_tire_remaining_groove2', 'winter_tire_remaining_groove2'),
                            array('mca.winter_tire_remaining_groove3', 'winter_tire_remaining_groove3'),
                            array('mca.winter_tire_remaining_groove4', 'winter_tire_remaining_groove4'),
                            array('mca.winter_tire_punk', 'winter_tire_punk'),
                            array('mca.winter_nut_flg', 'winter_nut_flg'),
                            array('mca.winter_location_id', 'winter_location_id'),

                            array('mca.summer_class_flg', 'summer_class_flg'),
                            array('mca.winter_class_flg', 'winter_class_flg'),
                            array('mca.summer_tire_img_path1', 'summer_tire_img_path1'),
                            array('mca.summer_tire_img_path2', 'summer_tire_img_path2'),
                            array('mca.summer_tire_img_path3', 'summer_tire_img_path3'),
                            array('mca.summer_tire_img_path4', 'summer_tire_img_path4'),
                            array('mca.winter_tire_img_path1', 'winter_tire_img_path1'),
                            array('mca.winter_tire_img_path2', 'winter_tire_img_path2'),
                            array('mca.winter_tire_img_path3', 'winter_tire_img_path3'),
                            array('mca.winter_tire_img_path4', 'winter_tire_img_path4'),
                            array('mca.note', 'note'),
                            array('mca.message', 'message'),

                            array(\DB::expr("''"), 'receipt_date'),

                            array('mca.start_date', 'start_date'),
                            array('mca.end_date', 'end_date')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('m_car', 'mca'))
        ->join(array('m_customer', 'mc'), 'INNER')
            ->on('mc.customer_code', '=', 'mca.customer_code')
            ->on('mc.del_flg', '=', \DB::expr("'NO'"))
            ->on('mc.resign_flg', '=', \DB::expr("'NO'"))
        ;

        // お客様番号
        if (!empty($conditions['customer_code'])) {
            $stmt->where(\DB::expr('CAST(mc.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // 車両番号
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->where('mca.car_code', 'LIKE', \DB::expr("'%".$conditions['car_code']."%'"));
        }
        // タイヤ種別
        if (!empty($conditions['class_flg'])) {
            if ($conditions['class_flg'] != 'summer_winter') {
                $stmt->where('mca.'.$conditions['class_flg'].'_class_flg', '=', 'YES');
            } else {
                $stmt->where('mca.summer_class_flg', '=', 'YES');
                $stmt->where('mca.winter_class_flg', '=', 'YES');
            }
        }
        // 警告フラグ&注意フラグ
        if (!empty($conditions['warning_flg']) && !empty($conditions['caution_flg'])) {
            $stmt->where_open();
            $stmt->where('mca.summer_tire_remaining_groove1', '<=', $company_list['summer_tire_caution']);
            $stmt->or_where('mca.winter_tire_remaining_groove1', '<=', $company_list['winter_tire_caution']);
            $stmt->where_close();
        } elseif (!empty($conditions['warning_flg'])) {
            // 警告フラグ
            $stmt->where_open();
            $stmt->where('mca.summer_tire_remaining_groove1', '<=', $company_list['summer_tire_warning']);
            $stmt->or_where('mca.winter_tire_remaining_groove1', '<=', $company_list['winter_tire_warning']);
            $stmt->where_close();
        } elseif (!empty($conditions['caution_flg'])) {
            // 注意フラグ
            $stmt->where_open();
            $stmt->where('mca.summer_tire_remaining_groove1', 'BETWEEN', array($company_list['summer_tire_warning'], $company_list['summer_tire_caution']));
            $stmt->or_where('mca.winter_tire_remaining_groove1', 'BETWEEN', array($company_list['winter_tire_warning'], $company_list['winter_tire_caution']));
            $stmt->where_close();
        }

        $stmt->where('mca.del_flg', '=', 'NO');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('mca.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('mca.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
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