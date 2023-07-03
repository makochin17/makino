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

class T0090 extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 出力対象庸車先取得
     */
    public static function getCarrierList($conditions) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $carrier_code = $conditions['carrier_code'];
        //集計開始日
        $start_date = date('Y-m-d', strtotime($conditions['target_date'].'-01'.' -1 months'));
        //集計終了日
        $end_date = date('Y-m-d', strtotime($conditions['target_date'].'-01'.' +1 months'));
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.carrier_name', 'carrier_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_carrier', 'm'), 'inner')
                ->on('t.carrier_code', '=', 'm.carrier_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        //$stmt->where('t.sales_status', '=', '1');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $stmt->where('m.carrier_code', '=', $carrier_code);
        }
        // 適用開始日
        $date_case = '(CASE WHEN t.drop_appropriation = 2 THEN t.drop_date ELSE '
                . '(CASE WHEN m.criterion_closing_date = 1 THEN t.stack_date ELSE t.drop_date END) END)';
        $stmt->where(\DB::expr($date_case), '>=', $start_date);
        // 適用終了日
        $stmt->where(\DB::expr($date_case), '<', $end_date);
        // 金額
        $stmt->where('t.carrier_payment', '>', 0);
        
        // グループ化
        $stmt->group_by('m.carrier_code');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //売上補正集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.carrier_name', 'carrier_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3')
                );
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_carrier', 'm'), 'inner')
                ->on('t.carrier_code', '=', 'm.carrier_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($carrier_code) != '') {
            $stmt->where('m.carrier_code', '=', $carrier_code);
        }
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $start_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $end_date);
        // 金額
        $stmt->where('t.carrier_cost', '>', 0);
        
        // グループ化
        $stmt->group_by('m.carrier_code');
        
        // 検索実行
        $sales_correction_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計と売上補正集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['carrier_code']] = $dispatch_charter;
        }
        foreach ($sales_correction_list as $sales_correction) {
            $merge_list[$sales_correction['carrier_code']] = $sales_correction;
        }
        
        //ソートキー作成
        $sort_key = array();
        foreach ($merge_list as $merge) {
            $sort_key[] = $merge['carrier_name'];
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
     * 庸車支払予定明細データ取得
     */
    public static function getDetailData($division_code, $carrier_code, $from_date, $to_date) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        $stmt = \DB::select(
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array(\DB::expr('m_car_model.car_model_name'), 'car_model_name'),
                array('car_code', 'car_number'),
                array(\DB::expr('CASE WHEN t.tax_category = 1 THEN t.carrier_payment ELSE 0 END'), 'carrier_payment'),
                array(\DB::expr('CASE WHEN t.tax_category = 2 THEN t.carrier_payment ELSE 0 END'), 'carrier_payment_tax_free'),
                array(\DB::expr('CASE WHEN t.carrier_highway_claim = 2 THEN t.carrier_highway_fee ELSE 0 END'), 'highway_fee'),
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_carrier', 'm_carrier'), 'inner')
                ->on('m_carrier.carrier_code', '=', 't.carrier_code')
                ->on('m_carrier.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_carrier.end_date', '>', '"'.date("Y-m-d").'"')
            ->join(array('m_car_model', 'm_car_model'), 'inner')
                ->on('m_car_model.car_model_code', '=', 't.car_model_code')
                ->on('m_car_model.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m_car_model.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        //$stmt->where('t.sales_status', '=', '1');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 庸車先コード
        $stmt->where('t.carrier_code', '=', $carrier_code);
        // 集計開始日
        $date_case = '(CASE WHEN t.drop_appropriation = 2 THEN t.drop_date ELSE '
                . '(CASE WHEN m_carrier.criterion_closing_date = 1 THEN t.stack_date ELSE t.drop_date END) END)';
        $stmt->where(\DB::expr($date_case), '>=', $from_date);
        // 集計終了日
        $stmt->where(\DB::expr($date_case), '<', $to_date);
        // 金額
        $stmt->where('t.carrier_payment', '>', 0);
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //売上補正集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('t.sales_date', 'stack_date'),
                array(\DB::expr('null'), 'drop_date'),
                array('m_sales_category.sales_category_name', 'stack_place'),
                array(\DB::expr('null'), 'drop_place'),
                array(\DB::expr('null'), 'car_model_name'),
                array(\DB::expr('null'), 'car_number'),
                array('t.carrier_cost', 'carrier_payment'),
                array(\DB::expr('null'), 'carrier_payment_tax_free'),
                array(\DB::expr('CASE WHEN t.highway_fee_claim = 2 THEN t.highway_fee ELSE 0 END'), 'highway_fee'),
                );
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'))
            ->join(array('m_sales_category', 'm_sales_category'), 'inner')
                ->on('m_sales_category.sales_category_code', '=', 't.sales_category_code');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 庸車先コード
        $stmt->where('t.carrier_code', '=', $carrier_code);
        // 集計開始日
        $stmt->where('t.sales_date', '>=', $from_date);
        // 集計終了日
        $stmt->where('t.sales_date', '<', $to_date);
        // 金額
        $stmt->where('t.carrier_cost', '>', 0);
        
        // 検索実行
        $sales_correction_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計と売上補正集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
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
            'carrier_radio',
        	'carrier_code',
            'target_date',
            'target_date_day',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0090_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $result = array('division' => $conditions['division'],
                        'carrier_code' => $conditions['carrier_code'],
                        'target_date' => $conditions['target_date'],
                        'target_date_day' => $conditions['target_date_day']);
        
        return $result;
    }
        
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $conditions = self::getConditions();
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        
        $filename = "【".$division_name."】傭車支払予定明細書（".date('Ym',  strtotime($conditions['target_date']."-01"))."）";
        return $filename;
        
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        $tpl_dir = DOCROOT.'assets/template/';
        $tmp_dir = APPPATH."tmp/";
        $name = self::getExcelName().".xlsx";
        
        
        //庸車先リスト取得
        $carrier_list = self::getCarrierList($conditions);
        $carrier_count = 0;
        if (is_countable($carrier_list)){
            $carrier_count = count($carrier_list);
        }
        
        //庸車先リストが0件なら処理中断
        if ($carrier_count == 0)return \Config::get('m_CI0004');
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template傭車支払予定明細書.xlsx');
        
        //携帯電話が空なら非表示にして担当を上に詰める
        $company_data = CommonSql::getCompanyData($conditions['division'], self::$db);
        if (empty($company_data['mobile_phone_number'])) {
            $worksheet = $spreadsheet->getSheetByName('傭車支払');
            $worksheet->setCellValue('G10', '担当');
            $worksheet->setCellValue('H10', '=共通項目!B8');
            $worksheet->setCellValue('G11', '');
            $worksheet->setCellValue('H11', '');
        }
        
        //シート複製
        for ($i = 0; $i < $carrier_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('傭車支払');
            $CloneSheet->setTitle($carrier_list[$i]['carrier_name'].sprintf('%05d', $carrier_list[$i]['carrier_code']));
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('傭車支払'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //共通項目シート出力
        $worksheet = $spreadsheet->getSheetByName('共通項目');
        self::outputCommon($worksheet, $conditions['target_date'], $company_data);
        
        //明細出力ループ
        foreach ($carrier_list as $carrier) {
            $worksheet = $spreadsheet->getSheetByName($carrier['carrier_name'].sprintf('%05d', $carrier['carrier_code']));
            if (self::outputDetail($worksheet, $conditions['division'], $conditions['target_date'], $conditions['target_date_day'], $carrier)) {
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
            $result = OpeLog::addOpeLog('TI0021', \Config::get('m_TI0021'), '', self::$db);
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
    public static function outputCommon($worksheet, $target_date, $company_data) {
        
        //日付
        $worksheet->setCellValue('B2', date('Y/m',  strtotime($target_date."-01")));
        //郵便番号
        $worksheet->setCellValue('B3', $company_data['postal_code']);
        //住所
        $worksheet->setCellValue('B4', $company_data['address']);
        //専用回線
        $worksheet->setCellValue('B5', $company_data['private_line_number']);
        //FAX
        $worksheet->setCellValue('B6', $company_data['fax_number']);
        //携帯電話
        $worksheet->setCellValue('B7', $company_data['mobile_phone_number']);
        //担当
        $worksheet->setCellValue('B8', $company_data['person_in_charge']);
    }
    
    /**
     * 明細出力
     */
    public static function outputDetail($worksheet, $division_code, $target_date, $target_day, $carrier_data) {
        
        //集計開始日と集計終了日の計算
        $closing_date = closingdate::getFromToDate($target_date, $target_day, $carrier_data);
        $from_date = $closing_date['from_date'];
        $to_date = $closing_date['to_date'];
        
        $worksheet->setCellValue('J1', $from_date." - ".$to_date);
        
        //出力データ取得
        $detail_data = self::getDetailData($division_code, $carrier_data['carrier_code'], $from_date, $to_date);
        $detail_count = 0;
        if (is_countable($detail_data)){
            $detail_count = count($detail_data);
        }
        
        //出力データが0件なら処理中断
        if ($detail_count == 0)return false;
        
        //帳票ヘッダ項目出力
        $worksheet->setCellValue('A3', $carrier_data['official_name']);
        
        //明細出力
        $row = 16;
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
            $worksheet->setCellValue('G'.$row, $detail['carrier_payment']);
            //免税運賃
            $worksheet->setCellValue('H'.$row, $detail['carrier_payment_tax_free']);
            //高速代等
            $worksheet->setCellValue('I'.$row, $detail['highway_fee']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        
        if ($row < 43) {
            $row_start = 42;
        } elseif ($row < 90) {
            $row_start = 89;
        } elseif ($row < 137) {
            $row_start = 136;
        } elseif ($row < 184) {
            $row_start = 183;
        }
        
        for ($i = $row_start; $i <= 220; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //改ページ設定
        if ($row > 42)$worksheet->setBreak("A41", Worksheet::BREAK_ROW);
        if ($row > 89)$worksheet->setBreak("A88", Worksheet::BREAK_ROW);
        if ($row > 136)$worksheet->setBreak("A135", Worksheet::BREAK_ROW);
        if ($row > 183)$worksheet->setBreak("A182", Worksheet::BREAK_ROW);
        
        return true;
    }
}