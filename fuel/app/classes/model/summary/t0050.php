<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Summary\T0051;

ini_set("memory_limit", "1000M");

class T0050 extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 車種リスト取得
     */
    public static function getCarModelDateDesignationList($conditions) {

        //車種コード
        $car_model_code = $conditions['car_model_code'];
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
        
        // 項目
        $stmt = \DB::select(
                array('m.aggregation_tonnage', 'car_model_name')
                );
        
        // テーブル
        $stmt->from(array('m_car_model', 'm'));
        
        // 車種コード
        if (trim($car_model_code) != '') {
            $stmt->where('m.car_model_code', '=', $car_model_code);
        }
        $stmt->where_open();
        // 適用開始日
        $stmt->where('m.start_date', '<', $end_date);
        // 適用終了日
        $stmt->or_where('m.end_date', '>=', $start_date);
        $stmt->where_close();
        // グループ化
        $stmt->group_by('m.aggregation_tonnage');
        
        // ソート
        $stmt->order_by('m.aggregation_tonnage', 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
    }
    
    /**
     * 車種別配車集計（チャーター便）
     */
    public static function getdispatchList($conditions) {
        
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //課コード
        $division_code = $conditions['division'];
        //車種コード
        $car_model_code = $conditions['car_model_code'];
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
                array('m.aggregation_tonnage', 'car_model_name'),
                array(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN 1 ELSE 0 END)'), 'mycar_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN 1 ELSE 0 END)'), 'carrier_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN m.freight_tonnage ELSE 0 END)'), 'mycar_tonnage'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN m.freight_tonnage ELSE 0 END)'), 'carrier_tonnage')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_car_model', 'm'), 'inner')
                ->on('t.car_model_code', '=', 'm.car_model_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mc'), 'inner')
                ->on('t.carrier_code', '=', 'mc.carrier_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('md.carrier_code', '=', 't.carrier_code');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 集計開始日
        $stmt->where('t.stack_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.stack_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where(\DB::expr('(CASE WHEN mc.company_section = 1 THEN md.division_code ELSE t.division_code END)'), '=', $division_code);
        }
        // 車種コード
        if (trim($car_model_code) != '') {
            $stmt->where('t.car_model_code', '=', $car_model_code);
        }
        // 配送区分
        if (trim($delivery_category) != '' && trim($delivery_category) != '0') {
            $stmt->where('t.delivery_category', '=', $delivery_category);
        }
        
        // グループ化
        $stmt->group_by('m.aggregation_tonnage')
            ->group_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('m.aggregation_tonnage', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(t.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.aggregation_tonnage', 'car_model_name'),
                array(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN 1 ELSE 0 END)'), 'mycar_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN 1 ELSE 0 END)'), 'carrier_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN m.freight_tonnage ELSE 0 END)'), 'mycar_tonnage'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN m.freight_tonnage ELSE 0 END)'), 'carrier_tonnage')
                );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number')
            ->join(array('m_car_model', 'm'), 'inner')
                ->on('tc.car_model_code', '=', 'm.car_model_code')
                ->on('m.start_date', '<=', 'tc.update_datetime')
                ->on('m.end_date', '>', 'tc.update_datetime')
            ->join(array('m_carrier', 'mc'), 'inner')
                ->on('tc.carrier_code', '=', 'mc.carrier_code')
                ->on('mc.start_date', '<=', 'tc.update_datetime')
                ->on('mc.end_date', '>', 'tc.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('md.carrier_code', '=', 'tc.carrier_code');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 集計開始日
        $stmt->where('tc.stack_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('tc.stack_date', '<', $end_date);
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $stmt->where(\DB::expr('(CASE WHEN mc.company_section = 1 THEN md.division_code ELSE t.division_code END)'), '=', $division_code);
        }
        // 車種コード
        if (trim($car_model_code) != '') {
            $stmt->where('tc.car_model_code', '=', $car_model_code);
        }
        // 配送区分
        if (trim($delivery_category) != '' && trim($delivery_category) != '0') {
            $stmt->where('t.delivery_category', '=', $delivery_category);
        }
        
        // グループ化
        $stmt->group_by('m.aggregation_tonnage')
            ->group_by(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('m.aggregation_tonnage', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(tc.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計、分載集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['car_model_name'].$dispatch_charter['stack_date']] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $index = $carrying_charter['car_model_name'].$carrying_charter['stack_date'];
            if (array_key_exists($index, $merge_list)) {
                //既に車種名と日付の組み合わせが存在する場合は台数およびトン数を加算
                $merge_list[$index]['mycar_count'] += $carrying_charter['mycar_count'];
                $merge_list[$index]['carrier_count'] += $carrying_charter['carrier_count'];
                $merge_list[$index]['mycar_tonnage'] += $carrying_charter['mycar_tonnage'];
                $merge_list[$index]['carrier_tonnage'] += $carrying_charter['carrier_tonnage'];
            } else {
                $merge_list[$index] = $carrying_charter;
            }
        }
        
        //月単位、年単位の場合は売上補正集計も結合
        if ($conditions['aggregation_unit_date'] != "1") {
            //売上補正集計データ取得
            $sales_correction_list = self::getSalesCorrectionList($conditions);
            
            foreach ($sales_correction_list as $sales_correction) {
                $index = $sales_correction['car_model_name'].$sales_correction['stack_date'];
                if (array_key_exists($index, $merge_list)) {
                    //既に車種名と日付の組み合わせが存在する場合は台数およびトン数を加算
                    $merge_list[$index]['mycar_count'] += $sales_correction['mycar_count'];
                    $merge_list[$index]['carrier_count'] += $sales_correction['carrier_count'];
                    $merge_list[$index]['mycar_tonnage'] += $sales_correction['mycar_tonnage'];
                    $merge_list[$index]['carrier_tonnage'] += $sales_correction['carrier_tonnage'];
                } else {
                    $merge_list[$index] = $sales_correction;
                }
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
     * 車種別配車集計（共配便）
     */
    public static function getdispatchShareList($conditions) {
        
        //日付フォーマット設定
        $date_format = self::getDateFormat($conditions);
        
        //課コード
        $division_code = $conditions['division'];
        //車種コード
        $car_model_code = $conditions['car_model_code'];
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
        
        //集計元データ取得SQL
        $from_sql = '(SELECT '.$date_case.' AS stack_date, t.car_model_code, m.aggregation_tonnage AS car_model_name, m.freight_tonnage, t.carrier_code, t.car_code, mc.company_section';
        $from_sql .= ' FROM t_dispatch_share t INNER JOIN m_car_model m';
        $from_sql .= ' ON t.car_model_code = m.car_model_code';
        $from_sql .= ' AND m.start_date <= t.update_datetime';
        $from_sql .= ' AND m.end_date > t.update_datetime';
        $from_sql .= ' INNER JOIN m_carrier mc';
        $from_sql .= ' ON t.carrier_code = mc.carrier_code';
        $from_sql .= ' AND mc.start_date <= t.update_datetime';
        $from_sql .= ' AND mc.end_date > t.update_datetime';
        $from_sql .= ' LEFT JOIN m_division md';
        $from_sql .= ' ON md.carrier_code = t.carrier_code';
        //削除フラグ
        $from_sql .= ' WHERE t.delete_flag = 0';
        // 集計開始日
        $from_sql .= ' AND '.$date_case.' >= \''.$start_date.'\'';
        // 集計終了日
        $from_sql .= ' AND '.$date_case.' < \''.$end_date.'\'';
        // 課コード
        if (trim($division_code) != '' && trim($division_code) != '000') {
            $from_sql .= ' AND (CASE WHEN mc.company_section = 1 THEN md.division_code ELSE t.division_code END) = '.$division_code;
        }
        // 車種コード
        if (trim($car_model_code) != '') {
            $from_sql .= ' AND t.car_model_code = '.$car_model_code;
        }
        //グルーピング
        $from_sql .= ' GROUP BY '.$date_case.', t.car_model_code, t.carrier_code, t.car_code)';
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('td.car_model_name', 'car_model_name'),
                array(\DB::expr('DATE_FORMAT(td.stack_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(CASE WHEN td.company_section = 1 THEN 1 ELSE 0 END)'), 'mycar_count'),
                array(\DB::expr('SUM(CASE WHEN td.company_section = 2 THEN 1 ELSE 0 END)'), 'carrier_count'),
                array(\DB::expr('SUM(CASE WHEN td.company_section = 1 THEN td.freight_tonnage ELSE 0 END)'), 'mycar_tonnage'),
                array(\DB::expr('SUM(CASE WHEN td.company_section = 2 THEN td.freight_tonnage ELSE 0 END)'), 'carrier_tonnage')
                );
        
        // テーブル
        $stmt->from(array(\DB::expr($from_sql), 'td'));
        
        // グループ化
        $stmt->group_by('td.car_model_name')
            ->group_by(\DB::expr('DATE_FORMAT(td.stack_date, \''.$date_format.'\')'));
        
        // ソート
        $stmt->order_by('td.car_model_name', 'ASC')
            ->order_by(\DB::expr('DATE_FORMAT(td.stack_date, \''.$date_format.'\')'), 'ASC');
        
        // 検索実行
        $merge_list = $stmt->execute(self::$db)->as_array();
        
        //月単位、年単位の場合は売上補正集計を結合
        if ($conditions['aggregation_unit_date'] != "1") {
            //売上補正集計データ取得
            $sales_correction_list = self::getSalesCorrectionList($conditions);
            
            foreach ($sales_correction_list as $sales_correction) {
                $index = $sales_correction['car_model_name'].$sales_correction['stack_date'];
                if (array_key_exists($index, $merge_list)) {
                    //既に車種名と日付の組み合わせが存在する場合は台数およびトン数を加算
                    $merge_list[$index]['mycar_count'] += $sales_correction['mycar_count'];
                    $merge_list[$index]['carrier_count'] += $sales_correction['carrier_count'];
                    $merge_list[$index]['mycar_tonnage'] += $sales_correction['mycar_tonnage'];
                    $merge_list[$index]['carrier_tonnage'] += $sales_correction['carrier_tonnage'];
                } else {
                    $merge_list[$index] = $sales_correction;
                }
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
     * 車種別売上補正売上集計
     */
    public static function getSalesCorrectionList($conditions) {
        
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
                array('m.aggregation_tonnage', 'car_model_name'),
                array('t.sales_category_code', 'sales_category_code'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN t.operation_count ELSE 0 END)'), 'mycar_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN t.operation_count ELSE 0 END)'), 'carrier_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN t.operation_count * m.freight_tonnage ELSE 0 END)'), 'mycar_tonnage'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN t.operation_count * m.freight_tonnage ELSE 0 END)'), 'carrier_tonnage')
                );
        } else {
            $stmt = \DB::select(
                array('m.aggregation_tonnage', 'car_model_name'),
                array(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'stack_date'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN t.operation_count ELSE 0 END)'), 'mycar_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN t.operation_count ELSE 0 END)'), 'carrier_count'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 1 THEN t.operation_count * m.freight_tonnage ELSE 0 END)'), 'mycar_tonnage'),
                array(\DB::expr('SUM(CASE WHEN mc.company_section = 2 THEN t.operation_count * m.freight_tonnage ELSE 0 END)'), 'carrier_tonnage')
                );
        }
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_car_model', 'm'), 'inner')
                ->on('t.car_model_code', '=', 'm.car_model_code')
                ->on('m.start_date', '<=', 't.update_datetime')
                ->on('m.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mc'), 'inner')
                ->on('t.carrier_code', '=', 'mc.carrier_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('md.carrier_code', '=', 't.carrier_code');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 売上区分コード
        //$stmt->where('t.sales_category_code', '=', '1');
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $end_date);
        // 車種コード
        if (trim($conditions['car_model_code']) != '') {
            $stmt->where('t.car_model_code', '=', $conditions['car_model_code']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $stmt->where(\DB::expr('(CASE WHEN mc.company_section = 1 THEN md.division_code ELSE t.division_code END)'), '=', $conditions['division']);
        }
        // 配送区分
        if (trim($conditions['delivery_category']) != '' && trim($conditions['delivery_category']) != '0') {
            $stmt->where('t.delivery_category', '=', $conditions['delivery_category']);
        }
        
        if ($conditions['aggregation_unit_date'] == "1") {
            // グループ化
            $stmt->group_by('m.aggregation_tonnage')
                ->group_by('t.sales_category_code');

            // ソート
            $stmt->order_by('m.aggregation_tonnage', 'ASC')
                ->order_by('t.sales_category_code', 'ASC');
        } else {
            // グループ化
            $stmt->group_by('m.aggregation_tonnage')
                ->group_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'));
            // ソート
            $stmt->order_by('m.aggregation_tonnage', 'ASC')
                ->order_by(\DB::expr('DATE_FORMAT(t.sales_date, \''.$date_format.'\')'), 'ASC');
        }
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
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
            'car_radio',
        	'car_model_code',
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
        if ($cond = \Session::get('t0050_list', array())) {
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
                        'car_model_code' => $conditions['car_model_code'],
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
        
        $filename = "【".$division_name."】取扱いトン数集計表（".date($date_format,  strtotime($conditions['start_date']))."～".date($date_format,  strtotime($conditions['end_date']))."）";
        return $filename;
        
    }

    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        if ($conditions['aggregation_unit_date'] == "1") {
            T0051::createExcelDay();
        } elseif ($conditions['aggregation_unit_date'] == "2") {
            T0051::createExcelMonth();
        } elseif ($conditions['aggregation_unit_date'] == "3") {
            T0051::createExcelYear();
        }
    }
}