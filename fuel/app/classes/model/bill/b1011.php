<?php
namespace Model\Bill;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;

class B1011 extends \Model {

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
     * 配車情報が存在している場合のみのレコード重複チェック
     */
    public static function checkBillShare($bill_number = null, $dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        $ret = \DB::select()
        ->from('t_bill_share')
        ->where('delete_flag', 0);
        if (!empty($bill_number)) {
            $ret = $ret->where('bill_number', '!=', $bill_number);
        }
        $res = $ret->where('dispatch_number', $dispatch_number)
        ->execute($db)
        ->as_array()
        ;

        return $res;
    }

    /**
     * 指定項目NULLチェック
     */
    public static function chkBillShareDataNull($data) {

        // if (empty($data['destination_date']) && empty($data['client_code']) && empty($data['carrier_code']) && is_null($data['price']) && is_null($data['volume']) && empty($data['product_name']) && empty($data['car_code']) && empty($data['driver_name'])) {
        if (empty($data['destination_date']) && empty($data['client_code']) && empty($data['carrier_code']) && empty($data['product_name']) && empty($data['car_code']) && empty($data['driver_name'])) {
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
                    'division_code'         => '課コード',
                    'client_code'           => '得意先コード',
                    'storage_location'      => '保管場所',
                    'product_name'          => '商品名',
                    'maker_name'            => 'メーカー名',
                    'total_volume'          => '数量',
                    'unit_code'             => '単位コード',
                    'remarks1'              => '備考1',
                    'remarks2'              => '備考2',
                    'remarks3'              => '備考3',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'bill_share') {

        $res = array();
        switch ($type) {
            case 'bill_share':
            default:
                $tmp = array(
                    'processing_division'   => '1',
                    'bill_number'           => '',
                    'division_code'         => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    // 請求番号
                    'bill_number'           => '',
                    // 課
                    'division_code'         => '',
                    // 売上状態
                    'sales_status'          => '',
                    // 配送区分
                    'delivery_code'         => '',
                    // 地区
                    'area_code'             => '',
                    // 運行日
                    'destination_date'      => '',
                    // 運行先
                    'destination'           => '',
                    // 得意先
                    'client_code'           => '',
                    'client_name'           => '',
                    // 傭車先
                    'carrier_code'          => '',
                    'carrier_name'          => '',
                    // 現場
                    'onsite_flag'           => '',

                    // 共配便配車番号(配車情報)
                    'dispatch_number'       => '',
                    // 車種(配車情報)
                    'car_model_code'        => '',
                    // 車両番号(配車情報)
                    'car_code'              => '',
                    // 社員コード(配車情報)
                    'member_code'           => '',
                    // 運転手(配車情報)
                    'driver_name'           => '',
                    // 依頼者(配車情報)
                    'requester'             => '',
                    // 問い合わせNo(配車情報)
                    'inquiry_no'            => '',
                    // 納品先住所(配車情報)
                    'delivery_address'      => '',
                    // 備考1(配車情報)
                    'remarks1'              => '',
                    // 備考2(配車情報)
                    'remarks2'              => '',
                    // 備考3(配車情報)
                    'remarks3'              => '',

                    // 金額(請求情報)
                    'price'                 => '',
                    // 単価(請求情報)
                    'unit_price'            => '',
                    // 数量(請求情報)
                    'volume'                => '',
                    // 単位(請求情報)
                    'unit_code'             => '',
                    // 商品名(請求情報)
                    'product_name'          => '',
                    // 端数処理コード(請求情報)
                    'rounding_code'         => '',
                );
                // 在庫データ
                for ($i=0;$i < 5;$i++) {
                    $list[] = $sub_tmp;
                }
                $tmp['list']        = $list;
                $res                = $tmp;
                break;
        }

        return $res;
    }

    public static function setForms($type = 'bill_share', $conditions, $input_data) {

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

    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 請求番号
            'bill_number'           => array('name' => '請求番号', 'max_lengths' => '10'),
            // 配車番号
            'dispatch_number'       => array('name' => '配車番号', 'max_lengths' => '10'),
            // 運行日
            'destination_date'      => array('name' => '運行日', 'max_lengths' => ''),
            // 運行先
            'destination'           => array('name' => '運行先', 'max_lengths' => '30'),
            // 得意先
            'client_code'           => array('name' => '得意先', 'max_lengths' => '5'),
            // 傭車先
            'carrier_code'          => array('name' => '傭車先', 'max_lengths' => '5'),
            // 金額
            'price'                 => array('name' => '金額', 'max_lengths' => '10'),
            // 単価
            'unit_price'            => array('name' => '単価', 'max_lengths' => '10'),
            // 数量
            'volume'                => array('name' => '数量', 'max_lengths' => '10'),
            // 商品名
            'product_name'          => array('name' => '商品名', 'max_lengths' => '30'),
            // 車両番号
            'car_code'              => array('name' => '車両番号', 'max_lengths' => '4'),
            // 運転手
            'driver_name'           => array('name' => '運転手', 'max_lengths' => '6'),
            // 依頼者
            'requester'             => array('name' => '依頼者', 'max_lengths' => '15'),
            // 問い合わせNo
            'inquiry_no'            => array('name' => '問い合わせNo', 'max_lengths' => '15'),
            // 納品先住所
            'delivery_address'      => array('name' => '納品先住所', 'max_lengths' => '40'),
            // 備考1
            'remarks1'              => array('name' => '備考1', 'max_lengths' => '15'),
            // 備考2
            'remarks2'              => array('name' => '備考2', 'max_lengths' => '15'),
            // 備考3
            'remarks3'              => array('name' => '備考3', 'max_lengths' => '15'),
        );
    }

