<?php
namespace Model\Allocation;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Dispatch\D0040\D0040;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

class D0010 extends \Model {

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

    // ヘッダーデータ
    public static function getHeaders($type = 'csv') {

        $res = array();
        switch ($type) {
            case 'carrying':
                $res = array(
                    'carrying_number'       => '分載番号',
                    'dispatch_number'       => '配車番号',
                    'stack_date'            => '積日',
                    'drop_date'             => '降日',
                    'stack_place'           => '積地',
                    'drop_place'            => '降地',
                    'client_code'           => '得意先コード',
                    'car_model_code'        => '車種コード',
                    'carrier_code'          => '庸車先コード',
                    // 'car_code'              => '車両コード',
                    // 'car_number'            => '車番',
                    'car_code'              => '車番',
                    'member_code'           => '社員コード',
                    'driver_name'           => '運転手',
                    'phone_number'          => '電話番号',
                    'destination'           => '運行先',
                    'claim_sales'           => '請求売上',
                    'carrier_payment'       => '庸車支払',
                    'claim_highway_fee'     => '請求高速料金',
                    'claim_highway_claim'   => '請求高速料金請求有無',
                    'carrier_highway_fee'   => '庸車先高速料金',
                    'carrier_highway_claim' => '庸車先高速料金請求有無',
                    'driver_highway_fee'    => 'ドライバー高速料金',
                    'driver_highway_claim'  => 'ドライバー高速料金請求有無',
                );
                break;
            case 'dispatch':
            case 'csv':
            default:
                $res = array(
                    'dispatch_number'       => '配車番号',
                    'division_code'         => '課コード',
                    'sales_status'          => '売上ステータス',
                    'stack_date'            => '積日',
                    'drop_date'             => '降日',
                    'stack_place'           => '積地',
                    'drop_place'            => '降地',
                    'client_code'           => '得意先コード',
                    'product_code'          => '商品コード',
                    'car_model_code'        => '車種コード',
                    'carrier_code'          => '庸車先コード',
                    // 'car_code'              => '車両コード',
                    // 'car_number'            => '車番',
                    'car_code'              => '車番',
                    'member_code'           => '社員コード',
                    'driver_name'           => '運転手',
                    'phone_number'          => '電話番号',
                    'carrying_count'        => '分載台数',
                    'remarks'               => '備考',
                    'destination'           => '運行先',
                    'delivery_category'     => '配送区分',
                    'tax_category'          => '税区分',
                    'claim_sales'           => '請求売上',
                    'carrier_payment'       => '庸車支払',
                    'claim_highway_fee'     => '請求高速料金',
                    'claim_highway_claim'   => '請求高速料金請求有無',
                    'carrier_highway_fee'   => '庸車高速料金',
                    'carrier_highway_claim' => '庸車高速料金請求有無',
                    'driver_highway_fee'    => 'ドライバー高速料金',
                    'driver_highway_claim'  => 'ドライバー高速料金請求有無',
                    'allowance'             => '手当',
                    'overtime_fee'          => '時間外',
                    'stay'                  => '泊まり',
                    'linking_wrap'          => '連結・ラップ',
                    'round_trip'            => '往復',
                    'drop_appropriation'    => '降日計上',
                    'receipt_send_date'     => '受領書送付日',
                    'receipt_receive_date'  => '受領書受領日',
                    'in_house_remarks'      => '社内向け備考',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'dispatch') {

        $res = array();
        switch ($type) {
            case 'carrying':
                $carrying_tmp = array(
                    'carrying_number'       => '',
                    'dispatch_number'       => '',
                    'stack_date'            => date('Y-m-d'),
                    'drop_date'             => date('Y-m-d'),
                    'stack_place'           => '',
                    'drop_place'            => '',
                    'client_code'           => '',
                    'client_name'           => '',
                    'car_model_code'        => '',
                    'carrier_code'          => '',
                    'carrier_name'          => '',
                    'car_code'              => '',
                    'car_number'            => '',
                    'member_code'           => '',
                    'driver_name'           => '',
                    'phone_number'          => '',
                    'destination'           => '',
                    'claim_sales'           => '',
                    'carrier_payment'       => '',
                    'claim_highway_fee'     => '',
                    'claim_highway_claim'   => '',
                    'carrier_highway_fee'   => '',
                    'carrier_highway_claim' => '',
                    'driver_highway_fee'    => '',
                    'driver_highway_claim'  => '',
                );
                // 分載データ
                for ($i=0;$i < 3;$i++) {
                    $carrying[] = $carrying_tmp;
                }
                $res['carrying']    = $carrying;
                break;
            case 'dispatch':
            default:
                $tmp = array(
                    'processing_division'   => '',
                    'dispatch_number'       => '',
                    'division_code'         => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    'sales_status'          => '1',
                    'stack_date'            => date('Y-m-d'),
                    'drop_date'             => date('Y-m-d'),
                    'stack_place'           => '',
                    'drop_place'            => '',
                    'client_code'           => '',
                    'client_name'           => '',
                    'product_code'          => '',
                    'product_name'          => '',
                    'car_model_code'        => '',
                    'carrier_code'          => '',
                    'carrier_name'          => '',
                    'car_code'              => '',
                    'car_number'            => '',
                    'member_code'           => '',
                    'driver_name'           => '',
                    'phone_number'          => '',
                    'carrying_count'        => '',
                    'remarks'               => '',
                    'destination'           => '',
                    'delivery_category'     => '',
                    'tax_category'          => '',
                    'claim_sales'           => 0,
                    'carrier_payment'       => 0,
                    'claim_highway_fee'     => 0,
                    'claim_highway_claim'   => '1',
                    'carrier_highway_fee'   => 0,
                    'carrier_highway_claim' => '1',
                    'driver_highway_fee'    => 0,
                    'driver_highway_claim'  => '1',
                    'allowance'             => 0,
                    'overtime_fee'          => 0,
                    'stay'                  => 0,
                    'linking_wrap'          => 0,
                    'round_trip'            => '1',
                    'drop_appropriation'    => '1',
                    'receipt_send_date'     => '',
                    'receipt_receive_date'  => '',
                    'in_house_remarks'      => NULL,
                    'carrying'              => array(),
                );
                $carrying_tmp = array(
                    'carrying_number'       => '',
                    'dispatch_number'       => '',
                    'stack_date'            => date('Y-m-d'),
                    'drop_date'             => date('Y-m-d'),
                    'stack_place'           => '',
                    'drop_place'            => '',
                    'client_code'           => '',
                    'client_name'           => '',
                    'car_model_code'        => '',
                    'carrier_code'          => '',
                    'carrier_name'          => '',
                    'car_code'              => '',
                    'car_number'            => '',
                    'member_code'           => '',
                    'driver_name'           => '',
                    'phone_number'          => '',
                    'destination'           => '',
                    'claim_sales'           => '',
                    'carrier_payment'       => '',
                    'claim_highway_fee'     => '',
                    'claim_highway_claim'   => '',
                    'carrier_highway_fee'   => '',
                    'carrier_highway_claim' => '',
                    'driver_highway_fee'    => '',
                    'driver_highway_claim'  => '',
                );
                // 分載データ
                for ($i=0;$i < 3;$i++) {
                    $carrying[] = $carrying_tmp;
                }
                $sub_tmp['carrying']    = $carrying;

                // 配車データ
                for ($i=0;$i < 5;$i++) {
                    $list[] = $sub_tmp;
                }
                $tmp['list']        = $list;
                $res                = $tmp;
                break;
        }

        return $res;
    }

    public static function setForms($type = 'dispatch', $conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if ($key == 'list') {
                foreach ($cols as $listcnt => $data) {
                    foreach ($data as $listkey => $listval) {
                        if (isset($input_data[$key][$listcnt][$listkey])) {
                            if ($listkey == 'carrying') {
                                foreach ($listval as $carryingcnt => $carryingval) {
                                    foreach ($carryingval as $carryingkey => $val) {
                                        if (isset($input_data[$key][$listcnt][$listkey][$carryingcnt][$carryingkey])) {
                                            $conditions[$key][$listcnt][$listkey][$carryingcnt][$carryingkey] = $input_data[$key][$listcnt][$listkey][$carryingcnt][$carryingkey];
                                        }
                                    }
                                }
                            } else {
                                $conditions[$key][$listcnt][$listkey] = $input_data[$key][$listcnt][$listkey];
                            }
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

    // マスター情報取得
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
                    // array(\DB::expr('AES_DECRYPT(UNHEX(client_name),"'.$encrypt_key.'")'), 'client_name')
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
                    // array(\DB::expr('AES_DECRYPT(UNHEX(carrier_name),"'.$encrypt_key.'")'), 'carrier_name')
                )
                ->from('m_carrier')
                ->where('carrier_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
            case 'product':
                return \DB::select(
                    array('product_code', 'product_code'),
                    // array('product_name', 'product_name')
                    array(\DB::expr('AES_DECRYPT(UNHEX(product_name),"'.$encrypt_key.'")'), 'product_name')
                )
                ->from('m_product')
                ->where('product_code', $id)
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

    // マスター情報取得（全件）
    public static function getMasterList($type, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        switch ($type) {
            case 'client':
                return \DB::select(
                    array('mc.client_code', 'client_code'),
                    array('mcc.company_name', 'company_name'),
                    array('mcs.sales_office_name', 'sales_office_name'),
                    array('mcd.department_name', 'department_name'),
                    array('mc.client_name', 'client_name'),
                    array('mc.closing_date', 'closing_date'),
                    array('mc.closing_date_1', 'closing_date_1'),
                    array('mc.closing_date_2', 'closing_date_2'),
                    array('mc.closing_date_3', 'closing_date_3')
                )
                ->from(array('m_client', 'mc'))
                ->join(array('m_client_company', 'mcc'), 'left outer')
                    ->on('mc.client_company_code', '=', 'mcc.client_company_code')
                    ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
                ->join(array('m_client_sales_office', 'mcs'), 'left outer')
                    ->on('mc.client_sales_office_code', '=', 'mcs.client_sales_office_code')
                    ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
                ->join(array('m_client_department', 'mcd'), 'left outer')
                    ->on('mc.client_department_code', '=', 'mcd.client_department_code')
                    ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'')
                ->where('mc.start_date', '<=', date("Y-m-d"))
                ->where('mc.end_date', '>', date("Y-m-d"))
                ->execute($db)->as_array();
                break;
            case 'carrier':
                return \DB::select(
                    array('mc.carrier_code', 'carrier_code'),
                    array('mc.company_section', 'company_section'),
                    array('mcc.company_name', 'company_name'),
                    array('mcs.sales_office_name', 'sales_office_name'),
                    array('mcd.department_name', 'department_name'),
                    array('mc.carrier_name', 'carrier_name'),
                    array('mc.closing_date', 'closing_date'),
                    array('mc.closing_date_1', 'closing_date_1'),
                    array('mc.closing_date_2', 'closing_date_2'),
                    array('mc.closing_date_3', 'closing_date_3')
                )
                ->from(array('m_carrier', 'mc'))
                ->join(array('m_carrier_company', 'mcc'), 'left outer')
                    ->on('mc.carrier_company_code', '=', 'mcc.carrier_company_code')
                    ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
                ->join(array('m_carrier_sales_office', 'mcs'), 'left outer')
                    ->on('mc.carrier_sales_office_code', '=', 'mcs.carrier_sales_office_code')
                    ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
                ->join(array('m_carrier_department', 'mcd'), 'left outer')
                    ->on('mc.carrier_department_code', '=', 'mcd.carrier_department_code')
                    ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'')
                ->where('mc.start_date', '<=', date("Y-m-d"))
                ->where('mc.end_date', '>', date("Y-m-d"))
                ->execute($db)->as_array();
                break;
            case 'car':
                return \DB::select(
                    array('mcar.car_code', 'car_code'),
                    array('mcarmodel.car_model_name', 'car_model_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_name),"'.$encrypt_key.'")'), 'car_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_number),"'.$encrypt_key.'")'), 'car_number')
                )
                ->from(array('m_car', 'mcar'))
                ->join(array('m_car_model', 'mcarmodel'), 'left outer')
                    ->on('mcar.car_model_code', '=', 'mcarmodel.car_model_code')
                    ->on('mcarmodel.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mcarmodel.end_date', '>', '\''.date("Y-m-d").'\'')
                ->where('mcar.start_date', '<=', date("Y-m-d"))
                ->where('mcar.end_date', '>', date("Y-m-d"))
                ->execute($db)->as_array();
                break;
            case 'driver':
                return \DB::select(
                    array('mm.member_code', 'member_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'full_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                    array('md.division_name', 'division'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mp.position_name),"'.$encrypt_key.'")'), 'position'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.phone_number),"'.$encrypt_key.'")'), 'phone_number')
                )
                ->from(array('m_member', 'mm'))
                ->join(array('m_car', 'mc'), 'left outer')
                    ->on('mm.car_code', '=', 'mc.car_code')
                    ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
                    ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
                ->join(array('m_division', 'md'), 'left outer')
                    ->on('mm.division_code', '=', 'md.division_code')
                ->join(array('m_position', 'mp'), 'left outer')
                    ->on('mm.position_code', '=', 'mp.position_code')
                ->where('mm.start_date', '<=', date('Y-m-d'))
                ->where('mm.end_date', '>', date('Y-m-d'))
                ->execute($db)->as_array();
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

    // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
    public static function OurCompanyCheck($carrier_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($carrier_code)) {
            return false;
        }

        return \DB::select()
        ->from(array('m_carrier', 'm'))
        ->where('m.carrier_code', $carrier_code)
        ->where('m.company_section', 1)
        ->where('m.start_date', '<=', date('Y-m-d'))
        ->where('m.end_date', '>', date('Y-m-d'))
        ->execute($db)->current();

    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 社員マスタレコード取得
     */
    public static function getMember($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.member_code', 'member_code'),
                array('m.division_code', 'division_code'),
                array('m.position_code', 'position_code'),
                array('m.car_code', 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'full_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array('m.user_id', 'user_id'),
                array('m.user_authority', 'user_authority'),
                array('m.lock_status', 'lock_status'),
                array('m.start_date', 'start_date')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));

        // 社員コード
        $stmt->where('m.member_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        $carrying_flg = false;

        // レコード登録(配車データ)
        $insert_id = self::addDispatChcharter($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_DE0004')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0004');
        }

        // 分載データフォーム存在チェック
        if (!empty($conditions['carrying'])) {
            foreach ($conditions['carrying'] as $key => $val) {
                if ($key > 0 && !empty($insert_id) && !empty($val['car_code']) && !empty($val['driver_name'])) {
                    $carrying_flg = true;
                }
            }
        }
        if ($carrying_flg === true) {
            // レコード登録(分載データ)
            $result = self::addCarryingChcharter($insert_id, $conditions['carrying'], $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0004')."[".print_r($conditions,true)."]");
                return \Config::get('m_DE0004');
            }
        }

        // 操作ログ出力
//        $result = OpeLog::addOpeLog('DI0009', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0009'), '配車登録', $db);
//        if (!$result) {
//            \Log::error(\Config::get('m_CE0007'));
//            return \Config::get('m_CE0007');
//        }
        return null;
    }

    /**
     * 配車登録
     */
    public static function addDispatChcharter($data, $db = null) {

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
            'stack_date'            => date('Y-m-d', strtotime($data['stack_date'])),
            'drop_date'             => date('Y-m-d', strtotime($data['drop_date'])),
            'stack_place'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['stack_place'].'","'.$encrypt_key.'"))'),
            'drop_place'            => \DB::expr('HEX(AES_ENCRYPT("'.$data['drop_place'].'","'.$encrypt_key.'"))'),
            'client_code'           => $data['client_code'],
            'product_code'          => $data['product_code'],
            'car_model_code'        => $data['car_model_code'],
            'carrier_code'          => $data['carrier_code'],
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            // 'car_number'            => \DB::expr('HEX(AES_ENCRYPT("'.$data['car_number'].'","'.$encrypt_key.'"))'),
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'phone_number'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['phone_number'].'","'.$encrypt_key.'"))'),
            'carrying_count'        => (!empty($data['carrying_count'])) ? (int)$data['carrying_count']:0,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
            'destination'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'),
            'delivery_category'     => (!empty($data['delivery_category'])) ? $data['delivery_category']:'1',
            'tax_category'          => (!empty($data['tax_category'])) ? $data['tax_category']:'1',
            'claim_sales'           => (!empty($data['claim_sales'])) ? (int)$data['claim_sales']:0,
            'carrier_payment'       => (!empty($data['carrier_payment'])) ? (int)$data['carrier_payment']:0,
            'claim_highway_fee'     => (!empty($data['claim_highway_fee'])) ? (int)$data['claim_highway_fee']:0,
            'claim_highway_claim'   => (!empty($data['claim_highway_claim'])) ? $data['claim_highway_claim']:'1',
            'carrier_highway_fee'   => (!empty($data['carrier_highway_fee'])) ? (int)$data['carrier_highway_fee']:0,
            'carrier_highway_claim' => (!empty($data['carrier_highway_claim'])) ? $data['carrier_highway_claim']:'1',
            'driver_highway_fee'    => (!empty($data['driver_highway_fee'])) ? (int)$data['driver_highway_fee']:0,
            'driver_highway_claim'  => (!empty($data['driver_highway_claim'])) ? $data['driver_highway_claim']:'1',
            'allowance'             => (!empty($data['allowance'])) ? (int)$data['allowance']:0,
            'overtime_fee'          => (!empty($data['overtime_fee'])) ? (int)$data['overtime_fee']:0,
            'stay'                  => (!empty($data['stay'])) ? (int)$data['stay']:0,
            'linking_wrap'          => (!empty($data['linking_wrap'])) ? (int)$data['linking_wrap']:0,
            'round_trip'            => (!empty($data['round_trip'])) ? $data['round_trip']:'1',
            'drop_appropriation'    => (!empty($data['drop_appropriation'])) ? $data['drop_appropriation']:'1',
            'receipt_send_date'     => (!empty($data['receipt_send_date'])) ? date('Y-m-d', strtotime($data['receipt_send_date'])):null,
            'receipt_receive_date'  => (!empty($data['receipt_receive_date'])) ? date('Y-m-d', strtotime($data['receipt_receive_date'])):null,
            'in_house_remarks'      => (!empty($data['in_house_remarks'])) ? $data['in_house_remarks']:null
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_dispatch_charter')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    /**
     * 分載登録
     */
    public static function addCarryingChcharter($dispatch_number, $data, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if (!empty($dispatch_number) && !empty($val['car_code']) && !empty($val['driver_name'])) {
                    // 傭車先コード取得
                    if (empty($val['carrier_code'])) {
                        $val['carrier_code'] = D0010::getCarrierCode($val['member_code'], $val['driver_name'], D0010::$db);
                    }
                    if (empty($val['carrier_code'])) {
                        \Log::error(str_replace('XXXXX','庸車No',\Config::get('m_CW0005')));
                        return false;
                    }
                    // 傭車先が自社かどうか判定して自社なら車両コード存在チェック
//                    if (D0010::OurCompanyCheck($val['carrier_code'], D0010::$db)) {
//                        // 車両コードが車両マスタに登録されているかチェック
//                        if (!D0010::getNameById('car', $val['car_code'], D0010::$db)) {
//                            \Log::error(\Config::get('m_DW0021'));
//                            return false;
//                        }
//                    }

                    $set = array(
                        'dispatch_number'       => $dispatch_number,
                        'stack_date'            => date('Y-m-d', strtotime($val['stack_date'])),
                        'drop_date'             => date('Y-m-d', strtotime($val['drop_date'])),
                        'stack_place'           => \DB::expr('HEX(AES_ENCRYPT("'.$val['stack_place'].'","'.$encrypt_key.'"))'),
                        'drop_place'            => \DB::expr('HEX(AES_ENCRYPT("'.$val['drop_place'].'","'.$encrypt_key.'"))'),
                        'client_code'           => $val['client_code'],
                        'car_model_code'        => $val['car_model_code'],
                        'carrier_code'          => $val['carrier_code'],
                        'car_code'              => (!empty($val['car_code'])) ? $val['car_code']:null,
                        // 'car_number'            => \DB::expr('HEX(AES_ENCRYPT("'.$val['car_number'].'","'.$encrypt_key.'"))'),
                        'member_code'           => (!empty($val['member_code'])) ? $val['member_code']:null,
                        'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$val['driver_name'].'","'.$encrypt_key.'"))'),
                        'phone_number'          => \DB::expr('HEX(AES_ENCRYPT("'.$val['phone_number'].'","'.$encrypt_key.'"))'),
                        'destination'           => \DB::expr('HEX(AES_ENCRYPT("'.$val['destination'].'","'.$encrypt_key.'"))'),
                        'claim_sales'           => (!empty($val['claim_sales'])) ? (int)$val['claim_sales']:0,
                        'carrier_payment'       => (!empty($val['carrier_payment'])) ? $val['carrier_payment']:0,
                        'claim_highway_fee'     => (!empty($val['claim_highway_fee'])) ? $val['claim_highway_fee']:0,
                        'claim_highway_claim'   => (!empty($val['claim_highway_claim'])) ? $val['claim_highway_claim']:'1',
                        'carrier_highway_fee'    => (!empty($val['carrier_highway_fee'])) ? $val['carrier_highway_fee']:0,
                        'carrier_highway_claim'  => (!empty($val['carrier_highway_claim'])) ? $val['carrier_highway_claim']:'1',
                        'driver_highway_fee'    => (!empty($val['driver_highway_fee'])) ? $val['driver_highway_fee']:0,
                        'driver_highway_claim'  => (!empty($val['driver_highway_claim'])) ? $val['driver_highway_claim']:'1',
                    );
                    $set = array_merge($set, self::getEtcData(true));
                    $sql = \DB::insert('t_carrying_charter')->set($set);
                    // 登録実行
                    list($insert_id, $rows_affected) = $sql->execute($db);
                    if(!$insert_id) {
                        return false;
                    }
                }
            }
        }
        return true;

    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        $carrying_flg = false;

