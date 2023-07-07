<?php
namespace Model\Dispatch\D0040;
use \Model\Common\SystemConfig;

class D0040 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 配車データ（チャーター便）取得
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
                array(\DB::expr('AES_DECRYPT(UNHEX(mcl.client_name),"'.$encrypt_key.'")'), 'client_name'),
                array('t.product_code', 'product_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mp.product_name),"'.$encrypt_key.'")'), 'product_name'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mca.carrier_name),"'.$encrypt_key.'")'), 'carrier_name'),
                array('t.car_code', 'car_code'),
                array('t.car_code', 'car_number'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array('t.carrying_count', 'carrying_count'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks'),
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

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 配車データ（チャーター便）検索件数取得
     * $mode　1:通常検索　2：本日分検索
     */
    public static function getSearchCount($conditions, $db, $mode = 1) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // サブクエリ---------------------------------------------------
        // 項目
        $sub_query = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.client_code', 'client_code'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('DATE_FORMAT(t.update_datetime,\'%Y-%m-%d\')'), 'update_date')
                );

        // テーブル
        $sub_query->from(array('t_dispatch_charter', 't'))
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $sub_query->where('t.delete_flag', '=', '0');
        // 配車番号
        if (trim($conditions['dispatch_number']) != '') {
            $sub_query->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $sub_query->where('t.division_code', '=', $conditions['division']);
        }
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $sub_query->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $sub_query->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 売上ステータス
        if (trim($conditions['sales_status']) != '' && trim($conditions['sales_status']) != '0') {
            $sub_query->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $sub_query->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 商品コード
        if (trim($conditions['product']) != '' && trim($conditions['product']) != '0000') {
            $sub_query->where('t.product_code', '=', $conditions['product']);
        }
        // 車種コード
        if (trim($conditions['car_model']) != '' && trim($conditions['car_model']) != '000') {
            $sub_query->where('t.car_model_code', '=', $conditions['car_model']);
        }
        // 積日（FROM）
        if (trim($conditions['stack_date_from']) != '') {
            $sub_query->where('t.stack_date', '>=', $conditions['stack_date_from']);
        }
        // 積日（TO）
        if (trim($conditions['stack_date_to']) != '') {
            $sub_query->where('t.stack_date', '<=', $conditions['stack_date_to']);
        }
        // 降日（FROM）
        if (trim($conditions['drop_date_from']) != '') {
            $sub_query->where('t.drop_date', '>=', $conditions['drop_date_from']);
        }
        // 降日（TO）
        if (trim($conditions['drop_date_to']) != '') {
            $sub_query->where('t.drop_date', '<=', $conditions['drop_date_to']);
        }
        // 車番
        if (trim($conditions['car_number']) != '') {
            $sub_query->where('t.car_code', '=', $conditions['car_number']);
        }
        // 登録者
        if (trim($conditions['create_user']) != '') {
            $sub_query->where('t.create_user', '=', $conditions['create_user']);
        }
        // 作成日時
        if ($mode == 2) {
            $sub_query->where('t.create_datetime', 'between', array(date("Y/m/d").' 00:00:00', date("Y/m/d").' 23:59:59'));
        }
        
        // メインクエリ---------------------------------------------------
        // 項目
        $stmt = \DB::select(\DB::expr('COUNT(t.dispatch_number) AS count'));
        
        // テーブル
        $stmt->from(array(\DB::expr("(".$sub_query.")"), 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_date')
                ->on('mcl.end_date', '>', 't.update_date')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_date')
                ->on('mca.end_date', '>', 't.update_date');
        
        // 得意先名
        if (trim($conditions['client_name']) != '') {
            $stmt->where('mcl.client_name', 'LIKE', \DB::expr("'%".$conditions['client_name']."%'"));
        }
        // 庸車先名
        if (trim($conditions['carrier_name']) != '') {
            $stmt->where('mca.carrier_name', 'LIKE', \DB::expr("'%".$conditions['carrier_name']."%'"));
        }
        // 運転手
        if (trim($conditions['driver_name']) != '') {
            $stmt->where('t.driver_name', 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        
        // 検索実行
        $res = $stmt->execute($db)->as_array();
        return $res[0]['count'];
    }

    /**
     * 配車データ（チャーター便）検索
     * $mode　1:通常検索　2：本日分検索
     */
    public static function getSearch($conditions, $offset, $limit, $db, $mode = 1) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // サブクエリ---------------------------------------------------
        // 項目
        $sub_query = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array('t.sales_status', 'sales_status'),
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array('t.stack_place', 'stack_place'),
                array('t.drop_place', 'drop_place'),
                array('t.client_code', 'client_code'),
                array('t.product_code', 'product_code'),
                array('t.car_model_code', 'car_model_code'),
                array('t.carrier_code', 'carrier_code'),
                array('t.car_code', 'car_code'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array('t.destination', 'destination'),
                array('t.phone_number', 'phone_number'),
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
                array('t.stay', 'stay'),
                array('t.linking_wrap', 'linking_wrap'),
                array('t.round_trip', 'round_trip'),
                array('t.drop_appropriation', 'drop_appropriation'),
                array('t.receipt_send_date', 'receipt_send_date'),
                array('t.receipt_receive_date', 'receipt_receive_date'),
                array('t.in_house_remarks', 'in_house_remarks'),
                array(\DB::expr('DATE_FORMAT(t.update_datetime,\'%Y-%m-%d\')'), 'update_date')
                );

        // テーブル
        $sub_query->from(array('t_dispatch_charter', 't'))
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $sub_query->where('t.delete_flag', '=', '0');
        // 配車番号
        if (trim($conditions['dispatch_number']) != '') {
            $sub_query->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $sub_query->where('t.division_code', '=', $conditions['division']);
        }
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $sub_query->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $sub_query->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 売上ステータス
        if (trim($conditions['sales_status']) != '' && trim($conditions['sales_status']) != '0') {
            $sub_query->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $sub_query->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 商品コード
        if (trim($conditions['product']) != '' && trim($conditions['product']) != '0000') {
            $sub_query->where('t.product_code', '=', $conditions['product']);
        }
        // 車種コード
        if (trim($conditions['car_model']) != '' && trim($conditions['car_model']) != '000') {
            $sub_query->where('t.car_model_code', '=', $conditions['car_model']);
        }
        // 積日（FROM）
        if (trim($conditions['stack_date_from']) != '') {
            $sub_query->where('t.stack_date', '>=', $conditions['stack_date_from']);
        }
        // 積日（TO）
        if (trim($conditions['stack_date_to']) != '') {
            $sub_query->where('t.stack_date', '<=', $conditions['stack_date_to']);
        }
        // 降日（FROM）
        if (trim($conditions['drop_date_from']) != '') {
            $sub_query->where('t.drop_date', '>=', $conditions['drop_date_from']);
        }
        // 降日（TO）
        if (trim($conditions['drop_date_to']) != '') {
            $sub_query->where('t.drop_date', '<=', $conditions['drop_date_to']);
        }
        // 車番
        if (trim($conditions['car_number']) != '') {
            $sub_query->where('t.car_code', '=', $conditions['car_number']);
        }
        // 登録者
        if (trim($conditions['create_user']) != '') {
            $sub_query->where('t.create_user', '=', $conditions['create_user']);
        }
        // 作成日時
        if ($mode == 2) {
            $sub_query->where('t.create_datetime', 'between', array(date("Y/m/d").' 00:00:00', date("Y/m/d").' 23:59:59'));
        }
        
        // メインクエリ---------------------------------------------------
        // 項目
        $stmt = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division'),
                array('md.division_name', 'division'),
                array('t.sales_status', 'sales_status'),
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mp.product_name),"'.$encrypt_key.'")'), 'product'),
                array('mcm.car_model_name', 'car_model'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.car_code', 'car_number'),
                array('t.driver_name', 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array('t.carrying_count', 'carrying_count'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks'),
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
                array('t.stay', 'stay'),
                array('t.linking_wrap', 'linking_wrap'),
                array('t.round_trip', 'round_trip'),
                array('t.drop_appropriation', 'drop_appropriation'),
                array('t.receipt_send_date', 'receipt_send_date'),
                array('t.receipt_receive_date', 'receipt_receive_date'),
                array('t.in_house_remarks', 'in_house_remarks')
                );
        
        // テーブル
        $stmt->from(array(\DB::expr("(".$sub_query.")"), 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_date')
                ->on('mcl.end_date', '>', 't.update_date')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_date')
                ->on('mca.end_date', '>', 't.update_date')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('t.division_code', '=', 'md.division_code')
            ->join(array('m_product', 'mp'), 'left outer')
                ->on('t.product_code', '=', 'mp.product_code')
                ->on('mp.start_date', '<=', 't.update_date')
                ->on('mp.end_date', '>', 't.update_date')
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_date')
                ->on('mcm.end_date', '>', 't.update_date')
            ->join(array('m_car', 'mc'), 'left outer')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_date')
                ->on('mc.end_date', '>', 't.update_date');
            
        
        // 得意先名
        if (trim($conditions['client_name']) != '') {
            $stmt->where('mcl.client_name', 'LIKE', \DB::expr("'%".$conditions['client_name']."%'"));
        }
        // 庸車先名
        if (trim($conditions['carrier_name']) != '') {
            $stmt->where('mca.carrier_name', 'LIKE', \DB::expr("'%".$conditions['carrier_name']."%'"));
        }
        // 運転手
        if (trim($conditions['driver_name']) != '') {
            $stmt->where('t.driver_name', 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        
        // 検索実行
        return $stmt->order_by('t.stack_date', 'DESC')->order_by('t.dispatch_number', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
    }
}