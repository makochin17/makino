<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Summary\T0011;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

ini_set("memory_limit", "1000M");

class T0010 extends \Model {

    public static $db       = 'MAKINO';
    
    /**
     * 課別配車売上集計
     */
    public static function getDispatchList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(t.claim_sales)'), 'claim_sales'),
                array(\DB::expr('SUM(t.carrier_payment)'), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'));
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 集計開始日
        $stmt->where('t.stack_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('t.stack_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('t.stack_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('t.stack_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        
        // グループ化
        $stmt->group_by('t.division_code')
            ->group_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('t.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別売上補正売上集計
     * $mode　1:日付あり検索　2：日付なし検索
     */
    public static function getSalesCorrectionList($conditions, $mode) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        if ($mode == 1) {
            $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'sales_date'),
                array(\DB::expr('SUM(t.sales)'), 'sales'),
                array(\DB::expr('SUM(t.carrier_cost)'), 'carrier_cost')
                );
        } else {
            $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('SUM(t.sales)'), 'sales'),
                array(\DB::expr('SUM(t.carrier_cost)'), 'carrier_cost')
                );
        }
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'));
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 集計開始日
        $stmt->where('t.sales_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('t.sales_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('t.sales_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('t.sales_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        
        // グループ化
        $stmt->group_by('t.division_code')
            ->group_by('t.sales_category_code');
        if ($mode == 1)$stmt->group_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('t.division_code', 'ASC')
            ->order_by('t.sales_category_code', 'ASC');
        if ($mode == 1)$stmt->order_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別共配便売上集計
     */
    public static function getDispatchShareCSList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(CASE WHEN t.delivery_code = 3 THEN t.price * -1 ELSE t.price END)'), 'claim_sales'),
                );
        
        // テーブル
        $stmt->from(array('t_bill_share', 't'));
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 集計開始日
        $stmt->where('t.destination_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('t.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('t.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('t.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        
        // グループ化
        $stmt->group_by('t.division_code')
            ->group_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('t.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別共配便売上集計
     */
    public static function getDispatchShareCPList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        $date_case = '(CASE WHEN t.delivery_code = 1 THEN t.delivery_date ELSE '
                . '(CASE WHEN t.delivery_code = 2 THEN t.pickup_date ELSE IFNULL(t.delivery_date, t.pickup_date) END) END)';
        
        //項目
        $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(t.carrier_payment)'), 'carrier_payment')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 集計開始日
        $stmt->where(\DB::expr($date_case), '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where(\DB::expr($date_case), '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where(\DB::expr($date_case), '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where(\DB::expr($date_case), '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        
        // グループ化
        $stmt->group_by('t.division_code')
            ->group_by(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('t.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT('.$date_case.', \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別入庫料集計
     */
    public static function getStockChangeInList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        $stmt = \DB::select(
                array('s.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(sc.fee)'), 'fee')
                );
        
        // テーブル
        $stmt->from(array('t_stock_change', 'sc'))
            ->join(array('t_stock', 's'), 'inner')
                ->on('s.stock_number', '=', 'sc.stock_number');
        // 削除フラグ
        $stmt->where('sc.delete_flag', '=', '0');
        // 入出庫区分コード
        $stmt->where('sc.stock_change_code', 'IN', array('1','3','5'));
        // 集計開始日
        $stmt->where('sc.destination_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        
        // グループ化
        $stmt->group_by('s.division_code')
            ->group_by(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('s.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別出庫料集計
     */
    public static function getStockChangeOutList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        $stmt = \DB::select(
                array('s.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'), 'destination_date'),
                array(\DB::expr('SUM(sc.fee)'), 'fee')
                );
        
        // テーブル
        $stmt->from(array('t_stock_change', 'sc'))
            ->join(array('t_stock', 's'), 'inner')
                ->on('s.stock_number', '=', 'sc.stock_number');
        // 削除フラグ
        $stmt->where('sc.delete_flag', '=', '0');
        // 入出庫区分コード
        $stmt->where('sc.stock_change_code', 'IN', array('2','4','6'));
        // 集計開始日
        $stmt->where('sc.destination_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('sc.destination_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        
        // グループ化
        $stmt->group_by('s.division_code')
            ->group_by(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('s.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(sc.destination_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 課別保管料集計
     */
    public static function getStorageFeeList($conditions) {
        
        $date_format = '%Y-%m-%d';
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
        
        //項目
        $stmt = \DB::select(
                array('t.division_code', 'division_code'),
                array(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'), 'closing_date'),
                array(\DB::expr('SUM(t.storage_fee)'), 'storage_fee')
                );
        
        // テーブル
        $stmt->from(array('t_storage_fee', 't'));
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 集計開始日
        $stmt->where('t.closing_date', '>=', date('Y-m-d', strtotime($conditions['start_date'])));
        // 集計終了日
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                $stmt->where('t.closing_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 day')));
                break;
            case '2':
                $stmt->where('t.closing_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 month')));
                break;
            case '3':
                $stmt->where('t.closing_date', '<', date('Y-m-d', strtotime($conditions['end_date'] . '+1 year')));
        }
        
        // グループ化
        $stmt->group_by('t.division_code')
            ->group_by(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('t.division_code', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.closing_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 出力条件取得
     */
    public static function getConditions() {
        $conditions 	= array_fill_keys(array(
            'summary_category',
        	'delivery_category',
        	'aggregation_unit_date',
            'start_year',
            'start_month',
            'start_day',
            'end_year',
            'end_month',
            'end_day',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0010_list', array())) {
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
        
        $result = array('summary_category' => $conditions['summary_category'],
                        'delivery_category' => $conditions['delivery_category'],
                        'aggregation_unit_date' => $conditions['aggregation_unit_date'],
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
        
        $filename = "課別売上集計表（".date($date_format,  strtotime($conditions['start_date']))."～".date($date_format,  strtotime($conditions['end_date']))."）";
        return $filename;
        
    }
    
    /**
     * 表見出し取得
     */
    public static function getCaption() {
        $conditions = self::getConditions();
        
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        
        $result = array();
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
                    $result[] = date('j',  strtotime($i)).'日';
                }
                break;
            case '2':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 month'))) {
                    $result[] = date('n',  strtotime($i)).'月';
                }
                break;
            case '3':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 year'))) {
                    $result[] = date('Y',  strtotime($i)).'年';
                }
        }
        
        return $result;
    }
    
    /**
     * 見出し配列取得
     */
    public static function getColNumList($conditions) {
        $col_num_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        $col_num = 0;
        switch ($conditions['aggregation_unit_date']) {
            case '1':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
                    $col_num_list += array($i => $col_num);
                    $col_num++;
                }
                break;
            case '2':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 month'))) {
                    $col_num_list += array(date('Y-m', strtotime($i)) => $col_num);
                    $col_num++;
                }
                break;
            case '3':
                for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 year'))) {
                    $col_num_list += array(date('Y', strtotime($i)) => $col_num);
                    $col_num++;
                }
        }
        return $col_num_list;
    }
    
    /**
     * 集計データ取得（配車データ）
     */
    public static function getSummaryDataDispatch() {
        $conditions = self::getConditions();
        
        //見出し配列作成
        $col_num_list = self::getColNumList($conditions);
        
        //行テンプレート作成
        $row_tmp_base = array();
        foreach ($col_num_list as $key => $val) {
            $row_tmp_base[] = 0;
        }
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, self::$db);
        
        //課見出し配列作成
        $summary_data = array();
        $row_num_list = array();
        $row_num = 0;
        foreach ($division_list as $key => $value) {
            //返却値の雛形作成
            $summary_data[] = array('division_name' => $value, 'claim_sales' => $row_tmp_base, 'carrier_payment' => $row_tmp_base, 'margin' => $row_tmp_base, 'margin_rate' => $row_tmp_base);
            //課見出し配列作成
            $row_num_list += array($key => $row_num);
            $row_num++;
        }
        
        //配車集計データ取得
        $dispatch_list = self::getDispatchList($conditions);
        
        //配車集計データから集計リスト作成
        foreach ($dispatch_list as $dispatch) {
            $col_num = $col_num_list[$dispatch['stack_date']];
            $row_num = $row_num_list[$dispatch['division_code']];
            
            //差益
            $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];
            
            //差益率
            $margin_rate = 0;
            if ($dispatch['carrier_payment'] > 0) {
                $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
            }
            
            $summary_data[$row_num]['claim_sales'][$col_num] = $dispatch['claim_sales'];
            $summary_data[$row_num]['carrier_payment'][$col_num] = $dispatch['carrier_payment'];
            $summary_data[$row_num]['margin'][$col_num] = $margin;
            $summary_data[$row_num]['margin_rate'][$col_num] = $margin_rate;
        }
        
        return $summary_data;
    }
    
    /**
     * 集計データ取得（売上補正データ）
     */
    public static function getSummaryDataSalesCorrection() {
        $conditions = self::getConditions();
        
        //見出し配列作成
        $col_num_list = self::getColNumList($conditions);
        
        //行テンプレート作成
        $row_tmp_base = array();
        foreach ($col_num_list as $key => $val) {
            $row_tmp_base[] = 0;
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, self::$db);
        
        //格納位置配列作成
        $summary_data = array();
        $category_num_list = array();
        $row_num_list = array();
        $row_num = 0;
        foreach ($division_list as $division_key => $division_value) {
            //返却値の雛形作成
            $summary_data[] = array('division_name' => $division_value, 'summary_list' => array());
                
            $category_num = 0;
            foreach ($sales_category_list as $category_key => $category_value) {
                //返却値の雛形作成
                $summary_data[$row_num]['summary_list'][$category_key] = array('claim_sales' => $row_tmp_base, 'carrier_payment' => $row_tmp_base, 'margin' => $row_tmp_base, 'margin_rate' => $row_tmp_base);
                //売上区分見出し配列作成
                $category_num_list += array($category_key => $category_num);
                $category_num++;
            }
            //課見出し配列作成
            $row_num_list += array($division_key => $row_num);
            $row_num++;
        }
        
        //売上補正集計データ取得
        $sales_correction_list = self::getSalesCorrectionList($conditions, 1);
        
        //売上補正集計データから集計リスト作成
        foreach ($sales_correction_list as $sales_correction) {
            $col_num = $col_num_list[$sales_correction['sales_date']];
            $row_num = $row_num_list[$sales_correction['division_code']];
            $category_num = $sales_correction['sales_category_code'];
            
            //差益
            $margin = $sales_correction['sales'] - $sales_correction['carrier_cost'];
            
            //差益率
            $margin_rate = 0;
            if ($sales_correction['carrier_cost'] > 0) {
                $margin_rate = round($margin / $sales_correction['carrier_cost'] * 100, 1);
            }
            
            $summary_data[$row_num]['summary_list'][$category_num]['claim_sales'][$col_num] = $sales_correction['sales'];
            $summary_data[$row_num]['summary_list'][$category_num]['carrier_payment'][$col_num] = $sales_correction['carrier_cost'];
            $summary_data[$row_num]['summary_list'][$category_num]['margin'][$col_num] = $margin;
            $summary_data[$row_num]['summary_list'][$category_num]['margin_rate'][$col_num] = $margin_rate;
        }
        
        return $summary_data;
    }
    
    /**
     * 集計データ取得（共配便データ）
     */
    public static function getSummaryDataDispatchShare() {
        $conditions = self::getConditions();
        
        //見出し配列作成
        $col_num_list = self::getColNumList($conditions);
        
        //行テンプレート作成
        $row_tmp_base = array();
        foreach ($col_num_list as $key => $val) {
            $row_tmp_base[] = 0;
        }
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, self::$db);
        
        //課見出し配列作成
        $summary_data = array();
        $row_num_list = array();
        $row_num = 0;
        foreach ($division_list as $key => $value) {
            //返却値の雛形作成
            $summary_data[] = array('division_name' => $value, 'claim_sales' => $row_tmp_base, 'carrier_payment' => $row_tmp_base, 'margin' => $row_tmp_base, 'margin_rate' => $row_tmp_base);
            //課見出し配列作成
            $row_num_list += array($key => $row_num);
            $row_num++;
        }
        
        //配車集計データ取得
        $claim_sales_list = self::getDispatchShareCSList($conditions);
        $carrier_payment_list = self::getDispatchShareCPList($conditions);
        
        //配車集計データから集計リスト作成
        foreach ($claim_sales_list as $data) {
            $col_num = $col_num_list[$data['destination_date']];
            $row_num = $row_num_list[$data['division_code']];
            
            $summary_data[$row_num]['claim_sales'][$col_num] = $data['claim_sales'];
        }
        
        foreach ($carrier_payment_list as $data) {
            $col_num = $col_num_list[$data['destination_date']];
            $row_num = $row_num_list[$data['division_code']];
            
            $summary_data[$row_num]['carrier_payment'][$col_num] = $data['carrier_payment'];
        }
        
        foreach ($row_num_list as $row_num) {
            foreach ($col_num_list as $col_num) {
                $claim_sales = $summary_data[$row_num]['claim_sales'][$col_num];
                $carrier_payment = $summary_data[$row_num]['carrier_payment'][$col_num];
                
                //差益
                $margin = $claim_sales - $carrier_payment;

                //差益率
                $margin_rate = 0;
                if ($carrier_payment > 0) {
                    $margin_rate = round($margin / $carrier_payment * 100, 1);
                }

                $summary_data[$row_num]['margin'][$col_num] = $margin;
                $summary_data[$row_num]['margin_rate'][$col_num] = $margin_rate;
            }
        }
        
        return $summary_data;
    }
    
    /**
     * 集計データ取得（入出庫料・保管料）
     */
    public static function getSummaryDataStock() {
        $conditions = self::getConditions();
        
        //見出し配列作成
        $col_num_list = self::getColNumList($conditions);
        
        //行テンプレート作成
        $row_tmp_base = array();
        foreach ($col_num_list as $key => $val) {
            $row_tmp_base[] = 0;
        }
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, self::$db);
        
        //課見出し配列作成
        $summary_data = array();
        $row_num_list = array();
        $row_num = 0;
        foreach ($division_list as $key => $value) {
            //返却値の雛形作成
            $summary_data[] = array('division_name' => $value, 'in_fee' => $row_tmp_base, 'out_fee' => $row_tmp_base, 'storage_fee' => $row_tmp_base);
            //課見出し配列作成
            $row_num_list += array($key => $row_num);
            $row_num++;
        }
        
        //集計データ取得
        $in_fee_list = self::getStockChangeInList($conditions);
        $out_fee_list = self::getStockChangeOutList($conditions);
        $storage_fee_list = self::getStorageFeeList($conditions);
        
        //配車集計データから集計リスト作成
        foreach ($in_fee_list as $data) {
            $col_num = $col_num_list[$data['destination_date']];
            $row_num = $row_num_list[$data['division_code']];
            
            $summary_data[$row_num]['in_fee'][$col_num] = $data['fee'];
        }
        
        foreach ($out_fee_list as $data) {
            $col_num = $col_num_list[$data['destination_date']];
            $row_num = $row_num_list[$data['division_code']];
            
            $summary_data[$row_num]['out_fee'][$col_num] = $data['fee'];
        }
        
        foreach ($storage_fee_list as $data) {
            $col_num = $col_num_list[$data['closing_date']];
            $row_num = $row_num_list[$data['division_code']];
            
            $summary_data[$row_num]['storage_fee'][$col_num] = $data['storage_fee'];
        }
        
        return $summary_data;
    }
    
    /**
     * TSV作成処理
     */
    public static function createTsv() {
        $conditions = self::getConditions();
        if ($conditions['aggregation_unit_date'] == "1") {
            T0011::createTsvDay();
        } elseif ($conditions['aggregation_unit_date'] == "2") {
            T0011::createTsvMonth();
        } elseif ($conditions['aggregation_unit_date'] == "3") {
            T0011::createTsvYear();
        }
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template課別売上集計（日単位）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('課別売上集計（表）');
        
        //帳票タイトル出力
        $delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        $delivery_category_name = "-".$delivery_category_list[$conditions['delivery_category']];
        if ($conditions['delivery_category'] == '0') {
            $delivery_category_name = '';
        }
        
        $title = "■課別売上集計表（".date('Y年m月d日',  strtotime($conditions['start_date']))."～".date('Y年m月d日',  strtotime($conditions['end_date'])).$delivery_category_name."）";
        $worksheet->setCellValue('A1', $title);
        
        //日見出し出力
        $row_num_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        $row_num = 4;
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, date('d',  strtotime($i)));
            $row_num_list += array($i => $row_num);
            $row_num++;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        for ($i = $row_start; $i <= 34; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, self::$db);
                
        //課見出し出力
        $col_num_list = array();
        $col_num = 2;
        foreach ($division_list as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($col_num, 2, $value);
            $col_num_list += array($key => $col_num);
            $col_num += 4;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        $max_col = Coordinate::columnIndexFromString($worksheet->getHighestColumn()) - 4;
        for ($i = $col_start; $i <= $max_col; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //配車集計データ取得
        $dispatch_list = self::getDispatchList($conditions);
        
        //配車集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['division_code']], $row_num_list[$dispatch['stack_date']], $dispatch['claim_sales']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['division_code']] + 1, $row_num_list[$dispatch['stack_date']], $dispatch['carrier_payment']);
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //売上区分見出し出力
        $row_num_list = array();
        $row_num = 35;
        foreach ($sales_category_list as $key => $value) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, $value);
            $row_num_list += array($key => $row_num);
            $row_num++;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        for ($i = $row_start; $i <= 44; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //売上補正集計データ取得
        $sales_correction_list = self::getSalesCorrectionList($conditions, 2);
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$sales_correction['division_code']], $row_num_list[$sales_correction['sales_category_code']], $sales_correction['sales']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$sales_correction['division_code']] + 1, $row_num_list[$sales_correction['sales_category_code']], $sales_correction['carrier_cost']);
        }
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0001', \Config::get('m_TI0001'), '', self::$db);
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

}