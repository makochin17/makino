<?php
namespace Model\Search;
use \Model\Common\SystemConfig;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;
use \Model\Dispatch\D0040\D0040;

class S1010 extends \Model {

    public static $db       = 'MAKINO';

    // フォームデータ
    public static function getForms() {

        return array(
            // 配車番号
            'dispatch_number'           => '',
            // 課
            'division_code'             => '',
            // 売上状態
            'sales_status'              => '',
            // 配送区分
            'delivery_code'             => '',
            // 配車区分
            'dispatch_code'             => '',
            // 地区
            'area_code'                 => '',
            // コース
            'course'                    => '',
            // 納品日
            'from_delivery_date'        => '',
            'to_delivery_date'          => '',
            // 引取日
            'from_pickup_date'          => '',
            'to_pickup_date'            => '',
            // 納品先
            'delivery_place'            => '',
            // 引取先
            'pickup_place'              => '',
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
            // 配車番号
            'dispatch_number'           => array('name' => '配車番号', 'max_lengths' => '10'),
            // コース
            'course'                    => array('name' => 'コース', 'max_lengths' => '5'),
            // 納品先
            'delivery_place'            => array('name' => '納品先', 'max_lengths' => '30'),
            // 引取先
            'pickup_place'              => array('name' => '引取先', 'max_lengths' => '30'),
            // 得意先
            'client_code'               => array('name' => '得意先', 'max_lengths' => '5'),
            // 傭車先
            'carrier_code'              => array('name' => '傭車先', 'max_lengths' => '5'),
            // 商品名
            'product_name'              => array('name' => '商品名', 'max_lengths' => '30'),
            // 車両番号
            'car_code'                  => array('name' => '車両番号', 'max_lengths' => '4'),
            // 運転手
            'driver_name'               => array('name' => '運転手', 'max_lengths' => '6'),
            // 納品日
            'from_delivery_date'        => array('name' => '納品日From', 'max_lengths' => ''),
            'to_delivery_date'          => array('name' => '納品日To', 'max_lengths' => ''),
            // 引取日
            'from_pickup_date'          => array('name' => '引取日From', 'max_lengths' => ''),
            'to_pickup_date'            => array('name' => '引取日To', 'max_lengths' => ''),
        );
    }
    
    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車レコード検索 & 配車レコード検索件数取得
     */
    public static function getDispatchShare($type = 'search', $conditions, $offset, $limit, $db = null) {

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
        if (!empty($conditions['from_delivery_date']) && trim($conditions['to_delivery_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_delivery_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_delivery_date'])))->format('mysql_date');
            $stmt->where('t.delivery_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_delivery_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_delivery_date'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '>=', $date);
            }
            if (!empty($conditions['to_delivery_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_delivery_date'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '<=', $date);
            }
        }
        // 引取日
        if (!empty($conditions['from_pickup_date']) && trim($conditions['to_pickup_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_pickup_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_pickup_date'])))->format('mysql_date');
            $stmt->where('t.pickup_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_pickup_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_pickup_date'])))->format('mysql_date');
                $stmt->where('t.pickup_date', '>=', $date);
            }
            if (!empty($conditions['to_pickup_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_pickup_date'])))->format('mysql_date');
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
     * 配車データ（チャーター便）検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return self::getDispatchShare('count', $conditions, null, null, $db);
    }

    /**
     * 配車データ（チャーター便）検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return self::getDispatchShare('search', $conditions, $offset, $limit, $db);
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
    
}