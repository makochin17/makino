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

class D0040 extends \Model {

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

    // エクスポートファイル用ヘッダー
    public static function getHeader() {

        return array(
            'dispatch_number'               => '配車番号',
            'division_code'                 => '課コード',
            'division_name'                 => '課名',
            'sales_status'                  => '売上ステータス',
            'stack_date'                    => '積日',
            'drop_date'                     => '降日',
            'stack_place'                   => '積地',
            'drop_place'                    => '降地',
            'client_code'                   => '得意先コード',
            'client_name'                   => '得意先名',
            'product_code'                  => '商品コード',
            'product_name'                  => '商品名',
            'car_model_code'                => '車種コード',
            'car_model_name'                => '車種名',
            'carrier_code'                  => '庸車先コード',
            'carrier_name'                  => '庸車先名',
            // 'car_code'                      => '車両コード',
            // 'car_number'                    => '車番',
            'car_code'                      => '車番',
            'member_code'                   => '社員コード',
            'driver_name'                   => '運転手',
            'phone_number'                  => '電話番号',
            'carrying_flg'                  => '分載有無',
            'remarks'                       => '備考',
            'delivery_category'             => '配送区分',
            'tax_category'                  => '税区分',
            'claim_sales'                   => '請求売上',
            'carrier_payment'               => '庸車支払',
            'claim_highway_fee'             => '請求高速料金',
            'claim_highway_claim'           => '請求高速料金請求有無',
            'carrier_highway_fee'           => '庸車高速料金',
            'carrier_highway_claim'         => '庸車高速料金請求有無',
            'driver_highway_fee'            => 'ドライバー高速料金',
            'driver_highway_claim'          => 'ドライバー高速料金請求有無',
            'allowance'                     => '手当',
            'overtime_fee'                  => '時間外',
            'stay'                          => '泊まり',
            'linking_wrap'                  => '連結・ラップ',
            'round_trip'                    => '往復',
            'drop_appropriation'            => '降日計上',
            'receipt_send_date'             => '受領書送付日',
            'receipt_receive_date'          => '受領書受領日',
            'in_house_remarks'              => '社内向け備考',
            'carrying_number'               => '分載番号',
            'carrying_dispatch_number'      => '配車番号',
            'carrying_stack_date'           => '積日',
            'carrying_drop_date'            => '降日',
            'carrying_stack_place'          => '積地',
            'carrying_drop_place'           => '降地',
            'carrying_client_code'          => '得意先コード',
            'carrying_client_name'          => '得意先名',
            'carrying_car_model_code'       => '車種コード',
            'carrying_car_model_name'       => '車種名',
            'carrying_carrier_code'         => '庸車先コード',
            'carrying_carrier_name'         => '庸車先名',
            // 'carrying_car_code'             => '車両コード',
            // 'carrying_car_number'           => '車番',
            'carrying_car_code'             => '車番',
            'carrying_member_code'          => '社員コード',
            'carrying_driver_name'          => '運転手',
            'carrying_phone_number'         => '電話番号',
            'carrying_destination'          => '運行先',
            'carrying_claim_sales'          => '請求売上',
            'carrying_carrier_payment'      => '庸車支払',
            'carrying_claim_highway_fee'    => '請求高速料金',
            'carrying_claim_highway_claim'  => '請求高速料金請求有無',
            'carrying_carrier_highway_fee'  => '庸車高速料金',
            'carrying_carrier_highway_claim'=> '庸車高速料金請求有無',
            'carrying_driver_highway_fee'   => 'ドライバー高速料金',
            'carrying_driver_highway_claim' => 'ドライバー高速料金請求有無'
        );

    }

    // フォームデータ
    public static function getForms() {

        return array(
            // 配車番号
            'dispatch_number'           => '',
            // 課
            'division_code'             => '',
            // 積日
            'from_stack_date'           => '',
            'to_stack_date'             => '',
            // 降日
            'from_drop_date'            => '',
            'to_drop_date'              => '',
            // 売上確定
            'sales_status'              => '',
            // 得意先
            'client_code'               => '',
            'client_name'               => '',
            // 傭車先
            'carrier_code'              => '',
            'carrier_name'              => '',
            // 商品
            'product_code'              => '',
            'product_name'              => '',
            // 車種
            'car_model_code'            => '',
            // 車両
            // 'car_number'                => '',
            'car_code'                  => '',
            // 運転手
            'member_code'               => '',
            'driver_name'               => '',
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
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車レコード検索 & 配車レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $db = null) {

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
                            array('t.sales_status', 'sales_status'),
                            array('t.stack_date', 'stack_date'),
                            array('t.drop_date', 'drop_date'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                            array('t.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                            array('t.product_code', 'product_code'),
                            array(\DB::expr('(SELECT AES_DECRYPT(UNHEX(product_name),"'.$encrypt_key.'") FROM m_product WHERE product_code = t.product_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'product_name'),
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
                break;
        }

        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'));
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
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先
        if (!empty($conditions['carrier_code'])) {
            $stmt->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 商品
        if (!empty($conditions['product_code']) && trim($conditions['product_code']) != '0000') {
            $stmt->where('t.product_code', '=', $conditions['product_code']);
        }
        // 車番
        if (!empty($conditions['car_number']) && trim($conditions['car_number']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.car_number),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_number']."%'"));
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配車番号
        if (!empty($conditions['dispatch_number'])) {
            $stmt->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->where('t.car_model_code', '=', $conditions['car_model_code']);
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        // 配送区分
        if (!empty($conditions['delivery_category']) && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 積日
        if (!empty($conditions['from_stack_date']) && trim($conditions['to_stack_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_stack_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_stack_date'])))->format('mysql_date');
            $stmt->where('t.stack_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_stack_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_stack_date'])))->format('mysql_date');
                $stmt->where('t.stack_date', '>=', $date);
            }
            if (!empty($conditions['to_stack_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_stack_date'])))->format('mysql_date');
                $stmt->where('t.stack_date', '<=', $date);
            }
        }
        // 降日
        if (!empty($conditions['from_drop_date']) && trim($conditions['to_drop_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_drop_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_drop_date'])))->format('mysql_date');
            $stmt->where('t.drop_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_drop_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_drop_date'])))->format('mysql_date');
                $stmt->where('t.drop_date', '>=', $date);
            }
            if (!empty($conditions['to_drop_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_drop_date'])))->format('mysql_date');
                $stmt->where('t.drop_date', '<=', $date);
            }
        }
        $stmt->where('t.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('t.dispatch_number', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.dispatch_number', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
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
                array('md.division_name', 'division'),
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

        // 社員コード
        $stmt->where('t.dispatch_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', '0');

        // 検索実行
        return $stmt->execute($db)->current();
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
                array('t.claim_sales', 'claim_sales'),
                array('t.carrier_payment', 'carrier_payment'),
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
                array('t.claim_highway_claim', 'claim_highway_claim'),
                array('t.carrier_highway_claim', 'carrier_highway_claim'),
                array('t.driver_highway_claim', 'driver_highway_claim'),
                array('t.claim_highway_fee', 'claim_highway_fee'),
                array('t.carrier_highway_fee', 'carrier_highway_fee'),
                array('t.driver_highway_fee', 'driver_highway_fee')
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
        $stmt->where('t.delete_flag', '=', '0');

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

}