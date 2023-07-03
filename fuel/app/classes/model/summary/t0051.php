<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Summary\T0050;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

ini_set("memory_limit", "1000M");

class T0051 extends \Model {

    public static $db       = 'ONISHI';
    
    /**
     * エクセル作成処理（日単位）
     */
    public static function createExcelDay() {
        $conditions = T0050::getConditions();
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template取扱いトン数集計（日単位）.xlsx');
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('稼働台数（表）');
        
        $summary_category = '';
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            $summary_category = 'チャーター便';
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            $summary_category = '共配便';
        }
        
        //-------稼働台数----------------------------------------------------------------------
        //帳票タイトル出力
        $division_list = GenerateList::getDivisionList(true, T0050::$db);
        $division_name = $division_list[$conditions['division']];
        if ($conditions['division'] == '00') {
            $division_name = '全課';
        }
        
        $delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        $delivery_category_name = $delivery_category_list[$conditions['delivery_category']];
        if ($conditions['division'] == '0') {
            $delivery_category_name = '';
        }
        
        $title = "■".$summary_category."-稼働台数（".date('Y年m月d日',  strtotime($conditions['start_date']))."～".date('Y年m月d日',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //日見出し出力
        $col_num_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            $worksheet->setCellValueByColumnAndRow($col_num,2, date('j',  strtotime($i)).'日');
            $col_num_list += array($i => $col_num);
            $col_num += 2;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        for ($i = $col_start; $i <= 63; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //車種リスト取得
        $car_model_list = T0050::getCarModelDateDesignationList($conditions);
        $car_model_count = 0;
        if (is_countable($car_model_list)){
            $car_model_count = count($car_model_list);
        }
        
        //車種見出し出力
        $row_num_list = array();
        $row_num = 4;
        foreach ($car_model_list as $car_model) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, $car_model['car_model_name'].'t');
            $row_num_list += array($car_model['car_model_name']=>$row_num);
            $row_num++;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        $max_row = $worksheet->getHighestRow() - 1;
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchList($conditions);
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchShareList($conditions);
        }
        
