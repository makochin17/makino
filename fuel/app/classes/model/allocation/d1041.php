<?php
namespace Model\Allocation;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Allocation\D1040;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class D1041 extends \Model {

    public static $db       = 'MAKINO';
    
    /**
     * エクセル作成処理
     */
    public static function createExcel($conditions) {
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template配車表（共配便）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('配車表');
        
        //ヘッダー項目出力
        $header_data = self::getHeader($conditions['car_code']);
        if (!empty($header_data)) {
            $worksheet->setCellValueByColumnAndRow(1, 3, $header_data['car_model_name']);
            $worksheet->setCellValueByColumnAndRow(2, 3, sprintf('%04d', $header_data['car_code']));
            $worksheet->setCellValueByColumnAndRow(3, 3, $header_data['driver_name']);
        }
        
        //0件チェック
        $total = D1040::getSearch('count', $conditions, null, null, self::$db);
        if (0 >= $total) {
            return \Config::get('m_CI0004');
        }
        
        //出力レコード件数チェック
        if (\Config::get('d1041_limit') < $total) {
            return str_replace('XXXXX',\Config::get('d1041_limit'),\Config::get('m_DW0016'));
        }
        
        //配車集計データ取得
        $dispatch_list = D1040::getSearch('search', $conditions, 0, $total, self::$db);
        
        //座標シフト値の配列作成
        $shift_x = array('service_date' => 0,
                        'delivery_category' => 1,
                        'area' => 2,
                        'course' => 2,
                        'client_name' => 3,
                        'product_name' => 3,
                        'carrier_name' => 4,
                        'service_place' => 4,
                        'volume' => 5,
                        'unit' => 6,
                        'remarks' => 7);
        
        $shift_y = array('service_date' => 0,
                        'delivery_category' => 0,
                        'area' => 0,
                        'course' => 1,
                        'client_name' => 0,
                        'product_name' => 1,
                        'carrier_name' => 0,
                        'service_place' => 1,
                        'volume' => 0,
                        'unit' => 0,
                        'remarks' => 0);
        
        // 配送区分リスト取得
        $delivery_category_list   = GenerateList::getShareDeliveryCategoryList(true);
        // 地区リスト取得
        $area_list                = GenerateList::getAreaList(true, self::$db);
        // 単位リスト取得
        $unit_list                = GenerateList::getUnitList(true, self::$db);
        
        //配車集計データ出力
        $current_x = 1;
        $current_y = 7;
        foreach ($dispatch_list as $dispatch) {
            //日付、運行先設定
            $service_date = $dispatch['delivery_date'];
            $service_place = $dispatch['delivery_place'];
            if ($dispatch['delivery_code'] == '2' || ($dispatch['delivery_code'] == '3' && empty($dispatch['delivery_date']))) {
                $service_date = $dispatch['pickup_date'];
                $service_place = $dispatch['pickup_place'];
            }
            
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['service_date'], $current_y + $shift_y['service_date'], date('Y/m/d', strtotime($service_date)));
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['delivery_category'], $current_y + $shift_y['delivery_category'], $delivery_category_list[$dispatch['delivery_code']]);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['area'], $current_y + $shift_y['area'], $area_list[$dispatch['area_code']]);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['course'], $current_y + $shift_y['course'], $dispatch['course']);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['client_name'], $current_y + $shift_y['client_name'], $dispatch['client_name']);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['product_name'], $current_y + $shift_y['product_name'], $dispatch['product_name']);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['carrier_name'], $current_y + $shift_y['carrier_name'], $dispatch['carrier_name']);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['service_place'], $current_y + $shift_y['service_place'], $service_place);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['volume'], $current_y + $shift_y['volume'], $dispatch['volume']);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['unit'], $current_y + $shift_y['unit'], $unit_list[$dispatch['unit_code']]);
            $worksheet->setCellValueByColumnAndRow($current_x + $shift_x['remarks'], $current_y + $shift_y['remarks'], $dispatch['remarks']);
            
            $current_y += 2;
        }
                
        //不要行の非表示化
        $row_start = $current_y;
        for ($i = $row_start; $i <= 246; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        //改ページ設定
        for ($i = 30; $i <= $current_y; $i += 24) {
            $worksheet->setBreak("B".$i, Worksheet::BREAK_ROW);
        }        
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('DI0024', \Config::get('m_DI0024'), '', self::$db);
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
        $fileName = '配車表（共配便）.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
        
    }
    
    /**
     * ヘッダー情報取得
     */
    public static function getHeader($car_code) {
        $db = self::$db;
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // データ取得
        $stmt = \DB::select(
                array('mc.car_code', 'car_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'")'), 'driver_name')
                );
        // テーブル
        $stmt->from(array('m_car', 'mc'));
        $stmt->join(array('m_car_model', 'mcm'), 'INNER')
                ->on('mcm.car_model_code', '=', 'mc.car_model_code')
                ->on('mcm.start_date', '<=', \DB::expr('\''.date('Y-m-d').'\''))
                ->on('mcm.end_date', '>', \DB::expr('\''.date('Y-m-d').'\''));
        $stmt->join(array('m_member', 'mm'), 'INNER')
                ->on('mm.car_code', '=', 'mc.car_code')
                ->on('mm.start_date', '<=', \DB::expr('\''.date('Y-m-d').'\''))
                ->on('mm.end_date', '>', \DB::expr('\''.date('Y-m-d').'\''));
        // 条件
        $stmt->where('mc.car_code', '=', $car_code)
            ->where('mc.start_date', '<=', date('Y-m-d'))
            ->where('mc.end_date', '>', date('Y-m-d'));
        
        return $stmt->execute($db)->current();
    }
    
}