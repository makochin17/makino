<?php
namespace Model\Bill;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

class B1010 extends \Model {

    public static $db       = 'MAKINO';

    // フォームデータ
    public static function getForms() {

        return array(
            // 請求番号
            'bill_number'               => '',
            // 配車番号
            'dispatch_number'           => '',
            // 課
            'division_code'             => '',
            // 売上状態
            'sales_status'              => '',
            // 配送区分
            'delivery_code'             => '',
            // 地区
            'area_code'                 => '',
            // 運行日
            'destination_date_from'     => '',
            'destination_date_to'       => '',
            // 運行先
            'destination'               => '',
            // 得意先
            'client_code'               => '',
            // 傭車先
            'carrier_code'              => '',
            // 商品名
            'product_name'              => '',
            // 車種
            'car_model_code'            => '',
            // 車両番号
            'car_code'                  => '',
            // 運転手
            'driver_name'               => '',
            // 登録者
            'create_user'               => '',
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

    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 請求番号
            'bill_number'               => array('name' => '請求番号', 'max_lengths' => '10'),
            // 運行先
            'destination'               => array('name' => '運行先', 'max_lengths' => '15'),
            // 得意先
            'client_code'               => array('name' => '得意先', 'max_lengths' => '5'),
            // 傭車先
            'carrier_code'              => array('name' => '傭車先', 'max_lengths' => '5'),
            // 商品名
            'product_name'              => array('name' => '商品名', 'max_lengths' => '15'),
            // 車両番号
            'car_code'                  => array('name' => '車両番号', 'max_lengths' => '4'),
            // 運転手
            'driver_name'               => array('name' => '運転手', 'max_lengths' => '6'),
            // 運行日
            'destination_date_from'     => array('name' => '運行日From', 'max_lengths' => ''),
            'destination_date_to'       => array('name' => '運行日To', 'max_lengths' => ''),
        );
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 請求データ（共配便）検索 & 請求レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(array('t.bill_number', 'bill_number'));
                break;
            case 'search':
            default:
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
                            array('t.remarks', 'remarks'),
                            array('t.sales_status', 'sales_status')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_bill_share', 't'));
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');
        }
        // 傭車先
        if (!empty($conditions['carrier_name'])) {
            $stmt->join(array('m_carrier', 'mca'), 'INNER')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime');
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
        // 車番
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->join(array('m_car', 'mc'), 'INNER')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
                ->on('mc.car_code', '=', \DB::expr("'".$conditions['car_code']."'"));
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->join(array('m_member', 'mm'), 'INNER')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime')
                ->on(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }

        // 請求番号
        if (!empty($conditions['bill_number'])) {
            $stmt->where(\DB::expr('CAST(t.bill_number AS SIGNED)'), '=', $conditions['bill_number']);
        }
        // 配車番号
        if (!empty($conditions['dispatch_number'])) {
            $stmt->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (!empty($conditions['delivery_code']) && trim($conditions['delivery_code']) != '0') {
            $stmt->where('t.delivery_code', '=', $conditions['delivery_code']);
        }
        // 地区
        if (!empty($conditions['area_code']) && trim($conditions['area_code']) != '0') {
            $stmt->where('t.area_code', '=', $conditions['area_code']);
        }
        // 運行日付
        if (!empty($conditions['destination_date_from']) && trim($conditions['destination_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['destination_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['destination_date_to'])))->format('mysql_date');
            $stmt->where('t.destination_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['destination_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['destination_date_from'])))->format('mysql_date');
                $stmt->where('t.destination_date', '>=', $date);
            }
            if (!empty($conditions['destination_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['destination_date_to'])))->format('mysql_date');
                $stmt->where('t.destination_date', '<=', $date);
            }
        }
        // 運行先
        if (!empty($conditions['destination'])) {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['destination']."%'"));
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先
        if (!empty($conditions['carrier_code'])) {
            $stmt->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 商品
        if (!empty($conditions['product_name'])) {
            $stmt->where('t.product_name', 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->where('t.car_model_code', '=', $conditions['car_model_code']);
        }
        // 車両コード
        if (!empty($conditions['car_code'])) {
            $stmt->where('t.car_code', '=', $conditions['car_code']);
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        // 登録者
        if (!empty($conditions['create_user'])) {
            $stmt->where('t.create_user', '=', $conditions['create_user']);
        }
        // 作成日時
        if ($mode == 2) {
            $stmt->where('t.create_datetime', 'between', array(date("Y/m/d").' 00:00:00', date("Y/m/d").' 23:59:59'));
        }

        $stmt->where('t.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $tmp = $stmt->compile($db);
                $cnt = \DB::select(array(\DB::expr('COUNT(bill_number)'), 'count'))
                ->from(array($stmt, 'a'));
                $cnt = $cnt->execute($db)->current();
                return $cnt['count'];
                break;
            case 'export':
                return $stmt->order_by('t.destination_date', 'DESC')->order_by('t.client_code', 'DESC')->order_by('t.bill_number', 'DESC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.destination_date', 'DESC')->order_by('t.client_code', 'DESC')->order_by('t.bill_number', 'DESC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
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

    /**
     * 請求データの取得（存在チェック用）
     */
    public static function getBillShare($bill_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.bill_number', 'bill_number'));

        // テーブル
        $stmt->from(array('t_bill_share', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 請求No
        $stmt->where('t.bill_number', '=', $bill_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 請求データ削除
     */
    public static function deleteRecord($bill_number, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除（請求）
        $result = self::delBillShare($bill_number, $db);
        if (!$result) {
            \Log::error(\Config::get('m_BE0003')."[bill_number:".$bill_number."]");
            return \Config::get('m_BE0003');
        }
        
        $dispatch_numbers = self::getDispatchNumber($bill_number, $db);
        
        if (!empty($dispatch_numbers)) {
            // 請求データ紐付け（共配便）の削除
            $result = self::delBillShareLink($bill_number, $db);
            if (!$result) {
                \Log::error(\Config::get('m_BE0003')."[bill_number:".$bill_number."]");
                return \Config::get('m_BE0003');
            }

            foreach($dispatch_numbers as $dispatch_number) {
                // レコード存在チェック
                if ($result = self::getDispatchShare($dispatch_number, $db)) {
                    // 配車レコード売上ステータス更新（配車共配便）
                    $result = self::updDispatchShareSalesStatus($dispatch_number, 1, $db);
                    if (!$result) {
                        \Log::error(\Config::get('m_DE0011')."[dispatch_number:".$dispatch_number."]");
                        return \Config::get('m_DE0011');
                    }
                }
            }
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
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_bill_share')->set(array_merge($set, self::getEtcData(false)));

        // 請求コード
        $stmt->where('bill_number', '=', $bill_number);
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

    /**
     * 請求データ更新（売上ステータス）
     */
    public static function updateRecord($upd_list, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($upd_list)) {
            return \Config::get('m_CW0010');
        }

        //売上ステータス更新ループ
        foreach ($upd_list as $record) {

            $bill_number    = $record['bill_number'];
            $sales_status   = $record['sales_status'];

            // レコード存在チェック
            if (!$result = self::getBillShare($bill_number, $db)) {
                return \Config::get('m_BW0002');
            }

            // レコード更新
            $result = self::updSalesStatus($bill_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_BE0002')."[bill_number:".$bill_number."]");
                return \Config::get('m_BE0002');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('BI0006', \Config::get('m_BI0006'), '請求更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($bill_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($bill_number)) {
            return false;
        }

        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_bill_share')->set(array_merge($set, self::getEtcData(false)));

        // 請求コード
        $stmt->where('bill_number', '=', $bill_number);
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

    /**
     * 配車データの取得（存在チェック用）
     */
    public static function getDispatchShare($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 配車データの売上ステータス更新（共配便配車データ）
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

        // 請求コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 請求に紐づく配車データの配車番号取得
     */
    public static function getDispatchNumber($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

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