        //配車集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_count']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_count']);
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, T0050::$db);
        
        //売上区分見出し出力
        $Sales_col_num_list = array();
        $Sales_col_num = 64;
        foreach ($sales_category_list as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($Sales_col_num, 2, $value);
            $Sales_col_num_list += array($key => $Sales_col_num);
            $Sales_col_num += 2;
        }
        
        //不要列の非表示化
        $Sales_col_start = $Sales_col_num;
        for ($i = $Sales_col_start; $i <= 83; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //売上補正集計データ取得
        $sales_correction_list = T0050::getSalesCorrectionList($conditions);
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $worksheet->setCellValueByColumnAndRow($Sales_col_num_list[$sales_correction['sales_category_code']], $row_num_list[$sales_correction['car_model_name']], $sales_correction['mycar_count']);
            $worksheet->setCellValueByColumnAndRow($Sales_col_num_list[$sales_correction['sales_category_code']] + 1, $row_num_list[$sales_correction['car_model_name']], $sales_correction['carrier_count']);
        }
        
        
        //-------取扱㌧数----------------------------------------------------------------------
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('取扱㌧数（表）');
        
        //帳票タイトル出力
        $title = "■".$summary_category."-取扱㌧数（".date('Y年m月d日',  strtotime($conditions['start_date']))."～".date('Y年m月d日',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //不要列の非表示化
        for ($i = $col_start; $i <= 63; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //不要行の非表示化
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //配車集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_tonnage']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_tonnage']);
        }
        
        //不要列の非表示化
        for ($i = $Sales_col_start; $i <= 83; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $worksheet->setCellValueByColumnAndRow($Sales_col_num_list[$sales_correction['sales_category_code']], $row_num_list[$sales_correction['car_model_name']], $sales_correction['mycar_tonnage']);
            $worksheet->setCellValueByColumnAndRow($Sales_col_num_list[$sales_correction['sales_category_code']] + 1, $row_num_list[$sales_correction['car_model_name']], $sales_correction['carrier_tonnage']);
        }
        
        try {
            \DB::start_transaction(T0050::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0013', \Config::get('m_TI0013'), '', T0050::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0050::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0050::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0050::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * エクセル作成処理（月単位）
     */
    public static function createExcelMonth() {
        $conditions = T0050::getConditions();
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template取扱いトン数集計（月単位）.xlsx');
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('稼働台数（表）');
        
        $summary_category = '';
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            $summary_category = 'チャーター便';
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            $summary_category = '共配便';
        }
        
        //-------稼働台数----------------------------------------------------------------------
        //帳票タイトル出力
        $division_list = GenerateList::getDivisionList(true, T0050::$db);
        $division_name = $division_list[$conditions['division']];
        if ($conditions['division'] == '00') {
            $division_name = '全課';
        }
        
        $delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        $delivery_category_name = $delivery_category_list[$conditions['delivery_category']];
        if ($conditions['division'] == '0') {
            $delivery_category_name = '';
        }
        
        $title = "■".$summary_category."-稼働台数（".date('Y年m月',  strtotime($conditions['start_date']))."～".date('Y年m月',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //月見出し出力
        $col_num_list = array();
        $start = date('Y-m', strtotime($conditions['start_date']));
        $end = date('Y-m', strtotime($conditions['end_date']));
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y-m', strtotime($i . '+1 month'))) {
            $worksheet->setCellValueByColumnAndRow($col_num,2, date('n',  strtotime($i.'-01')).'月');
            $col_num_list += array($i => $col_num);
            $col_num += 2;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        for ($i = $col_start; $i <= 25; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //車種リスト取得
        $car_model_list = T0050::getCarModelDateDesignationList($conditions);
        $car_model_count = 0;
        if (is_countable($car_model_list)){
            $car_model_count = count($car_model_list);
        }
        
        //車種見出し出力
        $row_num_list = array();
        $row_num = 4;
        foreach ($car_model_list as $car_model) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, $car_model['car_model_name'].'t');
            $row_num_list += array($car_model['car_model_name']=>$row_num);
            $row_num++;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        $max_row = $worksheet->getHighestRow() - 1;
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchList($conditions);
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchShareList($conditions);
        }
        
        //配車・売上補正集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_count']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_count']);
        }
        
        //-------取扱㌧数----------------------------------------------------------------------
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('取扱㌧数（表）');
        
        //帳票タイトル出力
        $title = "■".$summary_category."-取扱㌧数（".date('Y年m月',  strtotime($conditions['start_date']))."～".date('Y年m月',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //不要列の非表示化
        for ($i = $col_start; $i <= 25; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //不要行の非表示化
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //配車・売上補正集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_tonnage']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_tonnage']);
        }
        
        try {
            \DB::start_transaction(T0050::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0014', \Config::get('m_TI0014'), '', T0050::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0050::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0050::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0050::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * エクセル作成処理（年単位）
     */
    public static function createExcelYear() {
        $conditions = T0050::getConditions();
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template取扱いトン数集計（年単位）.xlsx');
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('稼働台数（表）');
        
        $summary_category = '';
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            $summary_category = 'チャーター便';
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            $summary_category = '共配便';
        }
        
        //-------稼働台数----------------------------------------------------------------------
        //帳票タイトル出力
        $division_list = GenerateList::getDivisionList(true, T0050::$db);
        $division_name = $division_list[$conditions['division']];
        if ($conditions['division'] == '00') {
            $division_name = '全課';
        }
        
        $delivery_category_list = GenerateList::getDeliveryCategoryList(true);
        $delivery_category_name = $delivery_category_list[$conditions['delivery_category']];
        if ($conditions['division'] == '0') {
            $delivery_category_name = '';
        }
        
        $title = "■".$summary_category."-稼働台数（".date('Y年',  strtotime($conditions['start_date']))."～".date('Y年',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //年見出し出力
        $col_num_list = array();
        $start = date('Y', strtotime($conditions['start_date']));
        $end = date('Y', strtotime($conditions['end_date']));
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y', strtotime($i . '-01-01 +1 year'))) {
            $worksheet->setCellValueByColumnAndRow($col_num,2, date('Y',  strtotime($i.'-01-01')).'年');
            $col_num_list += array($i => $col_num);
            $col_num += 2;
        }
        
        //不要列の非表示化
        $col_start = $col_num;
        for ($i = $col_start; $i <= 25; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //車種リスト取得
        $car_model_list = T0050::getCarModelDateDesignationList($conditions);
        $car_model_count = 0;
        if (is_countable($car_model_list)){
            $car_model_count = count($car_model_list);
        }
        
        //車種見出し出力
        $row_num_list = array();
        $row_num = 4;
        foreach ($car_model_list as $car_model) {
            $worksheet->setCellValueByColumnAndRow(1, $row_num, $car_model['car_model_name'].'t');
            $row_num_list += array($car_model['car_model_name']=>$row_num);
            $row_num++;
        }
        
        //不要行の非表示化
        $row_start = $row_num;
        $max_row = $worksheet->getHighestRow() - 1;
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchList($conditions);
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0050::getdispatchShareList($conditions);
        }
        
        //配車・売上補正集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_count']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_count']);
        }
        
        //-------取扱㌧数----------------------------------------------------------------------
        
        //「稼働台数（表）」シート選択
        $worksheet = $spreadsheet->getSheetByName('取扱㌧数（表）');
        
        //帳票タイトル出力
        $title = "■".$summary_category."-取扱㌧数（".date('Y年',  strtotime($conditions['start_date']))."～".date('Y年',  strtotime($conditions['end_date']))."-".$division_name."）".$delivery_category_name;
        $worksheet->setCellValue('A1', $title);
        
        //不要列の非表示化
        for ($i = $col_start; $i <= 25; $i++) {
            $ColumnName = Coordinate::stringFromColumnIndex($i);
            $worksheet->getColumnDimension($ColumnName)->setVisible(false);
        }
        
        //不要行の非表示化
        for ($i = $row_start; $i <= $max_row; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //配車・売上補正集計データ出力
        foreach ($dispatch_list as $dispatch) {
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']], $row_num_list[$dispatch['car_model_name']], $dispatch['mycar_tonnage']);
            $worksheet->setCellValueByColumnAndRow($col_num_list[$dispatch['stack_date']] + 1, $row_num_list[$dispatch['car_model_name']], $dispatch['carrier_tonnage']);
        }
        
        try {
            \DB::start_transaction(T0050::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0015', \Config::get('m_TI0015'), '', T0050::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0050::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0050::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0050::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
}