<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Summary\T0021;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

ini_set("memory_limit", "1000M");

class T0020 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 得意先リスト取得
     */
    public static function getClientList($conditions) {
        
        //得意先名称の項目名設定
        $column_name = self::getColumnName($conditions);
        
        //集計終了日
        $end_date = '';
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 day'));
                break;
            case '2':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 month'));
                break;
            case '3':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 year'));
        }
        
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name')
                );
        
        // 条件
        $stmt->from(array('m_client', 'm'));
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $stmt->where('m.client_code', '=', $conditions['client_code']);
        }
        $stmt->where_open();
        // 適用開始日
        $stmt->where('m.start_date', '<', $end_date);
        // 適用終了日
        $stmt->or_where('m.end_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        $stmt->where_close();
        // グループ化
        $stmt->group_by(\DB::expr($column_name));
        
        // 検索実行
        return $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->execute(self::$db)
            ->as_array();
        
    }
    
    /**
     * 得意先リスト作成
     */
    public static function createClientList($dispatch_list, $sales_correction_list) {
        
        //配車集計、分載集計を結合
        $merge_list = array();
        foreach ($dispatch_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['client_name']]['client_name'] = $dispatch_charter['client_name'];
        }
        foreach ($sales_correction_list as $sales_correction) {
            $index = $sales_correction['client_name'];
            if (!array_key_exists($index, $merge_list)) {
                $merge_list[$index]['client_name'] = $index;
            }
        }
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        sort($result_list);
        
        return $result_list;
        
    }
    
    /**
     * 得意先別配車売上集計
     */
    public static function getDispatchList($conditions) {
        
        //得意先名称の項目名設定
        $column_name = self::getColumnName($conditions);
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $client_code = $conditions['client_code'];
        //配送区分
        $delivery_category = $conditions['delivery_category'];
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['start_date']));
        //集計終了日
        $end_date = '';
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 day'));
                break;
            case '2':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 month'));
                break;
            case '3':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 year'));
        }
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(t.claim_sales)'), 'claim_sales'),
                array(\DB::expr('SUM(t.carrier_payment)'), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.stack_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.stack_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('t.division_code', '=', $division_code);
        }
        // 配送区分
        if (trim($delivery_category) != '' && trim($delivery_category) != '0') {
            $stmt->where('t.delivery_category', '=', $delivery_category);
        }
        $stmt->where_open();
        // 請求売上
        $stmt->where('t.claim_sales', '!=', 0);
        // 庸車支払
        $stmt->or_where('t.carrier_payment', '!=', 0);
        $stmt->where_close();
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(tc.claim_sales)'), 'claim_sales'),
                array(\DB::expr('SUM(tc.carrier_payment)'), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number')
            ->join(array('m_client', 'm'), 'inner')
                ->on('tc.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 'tc.update_datetime')
                ->on('m.end_date', '>', 'tc.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('tc.stack_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('tc.stack_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('t.division_code', '=', $division_code);
        }
        // 配送区分
        if (trim($delivery_category) != '' && trim($delivery_category) != '0') {
            $stmt->where('t.delivery_category', '=', $delivery_category);
        }
        $stmt->where_open();
        // 請求売上
        $stmt->where('tc.claim_sales', '!=', 0);
        // 庸車支払
        $stmt->or_where('tc.carrier_payment', '!=', 0);
        $stmt->where_close();
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計、分載集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['client_name'].$dispatch_charter['stack_date']] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $index = $carrying_charter['client_name'].$carrying_charter['stack_date'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先名と日付の組み合わせが存在する場合は金額を加算
                $merge_list[$index]['claim_sales'] += $carrying_charter['claim_sales'];
                $merge_list[$index]['carrier_payment'] += $carrying_charter['carrier_payment'];
            } else {
                $merge_list[$index] = $carrying_charter;
            }
        }
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        return $result_list;
        
    }
    
    /**
     * 得意先別共配便売上集計
     */
    public static function getDispatchShareList($conditions) {
        
        //得意先名称の項目名設定
        $column_name = self::getColumnName($conditions);
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $client_code = $conditions['client_code'];
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['start_date']));
        //集計終了日
        $end_date = '';
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 day'));
                break;
            case '2':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 month'));
                break;
            case '3':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 year'));
        }
        
        $date_case = '(CASE WHEN t.delivery_code = 1 THEN t.delivery_date ELSE '
                . '(CASE WHEN t.delivery_code = 2 THEN t.pickup_date ELSE IFNULL(t.delivery_date, t.pickup_date) END) END)';
        
        //請求データ集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(CASE WHEN t.delivery_code = 3 THEN t.price * -1 ELSE t.price END)'), 'claim_sales'),
                array(\DB::expr(0), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_bill_share', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.destination_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.destination_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('t.division_code', '=', $division_code);
        }
        // 請求売上
        $stmt->where('t.price', '!=', 0);
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $bill_share_list = $stmt->execute(self::$db)->as_array();
        
        //配車データ集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr(0), 'claim_sales'),
                array(\DB::expr('SUM(t.carrier_payment)'), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_share', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where(\DB::expr($date_case), '>=', $start_date);
        // 集計終了日
        $stmt->where(\DB::expr($date_case), '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('t.division_code', '=', $division_code);
        }
        // 庸車支払
        $stmt->where('t.carrier_payment', '!=', 0);
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $dispatch_share_list = $stmt->execute(self::$db)->as_array();
        
        //請求データ集計、配車データ集計を結合
        $merge_list = array();
        foreach ($bill_share_list as $bill_share) {
            $merge_list[$bill_share['client_name'].$bill_share['destination_date']] = $bill_share;
        }
        foreach ($dispatch_share_list as $dispatch_share) {
            $index = $dispatch_share['client_name'].$dispatch_share['destination_date'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先名と日付の組み合わせが存在する場合は金額を加算
                $merge_list[$index]['carrier_payment'] += $dispatch_share['carrier_payment'];
            } else {
                $merge_list[$index] = $dispatch_share;
            }
        }
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        return $result_list;
        
    }
    
    /**
     * 得意先別入出庫料・保管料売上集計
     */
    public static function getStockList($conditions) {
        
        //得意先名称の項目名設定
        $column_name = self::getColumnName($conditions);
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $client_code = $conditions['client_code'];
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['start_date']));
        //集計終了日
        $end_date = '';
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 day'));
                break;
            case '2':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 month'));
                break;
            case '3':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 year'));
        }
        
        //入庫料集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(t.fee)'), 'in_fee'),
                array(\DB::expr(0), 'out_fee'),
                array(\DB::expr(0), 'storage_fee')
                );
        
        // テーブル
        $stmt->from(array('t_stock_change', 't'))
            ->join(array('t_stock', 's'), 'inner')
                ->on('s.stock_number', '=', 't.stock_number')
            ->join(array('m_client', 'm'), 'inner')
                ->on('s.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 入出庫区分コード
        $stmt->where('t.stock_change_code', 'IN', array('1','3','5'));
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.destination_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.destination_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('s.division_code', '=', $division_code);
        }
        // 売上
        $stmt->where('t.fee', '!=', 0);
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $stock_change_in_list = $stmt->execute(self::$db)->as_array();
        
        //出庫料集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr(0), 'in_fee'),
                array(\DB::expr('SUM(t.fee)'), 'out_fee'),
                array(\DB::expr(0), 'storage_fee')
                );
        
        // テーブル
        $stmt->from(array('t_stock_change', 't'))
            ->join(array('t_stock', 's'), 'inner')
                ->on('s.stock_number', '=', 't.stock_number')
            ->join(array('m_client', 'm'), 'inner')
                ->on('s.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 入出庫区分コード
        $stmt->where('t.stock_change_code', 'IN', array('2','4','6'));
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.destination_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.destination_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('s.division_code', '=', $division_code);
        }
        // 売上
        $stmt->where('t.fee', '!=', 0);
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $stock_change_out_list = $stmt->execute(self::$db)->as_array();
        
        //保管料集計-------------------------------------------------------------
        $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr(0), 'in_fee'),
                array(\DB::expr(0), 'out_fee'),
                array(\DB::expr('SUM(t.storage_fee)'), 'storage_fee')
                );
        
        // テーブル
        $stmt->from(array('t_storage_fee', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.closing_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.closing_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where('t.division_code', '=', $division_code);
        }
        // 売上
        $stmt->where('t.storage_fee', '!=', 0);
        
        // グループ化
        $stmt->group_by(\DB::expr($column_name))
            ->group_by(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by(\DB::expr($column_name), 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $stock_list = $stmt->execute(self::$db)->as_array();
        
        //請求データ集計、配車データ集計を結合
        $merge_list = array();
        foreach ($stock_change_in_list as $stock_change_in) {
            $merge_list[$stock_change_in['client_name'].$stock_change_in['destination_date']] = $stock_change_in;
        }
        foreach ($stock_change_out_list as $stock_change_out) {
            $index = $stock_change_out['client_name'].$stock_change_out['destination_date'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先名と日付の組み合わせが存在する場合は金額を加算
                $merge_list[$index]['out_fee'] += $stock_change_out['out_fee'];
            } else {
                $merge_list[$index] = $stock_change_out;
            }
        }
        foreach ($stock_list as $stock) {
            $index = $stock['client_name'].$stock['destination_date'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先名と日付の組み合わせが存在する場合は金額を加算
                $merge_list[$index]['storage_fee'] += $stock['storage_fee'];
            } else {
                $merge_list[$index] = $stock;
            }
        }
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        return $result_list;
        
    }
    
    /**
     * 得意先別売上補正売上集計
     */
    public static function getSalesCorrectionList($conditions) {
        
        //得意先名称の項目名設定
        $column_name = self::getColumnName($conditions);
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['start_date']));
        //集計終了日
        $end_date = '';
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 day'));
                break;
            case '2':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 month'));
                break;
            case '3':
                $end_date = date('Y-m-d', strtotime($conditions['end_date'] . '+1 year'));
        }
        
        if ($conditions['aggregation_unit_date'] == "1") {
            $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('SUM(t.sales)'), 'sales'),
                array(\DB::expr('SUM(t.carrier_cost)'), 'carrier_cost')
                );
        } else {
            $stmt = \DB::select(
                array(\DB::expr($column_name), 'client_name'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'sales_date'),
                array(\DB::expr('SUM(t.sales)'), 'sales'),
                array(\DB::expr('SUM(t.carrier_cost)'), 'carrier_cost')
                );
        }
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime');
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $stmt->where('m.client_code', '=', $conditions['client_code']);
        }
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $end_date);
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $stmt->where('t.division_code', '=', $conditions['division']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        $stmt->where_open();
        // 売上
        $stmt->where('t.sales', '!=', 0);
        // 庸車費
        $stmt->or_where('t.carrier_cost', '!=', 0);
        $stmt->where_close();
        
        if ($conditions['aggregation_unit_date'] == "1") {
            // グループ化
            $stmt->group_by(\DB::expr($column_name))
                ->group_by('t.sales_category_code');
            // ソート
            $stmt->order_by(\DB::expr($column_name), 'ASC')
                ->order_by('t.sales_category_code', 'ASC');
        } else {
            // グループ化
            $stmt->group_by(\DB::expr($column_name))
                ->group_by('t.sales_category_code')
                ->group_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'));
            // ソート
            $stmt->order_by(\DB::expr($column_name), 'ASC')
                ->order_by('t.sales_category_code', 'ASC')
                ->order_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'ASC');
        }
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 得意先名称の項目名取得
     */
    public static function getColumnName($conditions) {
        //得意先名称の項目名設定
        $column_name = "";
        switch ($conditions['aggregation_unit_company']) {
            case '1':
                $column_name = "CONCAT(m.client_name_company, '(', LPAD(m.client_company_code, 5, '0'), ')')";
                break;
            case '2':
                $column_name = "CONCAT(IFNULL(m.client_name_sales_office, ''), '(', IFNULL(LPAD(m.client_sales_office_code, 5, '0'), '-'), ')')";
                break;
            case '3':
                $column_name = "CONCAT(IFNULL(m.client_name, ''), '(', IFNULL(LPAD(m.client_department_code, 5, '0'), '-'), ')')";
        }
        return $column_name;
    }
    
    /**
     * 日付フォーマット取得
     */
    public static function getDateFormat($conditions) {
        //日付フォーマット設定
        $date_format = "";
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $date_format = '%Y-%m-%d';
                break;
            case '2':
                $date_format = '%Y-%m';
                break;
            case '3':
                $date_format = '%Y';
        }
        return $date_format;
    }
    
    /**
     * 出力条件取得
     */
    public static function getConditions() {
        $conditions 	= array_fill_keys(array(
            'summary_category',
        	'division',
            'client_radio',
        	'client_code',
        	'delivery_category',
        	'aggregation_unit_date',
        	'aggregation_unit_company',
            'start_year',
            'start_month',
            'start_day',
            'end_year',
            'end_month',
            'end_day',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0020_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $start_date = null;
        $end_date = null;
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $start_date = $conditions['start_year'].'-'.sprintf('%02d', $conditions['start_month']).'-'.sprintf('%02d', $conditions['start_day']);
                $end_date = $conditions['end_year'].'-'.sprintf('%02d', $conditions['end_month']).'-'.sprintf('%02d', $conditions['end_day']);
                break;
            case '2':
                $start_date = $conditions['start_year'].'-'.sprintf('%02d', $conditions['start_month']).'-01';
                $end_date = $conditions['end_year'].'-'.sprintf('%02d', $conditions['end_month']).'-01';
                break;
            case '3':
                $start_date = $conditions['start_year'].'-01-01';
                $end_date = $conditions['end_year'].'-01-01';
        }
        
        $result = array('division' => $conditions['division'],
                        'summary_category' => $conditions['summary_category'],
                        'delivery_category' => $conditions['delivery_category'],
                        'client_code' => $conditions['client_code'],
                        'aggregation_unit_date' => $conditions['aggregation_unit_date'],
                        'aggregation_unit_company' => $conditions['aggregation_unit_company'],
                        'start_date' => $start_date,
                        'start_year' => $conditions['start_year'],
                        'start_month' => $conditions['start_month'],
                        'start_day' => $conditions['start_day'],
                        'end_date' => $end_date,
                        'end_year' => $conditions['end_year'],
                        'end_month' => $conditions['end_month'],
                        'end_day' => $conditions['end_day']);
        
        return $result;
    }
    
    /**
     * 集計開始・終了の入力チェック
     */
    public static function checkDate() {
        $conditions = self::getConditions();
        
        //日付単体チェック（日単位の場合のみチェック）
        if ($conditions['aggregation_unit_date'] == '1') {
            if (!checkdate($conditions['start_month'], $conditions['start_day'], $conditions['start_year'])) {
                return str_replace('XXXXX','集計開始日',\Config::get('m_CW0006'));
            }
            if (!checkdate($conditions['end_month'], $conditions['end_day'], $conditions['end_year'])) {
                return str_replace('XXXXX','集計終了日',\Config::get('m_CW0006'));
            }
        }
        
        //日付相関チェック
        if (strtotime($conditions['start_date']) > strtotime($conditions['end_date'])) {
            return str_replace('XXXXX','集計日付',\Config::get('m_CW0007'));
        }
        
        //日付範囲チェック
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $start_date = new \DateTime($conditions['start_date']);
                $end_date = new \DateTime($conditions['end_date']);
                if ($start_date->diff($end_date)->format('%a') >= 31) {
                    return str_replace('XXXXX','３１日',\Config::get('m_TW0001'));
                }
                break;
            case '2':
                $start_date = (int)$conditions['start_year']*12 + (int)$conditions['start_month'];
                $end_date = (int)$conditions['end_year']*12 + (int)$conditions['end_month'];
                if ($end_date - $start_date >= 12) {
                    return str_replace('XXXXX','１２月',\Config::get('m_TW0001'));
                }
                break;
            case '3':
                $start_date = (int)$conditions['start_year'];
                $end_date = (int)$conditions['end_year'];
                if ($end_date - $start_date >= 10) {
                    return str_replace('XXXXX','１０年',\Config::get('m_TW0001'));
                }
        }
    }
    
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $conditions = self::getConditions();
        
        $division_list = GenerateList::getDivisionList(true, self::$db);
        $division_name = $division_list[$conditions['division']];
        if ($conditions['division'] == '000') {
            $division_name = '全課';
        }
        
        //日付フォーマット設定
        $date_format = "";
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $date_format = 'Y年m月d日';
                break;
            case '2':
                $date_format = 'Y年m月';
                break;
            case '3':
                $date_format = 'Y年';
        }
        
        $filename = "【".$division_name."】得意先別売上集計表（".date($date_format,  strtotime($conditions['start_date']))."～".date($date_format,  strtotime($conditions['end_date']))."）";
        return $filename;
        
    }
    
    /**
     * TSV作成処理
     */
    public static function createTsv() {
        $conditions = self::getConditions();
        if ($conditions['aggregation_unit_date'] == "1") {
            T0021::createTsvDay();
        } elseif ($conditions['aggregation_unit_date'] == "2") {
            T0021::createTsvMonth();
        } elseif ($conditions['aggregation_unit_date'] == "3") {
            T0021::createTsvYear();
        }
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        $conditions['aggregation_unit_date'] = '1';
        
        $tpl_dir = DOCROOT.'assets/template/';
        $tmp_dir = APPPATH."tmp/";
        $name = self::getExcelName().".xlsx";
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template得意先別売上集計（日単位）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('得意先別売上集計（表）');
        
        //帳票タイトル出力
        $division_list = GenerateList::getDivisionList(true, self::$db);
        $division_name = $division_list[$conditions['division']];
        if ($conditions['division'] == '000') {
            $division_name = '全課';
        }
        
        $delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        $delivery_category_name = $delivery_category_list[$conditions['delivery_category']];
        if ($conditions['delivery_category'] == '0') {
            $delivery_category_name = '';
        }
        
        $sggregation_list = GenerateList::getAggregationUnitDateList();
        $title = "■【".$division_name."】得意先別売上集計表（".date('Y年m月d日',  strtotime($conditions['start_date']))."～".date('Y年m月d日',  strtotime($conditions['end_date']))."-".$sggregation_list[$conditions['aggregation_unit_date']]."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //日見出し出力
        $col_num_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        $col_num = 3;
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            $worksheet->setCellValueByColumnAndRow($col_num,3, date('d',  strtotime($i)));
            $col_num_list += array($i => $col_num);
            $col_num++;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        for ($i = $col_start; $i <= 33; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //得意先リスト取得
        $client_list = self::getClientList($conditions);
        $client_count = 0;
        if (is_countable($client_list)){
            $client_count = count($client_list);
        }
                
        //得意先見出し出力
        $row_num_list = array();
        $row_num = 4;
        foreach ($client_list as $client) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, $client['client_name']);
            $row_num_list += array($client['client_name']=>$row_num);
            $row_num += 4;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        $max_row = $worksheet->getHighestRow();
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //配車集計データ取得
        $dispatch_list = self::getDispatchList($conditions);
        
        //配車集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['client_name']], $dispatch['claim_sales']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['client_name']] + 1, $dispatch['carrier_payment']);
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //売上区分見出し出力
        $col_num_list = array();
        $col_num = 34;
        foreach ($sales_category_list as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($col_num, 3, $value);
            $col_num_list += array($key => $col_num);
            $col_num++;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        for ($i = $col_start; $i <= 43; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //売上補正集計データ取得
        $sales_correction_list = self::getSalesCorrectionList($conditions);
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$sales_correction['sales_category_code']], $row_num_list[$sales_correction['client_name']], $sales_correction['sales']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$sales_correction['sales_category_code']], $row_num_list[$sales_correction['client_name']] + 1, $sales_correction['carrier_cost']);
        }
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0004', \Config::get('m_TI0004'), '', self::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(self::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(self::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = self::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
        
    /**
     * TOP3のリスト取得
     */
    public static function getTop3($dispatch_charter_list, $sales_correction_list) {
        //配車リスト、売上補正リストを集計
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $index = $dispatch_charter['client_name'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先が存在する場合は金額を加算
                $merge_list[$index]['claim_sales'] += $dispatch_charter['claim_sales'];
            } else {
                $merge_list[$index] = $dispatch_charter;
            }
        }
        foreach ($sales_correction_list as $sales_correction) {
            $index = $sales_correction['client_name'];
            if (array_key_exists($index, $merge_list)) {
                //既に得意先が存在する場合は金額を加算
                $merge_list[$index]['claim_sales'] += $sales_correction['claim_sales'];
            } else {
                $merge_list[$index] = $sales_correction;
            }
        }
        
        //ソートキー作成
        $sort_key = array();
        foreach ($merge_list as $merge) {
            $sort_key[] = $merge['claim_sales'];
        }
        
        //売上金額でソート
        array_multisort($sort_key, SORT_ASC, $merge_list);
        
        //売上金額TOP3の得意先名を取得
        $result_list = array();
        for ($i = 0; $i < 3; $i++) {
            $result_list[] = $merge_list[$i]['client_name'];
        }
        
        return $result_list;
    }
}