        // レコード更新
        // 配車情報
        $result = self::updDispatChcharter($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0005')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0005');
        }

        // 分載データ削除
        if ($ary_data = self::getCarryingCharter(null, $conditions['dispatch_number'], $db)) {
            $result = self::delCarryingChcharter($conditions['dispatch_number'], $conditions, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0005')."[".print_r($conditions,true)."]");
                return \Config::get('m_DE0005');
            }
        }

        // 分載データフォーム存在チェック
        if (!empty($conditions['carrying'])) {
            foreach ($conditions['carrying'] as $key => $val) {
                if ($key > 0 && !empty($conditions['dispatch_number']) && !empty($val['car_code']) && !empty($val['driver_name'])) {
                    $carrying_flg = true;
                }
            }
        }
        if ($carrying_flg === true) {
            // 分載データ登録
            $result = self::addCarryingChcharter($conditions['dispatch_number'], $conditions['carrying'], $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0005')."[".print_r($conditions,true)."]");
                return \Config::get('m_DE0005');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0010', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0010'), '配車更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 配車情報更新
     */
    public static function updDispatChcharter($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'division_code'         => $data['division_code'],
            'sales_status'          => (!empty($data['sales_status'])) ? $data['sales_status']:'1',
            'stack_date'            => date('Y-m-d', strtotime($data['stack_date'])),
            'drop_date'             => date('Y-m-d', strtotime($data['drop_date'])),
            'stack_place'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['stack_place'].'","'.$encrypt_key.'"))'),
            'drop_place'            => \DB::expr('HEX(AES_ENCRYPT("'.$data['drop_place'].'","'.$encrypt_key.'"))'),
            'client_code'           => $data['client_code'],
            'product_code'          => $data['product_code'],
            'car_model_code'        => $data['car_model_code'],
            'carrier_code'          => $data['carrier_code'],
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            // 'car_number'            => \DB::expr('HEX(AES_ENCRYPT("'.$data['car_number'].'","'.$encrypt_key.'"))'),
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'phone_number'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['phone_number'].'","'.$encrypt_key.'"))'),
            'carrying_count'        => (!empty($data['carrying_count'])) ? (int)$data['carrying_count']:0,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
            'destination'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'),
            'delivery_category'     => $data['delivery_category'],
            'tax_category'          => $data['tax_category'],
            'claim_sales'           => (!empty($data['claim_sales'])) ? (int)$data['claim_sales']:0,
            'carrier_payment'       => (!empty($data['carrier_payment'])) ? (int)$data['carrier_payment']:0,
            'claim_highway_fee'     => (!empty($data['claim_highway_fee'])) ? (int)$data['claim_highway_fee']:0,
            'claim_highway_claim'   => (!empty($data['claim_highway_claim'])) ? $data['claim_highway_claim']:'1',
            'carrier_highway_fee'   => (!empty($data['carrier_highway_fee'])) ? (int)$data['carrier_highway_fee']:0,
            'carrier_highway_claim' => (!empty($data['carrier_highway_claim'])) ? $data['carrier_highway_claim']:'1',
            'driver_highway_fee'    => (!empty($data['driver_highway_fee'])) ? (int)$data['driver_highway_fee']:0,
            'driver_highway_claim'  => (!empty($data['driver_highway_claim'])) ? $data['driver_highway_claim']:'1',
            'allowance'             => (!empty($data['allowance'])) ? (int)$data['allowance']:0,
            'overtime_fee'          => (!empty($data['overtime_fee'])) ? (int)$data['overtime_fee']:0,
            'stay'                  => (!empty($data['stay'])) ? (int)$data['stay']:0,
            'linking_wrap'          => (!empty($data['linking_wrap'])) ? (int)$data['linking_wrap']:0,
            'round_trip'            => (!empty($data['round_trip'])) ? $data['round_trip']:'1',
            'drop_appropriation'    => (!empty($data['drop_appropriation'])) ? $data['drop_appropriation']:'1',
            'receipt_send_date'     => (!empty($data['receipt_send_date'])) ? date('Y-m-d', strtotime($data['receipt_send_date'])):null,
            'receipt_receive_date'  => (!empty($data['receipt_receive_date'])) ? date('Y-m-d', strtotime($data['receipt_receive_date'])):null,
            'in_house_remarks'      => (!empty($data['in_house_remarks'])) ? $data['in_house_remarks']:null
        );

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $data['dispatch_number']);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
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
        $result = self::delDispatChcharter($conditions['dispatch_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0006')."[dispatch_number:".$conditions['dispatch_number']."]");
            return \Config::get('m_DE0006');
        }

        if ($ary_data = self::getCarryingCharter(null, $conditions['dispatch_number'], $db)) {
            // 分載データ削除
            $result = self::delCarryingChcharterUpd($conditions['dispatch_number'], $conditions, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0006')."[".print_r($conditions,true)."]");
                return \Config::get('m_DE0006');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0011', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0011'), '配車削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 配車データ削除
     */
    public static function delDispatChcharter($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 分載削除（削除ボタン用）
     */
    public static function delCarryingChcharterUpd($dispatch_number, $data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_carrying_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;

    }

    /**
     * 分載削除（更新ボタン用）
     */
    public static function delCarryingChcharter($dispatch_number, $data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // テーブル
        $stmt = \DB::delete('t_carrying_charter');

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;

    }

    //=========================================================================//
    //=============================   配車データ  ==============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getDispatchCharter($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.division_name),"'.$encrypt_key.'")'), 'division'),
                array('t.sales_status', 'sales_status'),
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.product_code', 'product_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mp.product_name),"'.$encrypt_key.'")'), 'product_name'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.car_code', 'car_code'),
                // array(\DB::expr('CASE WHEN t.car_code IS NULL THEN AES_DECRYPT(UNHEX(t.car_number),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'") END'), 'car_number'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array('t.carrying_count', 'carrying_count'),
                array('t.remarks', 'remarks'),
                array('t.delivery_category', 'delivery_category'),
                array('t.tax_category', 'tax_category'),
                array('t.claim_sales', 'claim_sales'),
                array('t.carrier_payment', 'carrier_payment'),
                array('t.claim_highway_fee', 'claim_highway_fee'),
                array('t.claim_highway_claim', 'claim_highway_claim'),
                array('t.carrier_highway_fee', 'carrier_highway_fee'),
                array('t.carrier_highway_claim', 'carrier_highway_claim'),
                array('t.driver_highway_fee', 'driver_highway_fee'),
                array('t.driver_highway_claim', 'driver_highway_claim'),
                array('t.allowance', 'allowance'),
                array('t.overtime_fee', 'overtime_fee'),
                array('t.stay', 'stay'),
                array('t.linking_wrap', 'linking_wrap'),
                array('t.round_trip', 'round_trip'),
                array('t.drop_appropriation', 'drop_appropriation'),
                array('t.receipt_send_date', 'receipt_send_date'),
                array('t.receipt_receive_date', 'receipt_receive_date'),
                array('t.in_house_remarks', 'in_house_remarks')
                );

        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
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
            ->join(array('m_product', 'mp'), 'left outer')
                ->on('t.product_code', '=', 'mp.product_code')
                ->on('mp.start_date', '<=', 't.update_datetime')
                ->on('mp.end_date', '>', 't.update_datetime')
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
            ->join(array('m_car', 'mc'), 'left outer')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');

        // 配車コード
        $stmt->where('t.dispatch_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
    }
    /**
     * 配車データ（チャーター便）検索件数取得
     */
    public static function getSearchCountDispatchCharter($conditions, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return D0040::getSearchCount($conditions, $db);
    }

    /**
     * 配車データ（チャーター便）検索
     */
    public static function getSearchListDispatchCharter($conditions, $offset, $limit, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return D0040::getSearch($conditions, $offset, $limit, $db);

    }

    //=========================================================================//
    //=============================   分載データ  ==============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getCarryingCharter($code = null, $dispatch_number = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.carrying_number', 'carrying_number'),
                array('t.dispatch_number', 'dispatch_number'),
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array('t.client_code', 'client_code'),
                array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                array('t.car_model_code', 'car_model_code'),
                array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                array('t.car_code', 'car_code'),
                // array(\DB::expr('
                //     CASE
                //         WHEN t.car_code IS NULL THEN AES_DECRYPT(UNHEX(t.car_number),"'.$encrypt_key.'")
                //         ELSE (SELECT AES_DECRYPT(UNHEX(car_number),"'.$encrypt_key.'") FROM m_car WHERE car_code = t.car_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)
                //     END'
                // ), 'car_number'),
                array('t.member_code', 'member_code'),
                array(\DB::expr(
                    'CASE
                    WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")
                    ELSE (SELECT AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'") FROM m_member WHERE member_code = t.member_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)
                    END'
                ), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array('t.claim_sales', 'claim_sales'),
                array('t.carrier_payment', 'carrier_payment'),
                array('t.claim_highway_fee', 'claim_highway_fee'),
                array('t.claim_highway_claim', 'claim_highway_claim'),
                array('t.carrier_highway_fee', 'carrier_highway_fee'),
                array('t.carrier_highway_claim', 'carrier_highway_claim'),
                array('t.driver_highway_fee', 'driver_highway_fee'),
                array('t.driver_highway_claim', 'driver_highway_claim')
                );

        // テーブル
        $stmt->from(array('t_carrying_charter', 't'));

        // 分載No
        if (!empty($code)) {
            $stmt->where('t.carrying_number', '=', $code);
        }
        // 配車No
        if (!empty($dispatch_number)) {
            $stmt->where('t.dispatch_number', '=', $dispatch_number);
        }
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

}