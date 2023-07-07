<?php
namespace Model\Printing;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\OpeLog;
use \Model\Common\CommonSql;
use \Model\Common\closingdate;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class T0082 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 出力対象得意先取得
     */
    public static function getClientList($conditions) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $client_code = $conditions['client_code'];
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['target_date'].'-01'.' -1 months'));
        //集計終了日
        $end_date = date('Y-m-d', strtotime($conditions['target_date'].'-01'.' +1 months'));
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $date_case = '(CASE WHEN t.drop_appropriation = 2 THEN t.drop_date ELSE '
                . '(CASE WHEN m.criterion_closing_date = 1 THEN t.stack_date ELSE t.drop_date END) END)';
        $stmt->where(\DB::expr($date_case), '>=', $start_date);
        // 集計終了日
        $stmt->where(\DB::expr($date_case), '<', $end_date);
        
        // グループ化
        $stmt->group_by('m.client_code');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3')
                );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number')
            ->join(array('m_client', 'm'), 'inner')
                ->on('tc.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where(\DB::expr('(CASE WHEN m.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '>=', $start_date);
        // 集計終了日
        $stmt->where(\DB::expr('(CASE WHEN m.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '<', $end_date);
        
        // グループ化
        $stmt->group_by('m.client_code');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //売上補正集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3')
                );
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $end_date);
        
        // グループ化
        $stmt->group_by('m.client_code');
        
        // 検索実行
        $sales_correction_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計、分載集計、売上補正集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['client_code']] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $merge_list[$carrying_charter['client_code']] = $carrying_charter;
        }
        foreach ($sales_correction_list as $sales_correction) {
            $merge_list[$sales_correction['client_code']] = $sales_correction;
        }
        
        //ソートキー作成
        $sort_key = array();
        foreach ($merge_list as $merge) {
            $sort_key[] = $merge['client_name'];
        }
        
        //得意先名でソート
        array_multisort($sort_key, SORT_ASC, $merge_list);
        
        //配列の添え字を連番に変更
        $result_list = array();
        foreach ($merge_list as $merge) {
            $result_list[] = $merge;
        }
        
        return $result_list;
    }
    
    /**
     * 売上請求予定明細データ取得
     */
    public static function getDetailData($division_code, $client_code, $from_date, $to_date) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('m_car_model.car_model_name'), 'car_model_name'),
                array('t.car_code', 'car_number'),
                array(\DB::expr('CASE WHEN t.tax_category = 1 THEN t.claim_sales ELSE 0 END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN t.tax_category = 2 THEN t.claim_sales ELSE 0 END'), 'claim_sales_tax_free'),
                array(\DB::expr('CASE WHEN t.claim_highway_claim = 2 THEN t.claim_highway_fee ELSE 0 END'), 'highway_fee'),
                array('t.remarks', 'remarks'),
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_client', 'm_client'), 'inner')
                ->on('m_client.client_code', '=', 't.client_code')
                ->on('m_client.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_client.end_date', '>', '"'.date("Y-m-d").'"')
            ->join(array('m_car_model', 'm_car_model'), 'inner')
                ->on('m_car_model.car_model_code', '=', 't.car_model_code')
                ->on('m_car_model.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_car_model.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        // 集計開始日
        $date_case = '(CASE WHEN t.drop_appropriation = 2 THEN t.drop_date ELSE '
                . '(CASE WHEN m_client.criterion_closing_date = 1 THEN t.stack_date ELSE t.drop_date END) END)';
        $stmt->where(\DB::expr($date_case), '>=', $from_date);
        // 集計終了日
        $stmt->where(\DB::expr($date_case), '<', $to_date);
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('tc.dispatch_number', 'dispatch_number'),
                array('tc.carrying_number', 'carrying_number'),
                array('tc.stack_date', 'stack_date'),
                array('tc.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('m_car_model.car_model_name'), 'car_model_name'),
                array('tc.car_code', 'car_number'),
                array(\DB::expr('CASE WHEN t.tax_category = 1 THEN tc.claim_sales ELSE 0 END'), 'claim_sales'),
                array(\DB::expr('CASE WHEN t.tax_category = 2 THEN tc.claim_sales ELSE 0 END'), 'claim_sales_tax_free'),
                array(\DB::expr('CASE WHEN tc.claim_highway_claim = 2 THEN tc.claim_highway_fee ELSE 0 END'), 'highway_fee'),
                array('t.remarks', 'remarks'),
                );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number')
            ->join(array('m_client', 'm_client'), 'inner')
                ->on('m_client.client_code', '=', 'tc.client_code')
                ->on('m_client.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_client.end_date', '>', '"'.date("Y-m-d").'"')
            ->join(array('m_car_model', 'm_car_model'), 'inner')
                ->on('m_car_model.car_model_code', '=', 'tc.car_model_code')
                ->on('m_car_model.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_car_model.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        $stmt->where('tc.client_code', '=', $client_code);
        // 集計開始日
        $stmt->where(\DB::expr('(CASE WHEN m_client.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '>=', $from_date);
        // 集計終了日
        $stmt->where(\DB::expr('(CASE WHEN m_client.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '<', $to_date);
        // ソート
        $stmt->order_by('tc.dispatch_number', 'ASC')->order_by('tc.carrying_number', 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        // レコード集約
        $carrying_charter_list = self::aggregateCarryingCharter($carrying_charter_list);
        
        //売上補正集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('t.sales_date', 'stack_date'),
                array(\DB::expr('null'), 'drop_date'),
                array('m_sales_category.sales_category_name', 'stack_place'),
                array(\DB::expr('null'), 'drop_place'),
                array('m_car_model.car_model_name', 'car_model_name'),
                array('m_car.car_code', 'car_number'),
                array('t.sales', 'claim_sales'),
                array(\DB::expr('null'), 'claim_sales_tax_free'),
                array(\DB::expr('CASE WHEN t.highway_fee_claim = 2 THEN t.highway_fee ELSE 0 END'), 'highway_fee'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks'),
                );
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_sales_category', 'm_sales_category'), 'inner')
                ->on('m_sales_category.sales_category_code', '=', 't.sales_category_code')
            ->join(array('m_member', 'm_member'), 'left')
                ->on('m_member.member_code', '=', 't.member_code')
                ->on('m_member.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_member.end_date', '>', '"'.date("Y-m-d").'"')
            ->join(array('m_car', 'm_car'), 'left')
                ->on('m_car.car_code', '=', 'm_member.car_code')
                ->on('m_car.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_car.end_date', '>', '"'.date("Y-m-d").'"')
            ->join(array('m_car_model', 'm_car_model'), 'left')
                ->on('m_car_model.car_model_code', '=', 'm_car.car_model_code')
                ->on('m_car_model.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_car_model.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $from_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $to_date);
        
        // 検索実行
        $sales_correction_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計、分載集計、売上補正集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
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
            'client_radio',
        	'client_code',
            'target_date',
            'target_date_day',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0082_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $result = array('division' => $conditions['division'],
                        'client_code' => $conditions['client_code'],
                        'target_date' => $conditions['target_date'],
                        'target_date_day' => $conditions['target_date_day']);
        
        return $result;
    }
    
    /**
     * 分載のレコード集約
     * （同一配車番号のレコードは１レコードに集約する）
     */
    public static function aggregateCarryingCharter($carrying_charter_list) {
        $result_list = array();
        $dispatch_number = 0;
        $dispatch_number_old = 0;
        $index = -1;
        
        //分載レコードループ
        foreach ($carrying_charter_list as $carrying_charter) {
            $dispatch_number = $carrying_charter['dispatch_number'];
            if ($dispatch_number != $dispatch_number_old) {
                $index++;
                $record = array(
                    'stack_date'			=> $carrying_charter['stack_date'],
                    'drop_date'				=> $carrying_charter['drop_date'],
                    'stack_place'			=> $carrying_charter['stack_place'],
                    'drop_place'			=> $carrying_charter['drop_place'],
                    'car_model_name'		=> $carrying_charter['car_model_name'],
                    'car_number'			=> $carrying_charter['car_number'],
                    'claim_sales'			=> $carrying_charter['claim_sales'],
                    'claim_sales_tax_free'	=> $carrying_charter['claim_sales_tax_free'],
                    'highway_fee'			=> $carrying_charter['highway_fee'],
                    'remarks'               => $carrying_charter['remarks'],
                );
                $result_list[$index] = $record;
            } else {
                $result_list[$index]['claim_sales'] += $carrying_charter['claim_sales'];
                $result_list[$index]['claim_sales_tax_free'] += $carrying_charter['claim_sales_tax_free'];
                $result_list[$index]['highway_fee'] += $carrying_charter['highway_fee'];
            }
            $dispatch_number_old = $dispatch_number;
        }
        
        return $result_list;
    }
        
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $conditions = self::getConditions();
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        
        $filename = "【".$division_name."】請求明細書（".date('Ym',  strtotime($conditions['target_date']."-01"))."）";
        return $filename;
        
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        $tpl_dir = DOCROOT.'assets/template/';
        
        //得意先リスト取得
        $client_list = self::getClientList($conditions);
        $client_count = 0;
        if (is_countable($client_list)){
            $client_count = count($client_list);
        }
        
        //得意先リストが0件なら処理中断
        if ($client_count == 0)return \Config::get('m_CI0004');
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template請求明細書.xlsx');
        
        //課名出力
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        $worksheet = $spreadsheet->getSheetByName('売上請求');
        $worksheet->setCellValue('K4', '担当：'.$division_name);
        
        //シート複製
        for ($i = 0; $i < $client_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('売上請求');
            $CloneSheet->setTitle($client_list[$i]['client_name'].sprintf('%05d', $client_list[$i]['client_code']));
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('売上請求'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //共通項目シート出力
        $worksheet = $spreadsheet->getSheetByName('共通項目');
        self::outputCommon($worksheet, $conditions['division'], $conditions['target_date']);
        
        //明細出力ループ
        foreach ($client_list as $client) {
            $worksheet = $spreadsheet->getSheetByName($client['client_name'].sprintf('%05d', $client['client_code']));
            if (self::outputDetail($worksheet, $conditions['division'], $conditions['target_date'], $conditions['target_date_day'], $client)) {
                //出力したシートをアクティブに
                $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
            } else {
                //出力レコードがない場合はシート削除
                $sel_index = $spreadsheet->getIndex($worksheet);
                $spreadsheet->removeSheetByIndex($sel_index);
            }
        }
        
        //シートが1件なら処理中断（共通シートのみで実質出力なしのため）
        if ($spreadsheet->getSheetCount() == 1)return \Config::get('m_CI0004');
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0022', \Config::get('m_TI0022'), '', self::$db);
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
     * 共通項目シート出力
     */
    public static function outputCommon($worksheet, $division_code, $target_date) {
        
        //$company_data = CommonSql::getCompanyData($division_code, self::$db);
        
        //日付
        $worksheet->setCellValue('B2', date('Y/m',  strtotime($target_date."-01")));
//        //郵便番号
//        $worksheet->setCellValue('B3', $company_data['postal_code']);
//        //住所
//        $worksheet->setCellValue('B4', $company_data['address']);
//        //専用回線
//        $worksheet->setCellValue('B5', $company_data['private_line_number']);
//        //FAX
//        $worksheet->setCellValue('B6', $company_data['fax_number']);
//        //携帯電話
//        $worksheet->setCellValue('B7', $company_data['mobile_phone_number']);
//        //担当
//        $worksheet->setCellValue('B8', $company_data['person_in_charge']);
    }
    
    /**
     * 明細シート出力
     */
    public static function outputDetail($worksheet, $division_code, $target_date, $target_day, $client_data) {
        
        //集計開始日と集計終了日の計算
        $closing_date = closingdate::getFromToDate($target_date, $target_day, $client_data);
        $from_date = $closing_date['from_date'];
        $to_date = $closing_date['to_date'];
        $bill_date = date('Y-m-d',  strtotime($to_date.' -1 day'));
        
        $worksheet->setCellValue('L1', $from_date." - ".$to_date);
        
        //請求年月出力
        $worksheet->setCellValue('K3', date('Y/m/d',  strtotime($bill_date)));
        
        //出力データ取得
        $detail_data = self::getDetailData($division_code, $client_data['client_code'], $from_date, $to_date);
        $detail_count = 0;
        if (is_countable($detail_data)){
            $detail_count = count($detail_data);
        }
        
        //出力データが0件なら処理中断
        if ($detail_count == 0)return false;
        
        //帳票ヘッダ項目出力
        $worksheet->setCellValue('A3', $client_data['official_name']);
        
        //明細出力
        $row = 7;
        foreach ($detail_data as $detail) {
            //積日
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['stack_date'])));
            //卸日
            if (!empty($detail['drop_date'])) {
                $worksheet->setCellValue('B'.$row, date('m/d',  strtotime($detail['drop_date'])));
            }
            //積地
            $worksheet->setCellValue('C'.$row, $detail['stack_place']);
            //卸地
            $worksheet->setCellValue('D'.$row, $detail['drop_place']);
            //車種
            $worksheet->setCellValue('E'.$row, $detail['car_model_name']);
            //車番
            if (!empty($detail['car_number'])) {
                $worksheet->setCellValue('F'.$row, sprintf('%04d', $detail['car_number']));
            }
            //運賃
            $worksheet->setCellValue('G'.$row, $detail['claim_sales']);
            //免税運賃
            $worksheet->setCellValue('H'.$row, $detail['claim_sales_tax_free']);
            //高速代等
            $worksheet->setCellValue('I'.$row, $detail['highway_fee']);
            //備考
            $worksheet->setCellValue('J'.$row, $detail['remarks']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        
        if ($row < 62) {
            $row_start = 61;
        } elseif ($row < 109) {
            $row_start = 108;
        } elseif ($row < 156) {
            $row_start = 155;
        } elseif ($row < 203) {
            $row_start = 202;
        }
        
        for ($i = $row_start; $i <= 239; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //改ページ設定
        if ($row > 61)$worksheet->setBreak("A60", Worksheet::BREAK_ROW);
        if ($row > 108)$worksheet->setBreak("A107", Worksheet::BREAK_ROW);
        if ($row > 155)$worksheet->setBreak("A154", Worksheet::BREAK_ROW);
        if ($row > 202)$worksheet->setBreak("A201", Worksheet::BREAK_ROW);
        
        return true;
    }
}