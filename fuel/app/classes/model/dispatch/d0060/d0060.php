<?php
namespace Model\Dispatch\D0060;
use \Model\Common\SystemConfig;

class D0060 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 売上補正レコード検索件数取得
     * $mode　1:通常検索　2：本日分検索
     */
    public static function getSearchCount($conditions, $db, $mode = 1) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // サブクエリ---------------------------------------------------
        // 項目
        $sub_query = \DB::select(
                array('t.sales_correction_number', 'sales_correction_number'),
                array('t.client_code', 'client_code'),
                array('t.carrier_code', 'carrier_code'),
                array(\DB::expr('DATE_FORMAT(t.update_datetime,\'%Y-%m-%d\')'), 'update_date')
                );

        // テーブル
        $sub_query->from(array('t_sales_correction', 't'));
        
        //削除フラグ
        $sub_query->where('t.delete_flag', '=', '0');
        // 売上補正番号
        if (trim($conditions['sales_correction_number']) != '') {
            $sub_query->where(\DB::expr('CAST(t.sales_correction_number AS SIGNED)'), '=', $conditions['sales_correction_number']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $sub_query->where('t.division_code', '=', $conditions['division']);
        }
        // 売上ステータス
        if (trim($conditions['sales_status']) != '' && trim($conditions['sales_status']) != '0') {
            $sub_query->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $sub_query->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $sub_query->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $sub_query->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 売上区分
        if (trim($conditions['sales_category']) != '' && trim($conditions['sales_category']) != '00') {
            $sub_query->where('t.sales_category_code', '=', $conditions['sales_category']);
        }
        // 車種コード
        if (trim($conditions['car_model']) != '' && trim($conditions['car_model']) != '000') {
            $sub_query->where('t.car_model_code', '=', $conditions['car_model']);
        }
        // 車両コード
        if (trim($conditions['car_code']) != '' && trim($conditions['car_code']) != '0') {
            $sub_query->where('t.car_code', '=', $conditions['car_code']);
        }
        // 日付（FROM）
        if (trim($conditions['sales_date_from']) != '') {
            $sub_query->where('t.sales_date', '>=', $conditions['sales_date_from']);
        }
        // 日付（TO）
        if (trim($conditions['sales_date_to']) != '') {
            $sub_query->where('t.sales_date', '<=', $conditions['sales_date_to']);
        }
        // 運転手
        if (trim($conditions['driver_name']) != '') {
            $sub_query->where(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
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
        $stmt = \DB::select(\DB::expr('COUNT(t.sales_correction_number) AS count'));
        
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
        
        // 検索実行
        $res = $stmt->execute($db)->as_array();
        return $res[0]['count'];
    }

    /**
     * 売上補正レコード検索
     * $mode　1:通常検索　2：本日分検索
     */
    public static function getSearch($conditions, $offset, $limit, $db, $mode = 1) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // サブクエリ---------------------------------------------------
        // 項目
        $sub_query = \DB::select(
                array('t.sales_correction_number', 'sales_correction_number'),
                array('t.division_code', 'division_code'),
                array('t.sales_status', 'sales_status'),
                array('t.sales_date', 'sales_date'),
                array('t.client_code', 'client_code'),
                array('t.sales_category_code', 'sales_category_code'),
                array('t.sales_category_value', 'sales_category_value'),
                array('t.car_model_code', 'car_model_code'),
                array('t.car_code', 'car_code'),
                array('t.carrier_code', 'carrier_code'),
                array('t.member_code', 'member_code'),
                array('t.driver_name', 'driver_name'),
                array('t.operation_count', 'operation_count'),
                array('t.delivery_category', 'delivery_category'),
                array('t.sales', 'sales'),
                array('t.carrier_cost', 'carrier_cost'),
                array('t.highway_fee', 'highway_fee'),
                array('t.highway_fee_claim', 'highway_fee_claim'),
                array('t.overtime_fee', 'overtime_fee'),
                array('t.remarks', 'remarks'),
                array(\DB::expr('DATE_FORMAT(t.update_datetime,\'%Y-%m-%d\')'), 'update_date')
                );

        // テーブル
        $sub_query->from(array('t_sales_correction', 't'));
        
        //削除フラグ
        $sub_query->where('t.delete_flag', '=', '0');
        // 売上補正番号
        if (trim($conditions['sales_correction_number']) != '') {
            $sub_query->where(\DB::expr('CAST(t.sales_correction_number AS SIGNED)'), '=', $conditions['sales_correction_number']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $sub_query->where('t.division_code', '=', $conditions['division']);
        }
        // 売上ステータス
        if (trim($conditions['sales_status']) != '' && trim($conditions['sales_status']) != '0') {
            $sub_query->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $sub_query->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $sub_query->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $sub_query->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        // 売上区分
        if (trim($conditions['sales_category']) != '' && trim($conditions['sales_category']) != '00') {
            $sub_query->where('t.sales_category_code', '=', $conditions['sales_category']);
        }
        // 車種コード
        if (trim($conditions['car_model']) != '' && trim($conditions['car_model']) != '000') {
            $sub_query->where('t.car_model_code', '=', $conditions['car_model']);
        }
        // 車両コード
        if (trim($conditions['car_code']) != '' && trim($conditions['car_code']) != '0') {
            $sub_query->where('t.car_code', '=', $conditions['car_code']);
        }
        // 日付（FROM）
        if (trim($conditions['sales_date_from']) != '') {
            $sub_query->where('t.sales_date', '>=', $conditions['sales_date_from']);
        }
        // 日付（TO）
        if (trim($conditions['sales_date_to']) != '') {
            $sub_query->where('t.sales_date', '<=', $conditions['sales_date_to']);
        }
        // 運転手
        if (trim($conditions['driver_name']) != '') {
            $sub_query->where(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
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
                array('t.sales_correction_number', 'sales_correction_number'),
                array('t.division_code', 'division'),
                array('md.division_name', 'division'),
                array('t.sales_status', 'sales_status'),
                array('t.sales_date', 'sales_date'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.sales_category_code', 'sales_category_code'),
                array('msc.sales_category_name', 'sales_category_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'sales_category_value'),
                array('mcm.car_model_name', 'car_model'),
                array('t.car_code', 'car_code'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.member_code', 'member_code'),
                array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array('t.operation_count', 'operation_count'),
                array('t.delivery_category', 'delivery_category'),
                array('t.sales', 'sales'),
                array('t.carrier_cost', 'carrier_cost'),
                array('t.highway_fee', 'highway_fee'),
                array('t.highway_fee_claim', 'highway_fee_claim'),
                array('t.overtime_fee', 'overtime_fee'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks'),
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
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_date')
                ->on('mcm.end_date', '>', 't.update_date')
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_date')
                ->on('mm.end_date', '>', 't.update_date')
            ->join(array('m_sales_category', 'msc'), 'left outer')
                ->on('t.sales_category_code', '=', 'msc.sales_category_code');
        
        // 得意先名
        if (trim($conditions['client_name']) != '') {
            $stmt->where('mcl.client_name', 'LIKE', \DB::expr("'%".$conditions['client_name']."%'"));
        }
        // 庸車先名
        if (trim($conditions['carrier_name']) != '') {
            $stmt->where('mca.carrier_name', 'LIKE', \DB::expr("'%".$conditions['carrier_name']."%'"));
        }
        
        // 検索実行
        return $stmt->order_by('t.sales_date', 'DESC')->order_by('t.sales_correction_number', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
    }
}