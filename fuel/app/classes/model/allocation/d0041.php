<?php
namespace Model\Allocation;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Allocation\D0040;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class D0041 extends \Model {

    public static $db       = 'ONISHI';
    
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $filename = "配車表（チャーター便）";
        return $filename;
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel($conditions) {
        
        $tpl_dir = DOCROOT.'assets/template/';
        $tmp_dir = APPPATH."tmp/";
        $name = self::getExcelName().".xlsx";
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template配車表（チャーター便）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('配車表');
        
        //ヘッダー出力
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division_code']];
        
        $header_date = '';
        if ($conditions['from_stack_date'] != '' && $conditions['to_stack_date'] != '') {
            if ($conditions['from_stack_date'] == $conditions['to_stack_date']) {
                $header_date = date('Y/m/d', strtotime($conditions['from_stack_date']));
            } else {
                $header_date = date('Y/m/d', strtotime($conditions['from_stack_date'])).'～'.date('Y/m/d', strtotime($conditions['to_stack_date']));
            }
        } elseif ($conditions['from_stack_date'] != '') {
            $header_date = date('Y/m/d', strtotime($conditions['from_stack_date'])).'～';
        } elseif ($conditions['to_stack_date'] != '') {
            $header_date = '～'.date('Y/m/d', strtotime($conditions['to_stack_date']));
        }
        $worksheet->getHeaderFooter()->setOddHeader('&L'.$division_name.' &R'.$header_date);
        
        //配車集計データ取得
        $dispatch_list = D0040::getSearch('search', $conditions, 0, 100, self::$db);
        
        //座標シフト値の配列作成
        $shift_x = array('stack_date' => 0,
                        'drop_date' => 1,
                        'client_name' => 2,
                        'carrier_name' => 3,
                        'claim_sales' => 7,
                        'carrier_payment' => 8,
                        'remarks' => 9,
                        'stack_place' => 0,
                        'drop_place' => 1,
                        'product_name' => 2,
                        'car_model_name' => 3,
                        'car_number' => 4,
                        'driver_name' => 5,
                        'phone_number' => 6);
        
        $shift_y = array('stack_date' => 0,
                        'drop_date' => 0,
                        'client_name' => 0,
                        'carrier_name' => 0,
                        'claim_sales' => 0,
                        'carrier_payment' => 0,
                        'remarks' => 0,
                        'stack_place' => 1,
                        'drop_place' => 1,
                        'product_name' => 1,
                        'car_model_name' => 1,
                        'car_number' => 1,
                        'driver_name' => 1,
                        'phone_number' => 1);
        
        //配車集計データ出力
        $current_x = 3;
        $current_y = 5;
        foreach ($dispatch_list as $dispatch) {
            
            if ($dispatch['carrying_count'] > 0) {
                //分載ありの場合
                $carrying_list = D0040::getCarryingCharter(null, $dispatch['dispatch_number'], self::$db);
                
                foreach ($carrying_list as $carrying) {
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['stack_date'], $current_y + $shift_y['stack_date'], date('Y/m/d', strtotime($carrying['stack_date'])));
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['drop_date'], $current_y + $shift_y['drop_date'], date('Y/m/d', strtotime($carrying['drop_date'])));
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['stack_place'], $current_y + $shift_y['stack_place'], $carrying['stack_place']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['drop_place'], $current_y + $shift_y['drop_place'], $carrying['drop_place']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['client_name'], $current_y + $shift_y['client_name'], $carrying['client_name']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['remarks'], $current_y + $shift_y['remarks'], $dispatch['remarks']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['product_name'], $current_y + $shift_y['product_name'], $dispatch['product_name']);
                    
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['carrier_name'], $current_y + $shift_y['carrier_name'], $carrying['carrier_name']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['car_model_name'], $current_y + $shift_y['car_model_name'], $carrying['car_model_name']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['car_number'], $current_y + $shift_y['car_number'], sprintf('%04d', $carrying['car_code']));
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['driver_name'], $current_y + $shift_y['driver_name'], $carrying['driver_name']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['phone_number'], $current_y + $shift_y['phone_number'], $carrying['phone_number']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['claim_sales'], $current_y + $shift_y['claim_sales'], $carrying['claim_sales']);
                    $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['carrier_payment'], $current_y + $shift_y['carrier_payment'], $carrying['carrier_payment']);
                    
                    $current_y += 2;
                }
            } else {
                //分載なしの場合
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['stack_date'], $current_y + $shift_y['stack_date'], date('Y/m/d', strtotime($dispatch['stack_date'])));
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['drop_date'], $current_y + $shift_y['drop_date'], date('Y/m/d', strtotime($dispatch['drop_date'])));
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['stack_place'], $current_y + $shift_y['stack_place'], $dispatch['stack_place']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['drop_place'], $current_y + $shift_y['drop_place'], $dispatch['drop_place']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['client_name'], $current_y + $shift_y['client_name'], $dispatch['client_name']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['remarks'], $current_y + $shift_y['remarks'], $dispatch['remarks']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['product_name'], $current_y + $shift_y['product_name'], $dispatch['product_name']);
                    
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['carrier_name'], $current_y + $shift_y['carrier_name'], $dispatch['carrier_name']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['car_model_name'], $current_y + $shift_y['car_model_name'], $dispatch['car_model_name']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['car_number'], $current_y + $shift_y['car_number'], sprintf('%04d', $dispatch['car_code']));
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['driver_name'], $current_y + $shift_y['driver_name'], $dispatch['driver_name']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['phone_number'], $current_y + $shift_y['phone_number'], $dispatch['phone_number']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['claim_sales'], $current_y + $shift_y['claim_sales'], $dispatch['claim_sales']);
                $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['carrier_payment'], $current_y + $shift_y['carrier_payment'], $dispatch['carrier_payment']);
                
                $current_y += 2;
            }
        }
                
        //不要行の非表示化
        $row_start = $current_y;
        for ($i = $row_start; $i <= 204; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //改ページ設定
        for ($i = 36; $i <= $current_y; $i += 32) {
            $worksheet->setBreak("A".$i, Worksheet::BREAK_ROW);
        }        
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('DI0015', \Config::get('m_DI0015'), '', self::$db);
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
        $fileName = '配車表（チャーター便）.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
        
    }
    
}