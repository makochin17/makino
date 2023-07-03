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

class D0030 extends \Model {

    public static $db           = 'ONISHI';

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

        $tmp = array(
            'processing_division'           => '',
            'division_code'                 => '',
            'list'                          => array(),
        );

        $sub_tmp = array(
                    'sales_correction_number'   => '',
                    'member_code'               => '',
                    'sales_status'              => '1',
                    'sales_category_code'       => '01',
                    'sales_category_value'      => '',
                    'client_code'               => '',
                    'client_name'               => '',
                    'car_model_code'            => '001',
                    'car_model_name'            => '',
                    'car_code'                  => '',
                    'carrier_code'              => '',
                    'carrier_name'              => '',
                    'member_code'               => '',
                    'driver_name'               => '',
                    'sales_date'                => '',
                    'operation_count'           => '',
                    'delivery_category'         => '1',
                    'sales'                     => 0,
                    'carrier_cost'              => 0,
                    'highway_fee'               => 0,
                    'highway_fee_claim'         => '1',
                    'overtime_fee'              => 0,
                    'remarks'                   => ''
                );
        // 配車データ
        for ($i=0;$i < 5;$i++) {
            $list[] = $sub_tmp;
        }
        $tmp['list']        = $list;
        $res                = $tmp;
        return $res;
    }

    public static function setForms($conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms();
        }

        foreach ($conditions as $key => $cols) {
            if ($key == 'list') {
                foreach ($cols as $listcnt => $data) {
                    foreach ($data as $listkey => $listval) {
                        if (isset($input_data[$key][$listcnt][$listkey])) {
                            $conditions[$key][$listcnt][$listkey] = $input_data[$key][$listcnt][$listkey];
                        }
                    }
                }
            } elseif ($key == 'division_code') {
                if (isset($input_data[$key])) {
                    $conditions[$key]   = $input_data[$key];
                } else {
                    $userinfo           = AuthConfig::getAuthConfig('all');
                    $conditions[$key]   = $userinfo['division_code'];
                }
            } else {
                $conditions[$key] = $input_data[$key];
            }
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
                    array('client_name', 'client_name')
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
                    array('carrier_name', 'carrier_name')
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
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録
        $result = self::addSalesCorrection($conditions, $db);

        if (!$result) {
            Log::error(\Config::get('m_DE0007')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0007');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0012', AuthConfig::getAuthConfig('user_id').\Config::get('m_DI0012'), '', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 登録
     */
    public static function addSalesCorrection($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'division_code'         => $data['division_code'],
            'sales_status'          => (!empty($data['sales_status'])) ? $data['sales_status']:'1',
            'sales_category_code'   => $data['sales_category_code'],
            'sales_category_value'  => \DB::expr('HEX(AES_ENCRYPT("'.$data['sales_category_value'].'","'.$encrypt_key.'"))'),
            'client_code'           => (!empty($data['client_code'])) ? $data['client_code']:null,
            'car_model_code'        => (!empty($data['car_model_code'])) ? $data['car_model_code']:null,
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            'carrier_code'          => (!empty($data['carrier_code'])) ? $data['carrier_code']:null,
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => (!empty($data['driver_name'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'):null,
            'sales_date'            => date('Y-m-d', strtotime($data['sales_date'])),
            'operation_count'       => (int)$data['operation_count'],
            'delivery_category'     => $data['delivery_category'],
            'sales'                 => (int)$data['sales'],
            'carrier_cost'          => (int)$data['carrier_cost'],
            'highway_fee'           => (int)$data['highway_fee'],
            'highway_fee_claim'     => $data['highway_fee_claim'],
            'overtime_fee'          => (int)$data['overtime_fee'],
            'remarks'               => (!empty($data['remarks'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['remarks'].'","'.$encrypt_key.'"))'):null
        );
        $set = array_merge($set, self::getEtcData(true));
        // 登録実行
        $result   = \DB::insert('t_sales_correction')->set($set)->execute($db);
        if($result[1] > 0) {
            return true;
        }
        return false;

    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        // レコード存在チェック
        $result = self::getSalesCorrection($conditions['sales_correction_number'], $db);
        if (count($result) == 0) {
            return \Config::get('m_DW0011');
        }
        $sales_correction_number = $result['sales_correction_number'];

        //　レコード更新
        $result = self::updSalesCorrection($sales_correction_number, $conditions, $db);
        if (!$result) {
            Log::error(\Config::get('m_DE0008')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0008');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0013', AuthConfig::getAuthConfig('user_id').\Config::get('m_DI0013'), '', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 社員マスタ更新
     */
    public static function updSalesCorrection($id, $data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($id) || empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // テーブル
        $stmt = \DB::update('t_sales_correction');
        // 項目セット
        $set = array(
            'division_code'         => $data['division_code'],
            'sales_status'          => (!empty($data['sales_status'])) ? $data['sales_status']:'1',
            'sales_category_code'   => $data['sales_category_code'],
            'sales_category_value'  => \DB::expr('HEX(AES_ENCRYPT("'.$data['sales_category_value'].'","'.$encrypt_key.'"))'),
            'client_code'           => (!empty($data['client_code'])) ? $data['client_code']:null,
            'car_model_code'        => (!empty($data['car_model_code'])) ? $data['car_model_code']:null,
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            'carrier_code'          => (!empty($data['carrier_code'])) ? $data['carrier_code']:null,
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => (!empty($data['driver_name'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'):null,
            'sales_date'            => date('Y-m-d', strtotime($data['sales_date'])),
            'operation_count'       => (int)$data['operation_count'],
            'delivery_category'     => $data['delivery_category'],
            'sales'                 => (int)$data['sales'],
            'carrier_cost'          => (int)$data['carrier_cost'],
            'highway_fee'           => (int)$data['highway_fee'],
            'highway_fee_claim'     => $data['highway_fee_claim'],
            'overtime_fee'          => (int)$data['overtime_fee'],
            'remarks'               => (!empty($data['remarks'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['remarks'].'","'.$encrypt_key.'"))'):null
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));

        // 売上補正テーブルNo
        $stmt->where('sales_correction_number', '=', $id);
        // 削除フラグ
        $stmt->where('delete_flag', '=', '0');
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

        // レコード存在チェック
        $result = self::getSalesCorrection($conditions['sales_correction_number'], $db);
        if (count($result) == 0) {
            return \Config::get('m_DW0011');
        }
        $sales_correction_number = $result['sales_correction_number'];

        // レコード削除
        $result = self::delSalesCorrection($sales_correction_number, $db);
        if (!$result) {
            Log::error(\Config::get('m_DE0009')."[sales_correction_number:".$sales_correction_number."]");
            return \Config::get('m_DE0009');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0014', AuthConfig::getAuthConfig('user_id').\Config::get('m_DI0014'), '', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 社員マスタ削除（論理削除）
     */
    public static function delSalesCorrection($id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($id)) {
            return false;
        }

        // テーブル
        $stmt = \DB::update('t_sales_correction');
        // 項目セット
        $set = array(
            'delete_flag' => 1
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));

        // 売上補正テーブルNo
        $stmt->where('sales_correction_number', '=', $id);
        // 削除フラグ
        $stmt->where('delete_flag', '=', '0');
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }


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
                array('mcl.client_name', 'client_name'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.car_code', 'car_code'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
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