    /**
     * 配車データの取得（存在チェック用）
     */
    public static function getDispatchShareSalesStatus($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.delivery_date', 'delivery_date'),
                array('t.pickup_date', 'pickup_date')
                );

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', 0);
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 売上ステータスデータ更新
     */
    public static function updDispatchShareSalesStatus($dispatch_number, $sales_status = 1, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
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
     * レコード取得
     */
    public static function getBillShare($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.bill_number', 'bill_number'),
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = t.division_code)'), 'division_name'),
                array('t.delivery_code', 'delivery_code'),
                array('t.area_code', 'area_code'),
                array('t.destination_date', 'destination_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array('t.client_code', 'client_code'),
                array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                array('t.product_name', 'product_name'),
                array('t.price', 'price'),
                array('t.unit_price', 'unit_price'),
                array('t.volume', 'volume'),
                array('t.unit_code', 'unit_code'),
                array('t.rounding_code', 'rounding_code'),
                array('t.car_model_code', 'car_model_code'),
                array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                array('t.car_code', 'car_code'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array('t.onsite_flag', 'onsite_flag'),
                array('t.requester', 'requester'),
                array('t.inquiry_no', 'inquiry_no'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_address),"'.$encrypt_key.'")'), 'delivery_address'),
                array('t.remarks', 'remarks1'),
                array('t.remarks2', 'remarks2'),
                array('t.remarks3', 'remarks3'),
                array('t.sales_status', 'sales_status')
                );

        // テーブル
        $stmt->from(array('t_bill_share', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime')
        ;

        // 在庫番号
        $stmt->where('t.bill_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
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
        $insert_id = self::addBillShare($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_BE0001')."[".print_r($conditions,true)."]");
            return \Config::get('m_BE0001');
        }

        if (!empty($conditions['dispatch_number'])) {
            //請求データ紐付け（共配便）登録
            self::addBillShareLink($insert_id, $conditions['dispatch_number'], $db);
            if (!$insert_id) {
                \Log::error(\Config::get('m_BE0001')."[".$insert_id."]");
                return \Config::get('m_BE0001');
            }
            
            // 配車売上ステータス更新
            $result = self::updDispatchShareSalesStatus($conditions['dispatch_number'], 2, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0011')."[dispatch_number:".$conditions['dispatch_number']."]");
                return \Config::get('m_DE0011');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('BI0005', \Config::get('m_BI0005'), '請求情報登録', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 請求データ登録
     */
    public static function addBillShare($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'dispatch_number'       => (!empty($data['dispatch_number'])) ? $data['dispatch_number']:null,
            'division_code'         => $data['division_code'],
            'sales_status'          => (empty($data['sales_status'])) ? 1:$data['sales_status'],
            'delivery_code'         => $data['delivery_code'],
            'area_code'             => $data['area_code'],
            'destination_date'      => $data['destination_date'],
            'destination'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'),
            'client_code'           => $data['client_code'],
            'carrier_code'          => $data['carrier_code'],
            'product_name'          => $data['product_name'],
            'price'                 => str_replace(',', '', $data['price']),
            'unit_price'            => (!empty($data['unit_price'])) ? str_replace(',', '', $data['unit_price']):null,
            'volume'                => (!empty($data['volume'])) ? str_replace(',', '', $data['volume']):null,
            'unit_code'             => $data['unit_code'],
            'rounding_code'         => $data['rounding_code'],
            'car_model_code'        => (!empty($data['car_model_code'])) ? $data['car_model_code']:null,
            'car_code'              => $data['car_code'],
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'onsite_flag'           => (!empty($data['onsite_flag'])) ? $data['onsite_flag']:0,
            'requester'             => (!empty($data['requester'])) ? $data['requester']:null,
            'inquiry_no'            => (!empty($data['inquiry_no'])) ? $data['inquiry_no']:null,
            'delivery_address'      => (!empty($data['delivery_address'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))'):null,
            'remarks'               => (!empty($data['remarks1'])) ? $data['remarks1']:null,
            'remarks2'              => (!empty($data['remarks2'])) ? $data['remarks2']:null,
            'remarks3'              => (!empty($data['remarks3'])) ? $data['remarks3']:null,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_bill_share')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }
    
    /**
     * 請求データ紐付け（共配便）登録
     */
    public static function addBillShareLink($bill_number, $dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($bill_number) || empty($dispatch_number)) {
            return false;
        }
        
        $set = array(
            'bill_number'       => $bill_number,
            'dispatch_number'   => $dispatch_number
                );
        
        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_bill_share_link')->set($set)->execute($db);

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

        // レコード更新
        $result = self::updBillShare($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_BE0002')."[".print_r($conditions,true)."]");
            return \Config::get('m_BE0002');
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('BI0006', \Config::get('m_BI0006'), '請求情報更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 請求データ更新
     */
    public static function updBillShare($data, $db = null) {

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
            'sales_status'          => (empty($data['sales_status'])) ? 1:$data['sales_status'],
            'delivery_code'         => $data['delivery_code'],
            'area_code'             => $data['area_code'],
            'destination_date'      => $data['destination_date'],
            'destination'           => (!empty($data['destination'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'):null,
            'client_code'           => $data['client_code'],
            'carrier_code'          => $data['carrier_code'],
            'product_name'          => $data['product_name'],
            'price'                 => str_replace(',', '', $data['price']),
            'unit_price'            => (!empty($data['unit_price'])) ? str_replace(',', '', $data['unit_price']):null,
            'volume'                => str_replace(',', '', $data['volume']),
            'unit_code'             => $data['unit_code'],
            'rounding_code'         => $data['rounding_code'],
            'car_model_code'        => $data['car_model_code'],
            'car_code'              => $data['car_code'],
            'member_code'           => (!empty($data['member_code'])) ? $data['member_code']:null,
            'driver_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))'),
            'onsite_flag'           => (!empty($data['onsite_flag'])) ? $data['onsite_flag']:0,
            'requester'             => (!empty($data['requester'])) ? $data['requester']:null,
            'inquiry_no'            => (!empty($data['inquiry_no'])) ? $data['inquiry_no']:null,
            'delivery_address'      => (!empty($data['delivery_address'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))'):null,
            'remarks'               => (!empty($data['remarks1'])) ? $data['remarks1']:null,
            'remarks2'              => (!empty($data['remarks2'])) ? $data['remarks2']:null,
            'remarks3'              => (!empty($data['remarks3'])) ? $data['remarks3']:null,
        );

        // テーブル
        $stmt = \DB::update('t_bill_share')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('bill_number', '=', $data['bill_number']);
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
        $result = self::delBillShare($conditions['bill_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_BE0003')."[bill_number:".$conditions['bill_number']."]");
            return \Config::get('m_BE0003');
        }
        
        $dispatch_numbers = self::getDispatchNumber($conditions['bill_number'], $db);
        if (!empty($dispatch_numbers)) {
            // 請求データ紐付け（共配便）の削除
            $result = self::delBillShareLink($conditions['bill_number'], $db);
            if (!$result) {
                \Log::error(\Config::get('m_BE0003')."[bill_number:".$conditions['bill_number']."]");
                return \Config::get('m_BE0003');
            }

            foreach($dispatch_numbers as $dispatch_number) {
                // レコード存在チェック
                if ($result = self::getDispatchShareSalesStatus($dispatch_number, $db)) {
                    // 配車レコード売上ステータス更新（配車共配便）
                    $result = self::updDispatchShareSalesStatus($dispatch_number, 1, $db);
                    if (!$result) {
                        \Log::error(\Config::get('m_DE0011')."[dispatch_number:".$dispatch_number."]");
                        return \Config::get('m_DE0011');
                    }
                }
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('BI0007', \Config::get('m_BI0007'), '請求情報削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 請求データ削除
     */
    public static function delBillShare($bill_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($bill_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_bill_share')->set(array_merge($set, self::getEtcData(false)));

        // 請求番号
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
    
    /**
     * 請求データ紐付け（共配便）削除
     */
    public static function delBillShareLink($bill_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($bill_number)) {
            return false;
        }

        // テーブル
        $stmt = \DB::delete('t_bill_share_link');
        // 請求番号
        $stmt->where('bill_number', '=', $bill_number);
        
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

    /**
     * 請求に紐づく配車データの配車番号取得
     */
    public static function getDispatchNumber($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));

        // テーブル
        $stmt->from(array('t_bill_share_link', 't'));

        // 配車コード
        $stmt->where('t.bill_number', '=', $code);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }
}