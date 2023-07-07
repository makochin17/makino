<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;

ini_set("memory_limit", "1000M");

class T0060 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * ドライバーリスト取得
     */
    public static function getDriverList($conditions) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        $stmt = \DB::select(
                array('mm.member_code', 'member_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'member_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('mcm.car_model_name', 'car_model_name'),
                array('mcm.tonnage', 'tonnage')
                );
        
        // テーブル
        $stmt->from(array('m_member', 'mm'))
            ->join(array('m_car', 'mc'), 'inner')
                ->on('mm.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_car_model', 'mcm'), 'inner')
                ->on('mcm.car_model_code', '=', 'mc.car_model_code')
                ->on('mcm.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcm.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 社員コード
        if (trim($conditions['member_code']) != '') {
            $stmt->where('mm.member_code', '=', $conditions['member_code']);
        }
        // 課コード
        $stmt->where('mm.division_code', '=', $conditions['division']);
        // 車両コードコード
        $stmt->where('mm.car_code', '!=', null);
        // 適用開始日
        $stmt->where('mm.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mm.end_date', '>', date("Y-m-d"));
        
        // ソート
        $stmt->order_by(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'ASC');
        
        // 検索実行
        return $stmt->execute(self::$db)->as_array();
        
    }
    
    /**
     * 社員運賃集計
     */
    public static function getMemberFareList($conditions, $member_code) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['start_date']));
        //集計終了日
        $end_date = date('Y-m-d', strtotime($conditions['end_date'].'+1 day'));
        
        //配車集計（往復なし）-------------------------------------------------------------
        $stmt = \DB::select(
                array('td.stack_date', 'stack_date'),
                array('td.drop_date', 'drop_date'),
                array('m.client_name', 'client_name'),
                array('td.delivery_category', 'delivery_category'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN td.claim_sales ELSE td.claim_sales - td.driver_highway_fee END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN td.driver_highway_fee ELSE 0 END'), 'highway_fee'),
                array('td.allowance', 'allowance'),
                array('td.stay', 'stay'),
                array('td.linking_wrap', 'linking_wrap')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 'td'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('td.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 'td.update_datetime')
                ->on('m.end_date', '>', 'td.update_datetime');
        
        // 削除フラグ
        $stmt->where('td.delete_flag', '=', '0');
        // 分載
        $stmt->where('td.carrying_count', '=', '0');
        // 往復
        $stmt->where('td.round_trip', '=', '1');
        // 社員コード
        $stmt->where('td.member_code', '=', $member_code);
        // 集計開始日、集計終了日
        $stmt->where('td.stack_date', 'between', array($start_date, $end_date));

        // ソート
        $stmt->order_by('td.stack_date', 'ASC')
            ->order_by('td.drop_date', 'ASC');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計（往復あり　積日）-------------------------------------------------------------
        $stmt = \DB::select(
                array('td.stack_date', 'stack_date'),
                array('td.stack_date', 'drop_date'),
                array('m.client_name', 'client_name'),
                array('td.delivery_category', 'delivery_category'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN '
                        . 'CASE WHEN td.claim_sales != 0 THEN td.claim_sales / 2 ELSE 0 END ELSE '
                        . 'CASE WHEN (td.claim_sales - td.driver_highway_fee) != 0 THEN (td.claim_sales - td.driver_highway_fee) / 2 ELSE 0 END END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN '
                        . 'CASE WHEN td.driver_highway_fee != 0 THEN td.driver_highway_fee / 2 ELSE 0 END ELSE 0 END'), 'highway_fee'),
                array(\DB::expr('CASE WHEN td.allowance != 0 THEN td.allowance / 2 ELSE 0 END'), 'allowance'),
                array(\DB::expr('CASE WHEN td.stay != 0 THEN td.stay / 2 ELSE 0 END'), 'stay'),
                array(\DB::expr('CASE WHEN td.linking_wrap != 0 THEN td.linking_wrap / 2 ELSE 0 END'), 'linking_wrap')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 'td'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('td.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 'td.update_datetime')
                ->on('m.end_date', '>', 'td.update_datetime');
        
        // 削除フラグ
        $stmt->where('td.delete_flag', '=', '0');
        // 分載
        $stmt->where('td.carrying_count', '=', '0');
        // 往復
        $stmt->where('td.round_trip', '=', '2');
        // 社員コード
        $stmt->where('td.member_code', '=', $member_code);
        // 集計開始日、集計終了日
        $stmt->where('td.stack_date', 'between', array($start_date, $end_date));

        // ソート
        $stmt->order_by('td.stack_date', 'ASC');
        
        // 検索実行
        $dispatch_charter_stack_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計（往復あり　降日）-------------------------------------------------------------
        $stmt = \DB::select(
                array('td.drop_date', 'stack_date'),
                array('td.drop_date', 'drop_date'),
                array('m.client_name', 'client_name'),
                array('td.delivery_category', 'delivery_category'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(td.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN '
                        . 'CASE WHEN td.claim_sales != 0 THEN td.claim_sales / 2 ELSE 0 END ELSE '
                        . 'CASE WHEN (td.claim_sales - td.driver_highway_fee) != 0 THEN (td.claim_sales - td.driver_highway_fee) / 2 ELSE 0 END END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN td.driver_highway_claim = 2 THEN '
                        . 'CASE WHEN td.driver_highway_fee != 0 THEN td.driver_highway_fee / 2 ELSE 0 END ELSE 0 END'), 'highway_fee'),
                array(\DB::expr('CASE WHEN td.allowance != 0 THEN td.allowance / 2 ELSE 0 END'), 'allowance'),
                array(\DB::expr('CASE WHEN td.stay != 0 THEN td.stay / 2 ELSE 0 END'), 'stay'),
                array(\DB::expr('CASE WHEN td.linking_wrap != 0 THEN td.linking_wrap / 2 ELSE 0 END'), 'linking_wrap')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 'td'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('td.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 'td.update_datetime')
                ->on('m.end_date', '>', 'td.update_datetime');
        
        // 削除フラグ
        $stmt->where('td.delete_flag', '=', '0');
        // 分載
        $stmt->where('td.carrying_count', '=', '0');
        // 往復
        $stmt->where('td.round_trip', '=', '2');
        // 社員コード
        $stmt->where('td.member_code', '=', $member_code);
        // 集計開始日、集計終了日
        $stmt->where('td.drop_date', 'between', array($start_date, $end_date));

        // ソート
        $stmt->order_by('td.drop_date', 'ASC');
        
        // 検索実行
        $dispatch_charter_drop_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('tc.stack_date', 'stack_date'),
                array('tc.drop_date', 'drop_date'),
                array('m.client_name', 'client_name'),
                array('t.delivery_category', 'delivery_category'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('CASE WHEN tc.driver_highway_claim = 2 THEN tc.claim_sales ELSE tc.claim_sales - tc.driver_highway_fee END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN tc.driver_highway_claim = 2 THEN tc.driver_highway_fee ELSE 0 END'), 'highway_fee'),
                array('t.allowance', 'allowance'),
                array('t.stay', 'stay'),
                array('t.linking_wrap', 'linking_wrap')
                );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number')
            ->join(array('m_client', 'm'), 'inner')
                ->on('tc.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', 'tc.update_datetime')
                ->on('m.end_date', '>', 'tc.update_datetime');
        
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 社員コード
        $stmt->where('tc.member_code', '=', $member_code);
        // 集計開始日、集計終了日
        $stmt->where('tc.stack_date', 'between', array($start_date, $end_date));

        // ソート
        $stmt->order_by('tc.stack_date', 'ASC')
            ->order_by('tc.drop_date', 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //売上補正集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('t.sales_date', 'stack_date'),
                array(\DB::expr('null'), 'drop_date'),
                array('m_sales_category.sales_category_name', 'client_name'),
                array('t.delivery_category', 'delivery_category'),
                array(\DB::expr('null'), 'stack_place'),
                array(\DB::expr('null'), 'drop_place'),
                array('t.sales', 'claim_sales'),
                array(\DB::expr('0'), 'highway_fee'),
                array(\DB::expr('0'), 'allowance'),
                array(\DB::expr('0'), 'stay'),
                array(\DB::expr('0'), 'linking_wrap')
                );
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_sales_category', 'm_sales_category'), 'inner')
                ->on('m_sales_category.sales_category_code', '=', 't.sales_category_code');
        
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 社員コード
        $stmt->where('t.member_code', '=', $member_code);
        // 集計開始日、集計終了日
        $stmt->where('t.sales_date', 'between', array($start_date, $end_date));

        // ソート
        $stmt->order_by('t.sales_date', 'ASC');
        
        // 検索実行
        $sales_correction_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計、分載集計、売上補正集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
        }
        foreach ($dispatch_charter_stack_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
        }
        foreach ($dispatch_charter_drop_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $merge_list[] = $carrying_charter;
        }
        foreach ($sales_correction_list as $sales_correction) {
            $merge_list[] = $sales_correction;
        }
        
        //ソートキー作成
        $sort_key1 = array();
        $sort_key2 = array();
        foreach ($merge_list as $merge) {
            $sort_key1[] = $merge['stack_date'];
            $sort_key2[] = $merge['drop_date'];
        }
        
        //積日、降日でソート
        array_multisort($sort_key1, SORT_ASC, $sort_key2, SORT_ASC, $merge_list);
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        return $result_list;
        
    }
    
    /**
     * 出力条件取得
     */
    public static function getConditions() {
        $conditions 	= array_fill_keys(array(
        	'division',
            'member_radio',
        	'member_code',
            'start_date',
            'end_date',
            'fare_radio',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0060_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $result = array('division' => $conditions['division'],
                        'member_code' => $conditions['member_code'],
                        'target_month' => date('n',  strtotime($conditions['end_date'])),
                        'start_date' => $conditions['start_date'],
                        'end_date' => $conditions['end_date'],
                        'fare_radio' => $conditions['fare_radio']);
        
        return $result;
    }
    
    /**
     * 集計開始・終了の入力チェック
     */
    public static function checkDate() {
        $conditions = self::getConditions();
        
        //日付相関チェック
        if (strtotime($conditions['start_date']) > strtotime($conditions['end_date'])) {
            return str_replace('XXXXX','集計日付',\Config::get('m_CW0007'));
        }
        
        //日付範囲チェック
        $start_date = new \DateTime($conditions['start_date']);
        $end_date = new \DateTime($conditions['end_date']);
        if ($start_date->diff($end_date)->format('%a') >= 31) {
            return str_replace('XXXXX','３１日',\Config::get('m_TW0001'));
        }
    }
    
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $conditions = self::getConditions();
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        
        $filename = "【".$division_list[$conditions['division']]."】ドライバー別売上集計表（".date('Y年m月',  strtotime($conditions['end_date']))."）";
        return $filename;
        
    }
    
    /**
     * エクセル作成
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        
        $result = null;
        switch ($conditions['division']) {
            case '1': //１課
                $result = T0061::createExcel4();
                break;
            case '2': //２課
                $result = T0061::createExcel2();
                break;
            case '3': //３課
                $result = T0061::createExcel1();
                break;
            case '4': //輸送所
                $result = T0061::createExcel3();
                break;
            default: //その他の課
                $result = T0061::createExcel1();
        }
        
        return $result;
    }
}