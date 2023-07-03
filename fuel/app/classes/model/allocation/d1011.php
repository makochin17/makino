<?php
namespace Model\Allocation;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

class D1011 extends \Model {

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
     * 指定項目NULLチェック
     */
    public static function chkDispatchShareDataNull($data) {

        if (empty($data['delivery_date']) && empty($data['pickup_date']) && empty($data['client_code']) && empty($data['carrier_code']) && empty($data['driver_name']) && empty($data['car_code']) && empty($data['product_name'])) {
            return false;
        }
        return true;
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
            case 'dispatch':
            case 'csv':
            default:
                $res = array(
                    'dispatch_number'       => '配車番号',
                    'division_code'         => '課コード',
                    'delivery_code'         => '配送区分',
                    'dispatch_code'         => '配車区分',
                    'area_code'             => '地区',
                    'course'                => 'コース',
                    'delivery_date'         => '納品日',
                    'pickup_date'           => '引取日',
                    'delivery_place'        => '納品先',
                    'pickup_place'          => '引取先',
                    'client_code'           => '得意先コード',
                    'carrier_code'          => '庸車先コード',
                    'product_name'          => '商品名',
                    'maker_name'            => 'メーカー名',
                    'volume'                => '数量',
                    'unit_code'             => '単位コード',
                    'car_model_code'        => '車種コード',
                    'car_code'              => '車両コード',
                    'member_code'           => '社員コード',
                    'driver_name'           => '運転手',
                    'remarks1'              => '備考1',
                    'remarks2'              => '備考2',
                    'remarks3'              => '備考3',
                    'requester'             => '依頼者',
                    'inquiry_no'            => '問い合わせNo',
                    'onsite_flag'           => '現場',
                    'delivery_address'      => '納品先住所',
                    'carrier_payment'       => '庸車費用',
                    'sales_status'          => '売上ステータス',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'dispatch') {

        $res = array();
        switch ($type) {
            case 'dispatch':
            default:
                $tmp = array(
                    'processing_division'   => '1',
                    'dispatch_number'       => '',
                    'division_code'         => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    'delivery_code'         => '',
                    'dispatch_code'         => '',
                    'area_code'             => '',
                    'course'                => '',
                    'delivery_date'         => '',
                    'pickup_date'           => '',
                    'delivery_place'        => '',
                    'pickup_place'          => '',
                    'client_code'           => '',
                    'client_name'           => '',
                    'carrier_code'          => '',
                    'carrier_name'          => '',
                    'product_name'          => '',
                    'maker_name'            => '',
                    'volume'                => '',
                    'unit_code'             => '',
                    'car_model_code'        => '',
                    'car_model_name'        => '',
                    'car_code'              => '',
                    'car_number'            => '',
                    'member_code'           => '',
                    'driver_name'           => '',
                    'remarks1'              => '',
                    'remarks2'              => '',
                    'remarks3'              => '',
                    'requester'             => '',
                    'inquiry_no'            => '',
                    'onsite_flag'           => '',
                    'delivery_address'      => '',
                    'carrier_payment'       => '',
                    'sales_status'          => '1',
                );
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

        // レコード登録(配車データ)
        $insert_id = self::addDispatchShare($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_DE0004')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0004');
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
     * 配車登録
     */
    public static function addDispatchShare($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'division_code'         => $data['division_code'],
            'delivery_code'         => $data['delivery_code'],
            'dispatch_code'         => $data['dispatch_code'],
            'area_code'             => $data['area_code'],
            'course'                => (!empty($data['course'])) ? $data['course']:null,
            'delivery_date'         => (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])):null,
            'pickup_date'           => (!empty($data['pickup_date'])) ? date('Y-m-d', strtotime($data['pickup_date'])):null,
            'delivery_place'        => (!empty($data['delivery_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_place'].'","'.$encrypt_key.'"))'):null,
            'pickup_place'          => (!empty($data['pickup_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['pickup_place'].'","'.$encrypt_key.'"))'):null,
            'client_code'           => $data['client_code'],
            'carrier_code'          => $data['carrier_code'],
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'volume'                => (!empty($data['volume'])) ? str_replace(',', '', $data['volume']):0.00,
            'unit_code'             => $data['unit_code'],
            'car_model_code'        => $data['car_model_code'],
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'remarks'               => (!empty($data['remarks1'])) ? $data['remarks1']:null,
            'remarks2'              => (!empty($data['remarks2'])) ? $data['remarks2']:null,
            'remarks3'              => (!empty($data['remarks3'])) ? $data['remarks3']:null,
            'requester'             => (!empty($data['requester'])) ? $data['requester']:null,
            'inquiry_no'            => (!empty($data['inquiry_no'])) ? $data['inquiry_no']:null,
            'onsite_flag'           => (!empty($data['onsite_flag'])) ? $data['onsite_flag']:0,
            'delivery_address'      => (!empty($data['delivery_address'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))'):null,
            'carrier_payment'       => (!empty($data['carrier_payment'])) ? str_replace(',', '', $data['carrier_payment']):0,
            'sales_status'          => (!empty($data['sales_status'])) ? $data['sales_status']:1,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_dispatch_share')->set($set)->execute($db);

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
        
        // 売上ステータス取得
        $result = self::getBillStatus($conditions['dispatch_number'], $db);
        if (!empty($result)) {
            $bill_number = $result['bill_number'];
            
            // 売上ステータスチェック
            if ($result['sales_status'] == '2') {
                \Log::error(\Config::get('m_DW0041')."[".$bill_number."]");
                return \Config::get('m_DW0041');
            }
        }
        
        // レコード更新
        // 配車情報
        $result = self::updDispatchShare($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0005')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0005');
        }
        
        // 請求情報
        if (!empty($bill_number)) {
            // 数量取得
            $result = self::getDispatchShareVolume($bill_number, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0024')."[".$bill_number."]");
                return \Config::get('m_DE0005');
            }
            
            $conditions['volume'] = $result['volume'];
            
            $result = self::updBillShare($bill_number, $conditions, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0025')."[".$bill_number."]");
                return \Config::get('m_DE0005');
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0022', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0022'), '配車更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 配車情報更新
     */
    public static function updDispatchShare($data, $db = null) {

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
            'delivery_code'         => $data['delivery_code'],
            'dispatch_code'         => $data['dispatch_code'],
            'area_code'             => $data['area_code'],
            'course'                => (!empty($data['course'])) ? $data['course']:null,
            'delivery_date'         => (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])):null,
            'pickup_date'           => (!empty($data['pickup_date'])) ? date('Y-m-d', strtotime($data['pickup_date'])):null,
            'delivery_place'        => (!empty($data['delivery_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_place'].'","'.$encrypt_key.'"))'):null,
            'pickup_place'          => (!empty($data['pickup_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['pickup_place'].'","'.$encrypt_key.'"))'):null,
            'client_code'           => $data['client_code'],
            'carrier_code'          => $data['carrier_code'],
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'volume'                => (!empty($data['volume'])) ? $data['volume']:0.00,
            'unit_code'             => $data['unit_code'],
            'car_model_code'        => $data['car_model_code'],
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'remarks'               => (!empty($data['remarks1'])) ? $data['remarks1']:null,
            'remarks2'              => (!empty($data['remarks2'])) ? $data['remarks2']:null,
            'remarks3'              => (!empty($data['remarks3'])) ? $data['remarks3']:null,
            'requester'             => (!empty($data['requester'])) ? $data['requester']:null,
            'inquiry_no'            => (!empty($data['inquiry_no'])) ? $data['inquiry_no']:null,
            'onsite_flag'           => (!empty($data['onsite_flag'])) ? $data['onsite_flag']:0,
            'delivery_address'      => (!empty($data['delivery_address'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))'):null,
            'carrier_payment'       => (!empty($data['carrier_payment'])) ? str_replace(',', '', $data['carrier_payment']):0,
            'sales_status'          => (!empty($data['sales_status'])) ? $data['sales_status']:1,
        );

        // テーブル
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

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
    
    /**
     * 配車データに紐づく請求データの売上ステータス取得
     */
    public static function getBillStatus($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 項目
        $stmt = \DB::select(
                array('t.bill_number', 'bill_number'),
                array('tb.sales_status', 'sales_status')
                );
        // テーブル
        $stmt->from(array('t_bill_share_link', 't'))
                ->join(array('t_bill_share', 'tb'), 'INNER')
                ->on('t.bill_number', '=', 'tb.bill_number');
        // 配車コード
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->current();
    }
    
    /**
     * 請求データに紐づく配車データの数量取得
     */
    public static function getDispatchShareVolume($bill_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 項目
        $stmt = \DB::select(
                array('t.bill_number', 'bill_number'),
                array(\DB::expr('SUM(td.volume)'), 'volume'),
                array('tb.sales_status', 'sales_status'),
                );

        // テーブル
        $stmt->from(array('t_bill_share_link', 't'))
                ->join(array('t_bill_share', 'tb'), 'INNER')
                ->on('t.bill_number', '=', 'tb.bill_number')
                ->join(array('t_dispatch_share', 'td'), 'INNER')
                ->on('t.dispatch_number', '=', 'td.dispatch_number');

        // 請求番号
        $stmt->where('t.bill_number', '=', $bill_number);
        
        // グループ化
        $stmt->group_by('t.bill_number');

        // 検索実行
        return $stmt->execute($db)->current();
    }
    
    /**
     * 請求データ更新
     */
    public static function updBillShare($bill_number, $data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($bill_number)) {
            return false;
        }
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'division_code'         => $data['division_code'],
            'delivery_code'         => $data['delivery_code'],
            'area_code'             => $data['area_code'],
            'client_code'           => $data['client_code'],
            'carrier_code'          => $data['carrier_code'],
            'car_model_code'        => $data['car_model_code'],
            'onsite_flag'           => (!empty($data['onsite_flag'])) ? $data['onsite_flag']:0,
            'car_code'              => (!empty($data['car_code'])) ? $data['car_code']:null,
            'delivery_address'      => (!empty($data['delivery_address'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))'):null,
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'requester'             => (!empty($data['requester'])) ? $data['requester']:null,
            'inquiry_no'            => (!empty($data['inquiry_no'])) ? $data['inquiry_no']:null,
            'remarks'               => (!empty($data['remarks1'])) ? $data['remarks1']:null,
            'remarks2'              => (!empty($data['remarks2'])) ? $data['remarks2']:null,
            'remarks3'              => (!empty($data['remarks3'])) ? $data['remarks3']:null,
            'volume'                => (!empty($data['volume'])) ? $data['volume']:0.00,
            'unit_code'             => $data['unit_code'],
            'product_name'          => $data['product_name']
        );

        //運行日と運行先の設定
        switch ($data['delivery_code']) {
            case '1':       // 納品日
                $set['destination_date']        = $data['delivery_date'];
                $set['destination']             = (!empty($data['delivery_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_place'].'","'.$encrypt_key.'"))'):null;
                break;
            case '2':       // 引取日
                $set['destination_date']        = $data['pickup_date'];
                $set['destination']             = (!empty($data['pickup_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['pickup_place'].'","'.$encrypt_key.'"))'):null;
                break;
            case '3':       // 納品日or引取日
            default:
                if (!empty($data['delivery_date'])) {
                    $set['destination_date']    = $data['delivery_date'];
                    $set['destination']         = (!empty($data['delivery_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_place'].'","'.$encrypt_key.'"))'):null;
                } elseif (!empty($data['pickup_date'])) {
                    $set['destination_date']    = $data['pickup_date'];
                    $set['destination']         = (!empty($data['pickup_place'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['pickup_place'].'","'.$encrypt_key.'"))'):null;
                }
        }

        // テーブル
        $stmt = \DB::update('t_bill_share')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('bill_number', '=', $bill_number);
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
        
        // 売上ステータス取得
        $result = self::getBillStatus($conditions['dispatch_number'], $db);
        if (!empty($result)) {
            // 売上ステータスチェック
            if ($result['sales_status'] == '2') {
                \Log::error(\Config::get('m_DW0041')."[".$result['bill_number']."]");
                return \Config::get('m_DW0041');
            }
        }

        // レコード削除
        $result = self::delDispatchShare($conditions['dispatch_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0006')."[dispatch_number:".$conditions['dispatch_number']."]");
            return \Config::get('m_DE0006');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0023', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0023'), '配車削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 配車データ削除
     */
    public static function delDispatchShare($dispatch_number, $db = null) {

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
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

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
    public static function getDispatchShare($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.division_name),"'.$encrypt_key.'")'), 'division'),
                array('t.delivery_code', 'delivery_code'),
                array('t.dispatch_code', 'dispatch_code'),
                array('t.area_code', 'area_code'),
                array('t.course', 'course'),
                array('t.delivery_date', 'delivery_date'),
                array('t.pickup_date', 'pickup_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'pickup_place'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.product_name', 'product_name'),
                array('t.maker_name', 'maker_name'),
                array('t.volume', 'volume'),
                array('t.unit_code', 'unit_code'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.car_code', 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('t.member_code', 'member_code'),
                // array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array('t.remarks', 'remarks1'),
                array('t.remarks2', 'remarks2'),
                array('t.remarks3', 'remarks3'),
                array('t.requester', 'requester'),
                array('t.inquiry_no', 'inquiry_no'),
                array('t.onsite_flag', 'onsite_flag'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_address),"'.$encrypt_key.'")'), 'delivery_address'),
                array('t.carrier_payment', 'carrier_payment'),
                array('t.sales_status', 'sales_status')
                );

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'))
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

}