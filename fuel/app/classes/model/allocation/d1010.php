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

class D1010 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車データ（共配便）検索 & 配車レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(t.dispatch_number) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('t.dispatch_number', 'dispatch_number'),
                            array('t.division_code', 'division_code'),
                            array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = t.division_code)'), 'division_name'),
                            array('t.delivery_code', 'delivery_code'),
                            array('t.dispatch_code', 'dispatch_code'),
                            array('t.area_code', 'area_code'),
                            array('t.course', 'course'),
                            array('t.delivery_date', 'delivery_date'),
                            array('t.pickup_date', 'pickup_date'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'pickup_place'),
                            array('t.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                            array('t.carrier_code', 'carrier_code'),
                            array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                            array('t.product_name', 'product_name'),
                            array('t.maker_name', 'maker_name'),
                            array('t.volume', 'volume'),
                            array('t.unit_code', 'unit_code'),
                            array('t.car_model_code', 'car_model_code'),
                            array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                            array('t.car_code', 'car_code'),
                            array('t.member_code', 'member_code'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                            array('t.remarks', 'remarks'),
                            array('t.inquiry_no', 'inquiry_no'),
                            array('t.carrier_payment', 'carrier_payment'),
                            array('t.sales_status', 'sales_status')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));
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

        // 配車番号
        if (!empty($conditions['dispatch_number'])) {
            $stmt->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 課コード
        if (!empty($conditions['division']) && trim($conditions['division']) != '000') {
            $stmt->where('t.division_code', '=', $conditions['division']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (!empty($conditions['delivery_code']) && trim($conditions['delivery_code']) != '0') {
            $stmt->where('t.delivery_code', '=', $conditions['delivery_code']);
        }
        // 配車区分
        if (!empty($conditions['dispatch_code']) && trim($conditions['dispatch_code']) != '0') {
            $stmt->where('t.dispatch_code', '=', $conditions['dispatch_code']);
        }
        // 地区
        if (!empty($conditions['area_code']) && trim($conditions['area_code']) != '0') {
            $stmt->where('t.area_code', '=', $conditions['area_code']);
        }
        // コース
        if (!empty($conditions['course'])) {
            $stmt->where('t.course', '=', $conditions['course']);
        }
        // 納品日
        if (!empty($conditions['delivery_date_from']) && trim($conditions['delivery_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
            $stmt->where('t.delivery_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['delivery_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '>=', $date);
            }
            if (!empty($conditions['delivery_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '<=', $date);
            }
        }
        // 引取日
        if (!empty($conditions['pickup_date_from']) && trim($conditions['pickup_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['pickup_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['pickup_date_to'])))->format('mysql_date');
            $stmt->where('t.pickup_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['pickup_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['pickup_date_from'])))->format('mysql_date');
                $stmt->where('t.pickup_date', '>=', $date);
            }
            if (!empty($conditions['pickup_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['pickup_date_to'])))->format('mysql_date');
                $stmt->where('t.pickup_date', '<=', $date);
            }
        }
        // 納品先
        if (!empty($conditions['delivery_place']) && trim($conditions['delivery_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['delivery_place']."%'"));
        }
        // 引取先
        if (!empty($conditions['pickup_place']) && trim($conditions['pickup_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['pickup_place']."%'"));
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
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.pickup_date', 'DESC')->order_by('t.dispatch_number', 'DESC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.pickup_date', 'DESC')->order_by('t.dispatch_number', 'DESC')
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
     * 配車、分載データ削除
     */
    public static function deleteRecord($dispatch_number, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 売上ステータス取得
        $result = self::getBillStatus($dispatch_number, $db);
        if (!empty($result)) {
            // 売上ステータスチェック
            if ($result['sales_status'] == '2') {
                \Log::error(\Config::get('m_DW0041')."[".$result['bill_number']."]");
                return \Config::get('m_DW0041');
            }
        }

        // レコード削除
        $result = self::delDispatchShare($dispatch_number, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0012')."[dispatch_number:".$dispatch_number."]");
            return \Config::get('m_DE0012');
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
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 配車データ更新（売上ステータス）
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

            $dispatch_number = $record['dispatch_number'];
            $sales_status = $record['sales_status'];

            // レコード存在チェック
            if (!$result = self::getDispatchShare($dispatch_number, $db)) {
                return \Config::get('m_DW0001');
            }

            // レコード更新
            $result = self::updSalesStatus($dispatch_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0005')."[dispatch_number:".$dispatch_number."]");
                return \Config::get('m_DE0005');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0010', \Config::get('m_DI0010'), '配車更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($dispatch_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
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

}