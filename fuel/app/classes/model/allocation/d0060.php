<?php
namespace Model\Allocation;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

class D0060 extends \Model {

    public static $db           = 'ONISHI';

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

    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }

    /**
     * 庸車先の検索
     */
    public static function getSearchCarrier($code, $db) {
        return M0030::getCarrier($code, $db);
    }

    /**
     * 商品の検索
     */
    public static function getSearchProduct($code, $db) {
        return M0060::getProduct($code, $db);
    }

    /**
     * 車両の検索
     */
    public static function getSearchCar($code, $db) {
        return M0050::getCar($code, $db);
    }

    /**
     * 社員の検索
     */
    public static function getSearchMember($code, $db) {
        return M0010::getMember($code, $db);
    }

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // フォームデータ
    public static function getForms() {

        return array(
            // 売上補正番号
            'sales_correction_number'   => '',
            // 課
            'division_code'             => '',
            // 日付
            'from_sales_date'           => '',
            'to_sales_date'             => '',
            // 売上区分
            'sales_category_code'       => '',
            // 得意先
            'client_code'               => '',
            'client_name'               => '',
            // 傭車先
            'carrier_code'              => '',
            'carrier_name'              => '',
            // 車種
            'car_model_code'            => '',
            // 車両
            'car_code'                  => '',
            // 運転手
            'member_code'               => '',
            'driver_name'               => '',
            // 売上確定
            'sales_status'              => '',
            // 配送区分
            'delivery_category'         => '',
        );
    }

    public static function setForms($conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms();
        }

        foreach ($conditions as $key => $cols) {
            $conditions[$key] = $input_data[$key];
        }

        return $conditions;
    }

    public static function getNameById($type, $id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        switch ($type) {
            case 'client':
                return \DB::select(
                    array('client_code', 'client_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(client_name),"'.$encrypt_key.'")'), 'client_name')
                )
                ->from('m_client')
                ->where('client_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
            case 'carrier':
                return \DB::select(
                    array('carrier_code', 'carrier_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(carrier_name),"'.$encrypt_key.'")'), 'carrier_name')
                )
                ->from('m_carrier')
                ->where('carrier_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
            case 'car':
                return \DB::select(
                    array('car_code', 'car_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(car_number),"'.$encrypt_key.'")'), 'car_number'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(car_name),"'.$encrypt_key.'")'), 'car_name')
                )
                ->from('m_car')
                ->where('car_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
            case 'driver':
                return \DB::select(
                    array('member_code', 'member_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(phone_number),"'.$encrypt_key.'")'), 'phone_number')
                )
                ->from('m_member')
                ->where(\DB::expr('AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'")'), $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
        }

        return false;
    }

    // 傭車先取得（ドライバー名）
    public static function getCarrierCode($member_code = null, $driver_name = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($member_code) && empty($driver_name)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        $sql = \DB::select(
            array('d.carrier_code', 'carrier_code')
        )
        ->from(array('m_member', 'm'))
        ->join(array('m_division', 'd'), 'INNER')
            ->on('d.division_code', '=', 'm.division_code');
        if (!empty($member_code)) {
            $sql = $sql->where('m.member_code', $member_code);
        }
        if (!empty($driver_name)) {
            $sql = $sql->where(\DB::expr('AES_DECRYPT(UNHEX(m.driver_name),"'.$encrypt_key.'")'), $driver_name);
        }
        $ret = $sql->where('start_date', '<=', date('Y-m-d'))
        ->where('end_date', '>', date('Y-m-d'))
        ->execute($db)->current();

        if (!empty($ret)) {
            return $ret['carrier_code'];
        }
        return false;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 売上補正レコード検索 & 売上補正レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(t.sales_correction_number) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                        array('t.sales_correction_number', 'sales_correction_number'),
                        array('t.division_code', 'division_code'),
                        array('t.sales_status', 'sales_status'),
                        array('t.sales_category_code', 'sales_category_code'),
                        array(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'sales_category_value'),
                        array('t.client_code', 'client_code'),
                        array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                        array('t.car_model_code', 'car_model_code'),
                        array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                        array('t.car_code', 'car_code'),
                        array('t.carrier_code', 'carrier_code'),
                        array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                        array('t.member_code', 'member_code'),
                        array(\DB::expr(
                            'CASE
                            WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")
                            ELSE (SELECT AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'") FROM m_member WHERE member_code = t.member_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)
                            END'
                        ), 'driver_name'),
                        array('t.sales_date', 'sales_date'),
                        array('t.operation_count', 'operation_count'),
                        array('t.delivery_category', 'delivery_category'),
                        array('t.sales', 'sales'),
                        array('t.carrier_cost', 'carrier_cost'),
                        array('t.highway_fee', 'highway_fee'),
                        array('t.highway_fee_claim', 'highway_fee_claim'),
                        array('t.overtime_fee', 'overtime_fee'),
                        array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_sales_correction', 't'));
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');
                //->on('mcl.client_name', 'LIKE', \DB::expr("'%".$conditions['client_name']."%'"));
        }
        // 傭車先
        if (!empty($conditions['carrier_name'])) {
            $stmt->join(array('m_carrier', 'mca'), 'INNER')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime');
                //->on('mca.carrier_name', 'LIKE', \DB::expr("'%".$conditions['carrier_name']."%'"));
        }
        // 課コード
        if (!empty($conditions['division_code']) && trim($conditions['division_code']) != '000') {
            $stmt->join(array('m_division', 'md'), 'INNER')
                ->on('t.division_code', '=', 'md.division_code')
                ->on('t.division_code', '=', \DB::expr("'".$conditions['division_code']."'"));
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->join(array('m_car_model', 'mcm'), 'INNER')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
                ->on('t.car_model_code', '=', \DB::expr("'".$conditions['car_model_code']."'"));
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->join(array('m_member', 'mm'), 'INNER')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime')
                ->on(\DB::expr('AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        // 売上補正番号
        if (!empty($conditions['sales_correction_number'])) {
            $stmt->where(\DB::expr('CAST(t.sales_correction_number AS SIGNED)'), '=', $conditions['sales_correction_number']);
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先
        if (!empty($conditions['carrier_code'])) {
            $stmt->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 車両コード
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '0') {
            $stmt->where('t.car_code', '=', $conditions['car_code']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (!empty($conditions['delivery_category']) && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 売上区分
        if (!empty($conditions['sales_category_code']) && trim($conditions['sales_category_code']) != '00') {
            $stmt->where('t.sales_category_code', '=', $conditions['sales_category_code']);
        }
        // 日付
        if (!empty($conditions['from_sales_date']) && trim($conditions['to_sales_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_sales_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_sales_date'])))->format('mysql_date');
            $stmt->where('t.sales_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_sales_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_sales_date'])))->format('mysql_date');
                $stmt->where('t.sales_date', '>=', $date);
            }
            if (!empty($conditions['to_sales_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_sales_date'])))->format('mysql_date');
                $stmt->where('t.sales_date', '<=', $date.' 23:59:59');
            }
        }
        $stmt->where('t.delete_flag', '=', '0');

        // // 日付（FROM）
        // if (trim($conditions['from_sales_date']) != '') {
        //     $stmt->where('t.sales_date', '>=', $conditions['from_sales_date']);
        // }
        // // 日付（TO）
        // if (trim($conditions['to_sales_date']) != '') {
        //     $stmt->where('t.sales_date', '<=', $conditions['to_sales_date']);
        // }

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('t.sales_correction_number', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.sales_correction_number', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    //=========================================================================//
    //=============================   売上データ  ==============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getSalesCorrection($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.sales_correction_number', 'sales_correction_number'),
                array('t.division_code', 'division_code'),
                array('t.sales_status', 'sales_status'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'sales_category_value'),
                array('t.client_code', 'client_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mcl.client_name),"'.$encrypt_key.'")'), 'client_name'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.car_code', 'car_code'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mca.carrier_name),"'.$encrypt_key.'")'), 'carrier_name'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array('t.sales_date', 'sales_date'),
                array('t.operation_count', 'operation_count'),
                array('t.delivery_category', 'delivery_category'),
                array('t.sales', 'sales'),
                array('t.carrier_cost', 'carrier_cost'),
                array('t.highway_fee', 'highway_fee'),
                array('t.highway_fee_claim', 'highway_fee_claim'),
                array('t.overtime_fee', 'overtime_fee'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks')
                );

        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('t.division_code', '=', 'md.division_code')
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');

        // 社員コード
        $stmt->where('t.sales_correction_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', '0');

        // 検索実行
        return $stmt->execute($db)->current();
    }

}