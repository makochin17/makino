<?php
namespace Model\Summary;
use \Model\Common\OpeLog;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class T0061 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * エクセル作成処理（3課）
     */
    public static function createExcel1() {
        $conditions = T0060::getConditions();
        
        //ドライバーリスト取得
        $driver_list = T0060::getDriverList($conditions);
        $driver_count = 0;
        if (is_countable($driver_list)){
            $driver_count = count($driver_list);
        }
        if ($driver_count == 0) {
            return \Config::get('m_CI0004');
        }
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'templateドライバー別売上集計表（3課）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('氏名');
        
        //シート複製
        for ($i = 0; $i < $driver_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('氏名');
            $CloneSheet->setTitle($driver_list[$i]['member_name']);
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('氏名'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //行データ作成
        $record_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            array_push($record_list, array('stack_date' => $i, 'drop_date' => '', 'client_name' => '休み', 'stack_place' => '', 'drop_place' => '', 'claim_sales' => 0, 'highway_fee' => 0));
        }
        
        foreach ($driver_list as $driver) {
            //シート選択
            $worksheet = $spreadsheet->getSheetByName($driver['member_name']);
            
            //帳票ヘッダ項目出力
            //タイトル
            $worksheet->setCellValue('A1', $conditions['target_month']."月度運転作業月報");
            //乗務員
            $worksheet->setCellValue('C6', $driver['member_name']);
            //車番
            $worksheet->setCellValue('C7', $driver['car_number']);
            
            //社員運賃集計データ取得
            $member_fare_list = T0060::getMemberFareList($conditions, $driver['member_code']);
            
            //出力行数
            $output_row = 18;
            
            foreach ($record_list as $record) {
                $stack_date = $record['stack_date'];
                
                //社員運賃集計データから積日でレコード検索
                $member_fare_keys = array_keys(array_column($member_fare_list, 'stack_date'), $stack_date);
                
                if (empty($member_fare_keys)) {
                    //空行の出力
                    $drop_date   = $record['drop_date'];
                    $client_name = $record['client_name'];
                    $stack_place = $record['stack_place'];
                    $drop_place  = $record['drop_place'];
                    $tonnage     = '';
                    $claim_sales = $record['claim_sales'];
                    $highway_fee = $record['highway_fee'];
                    $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee);
                    
                    self::outputRecord1($worksheet, $record_data, $output_row);
                    $output_row++;
                } else {
                    //社員運賃集計データ出力
                    foreach ($member_fare_keys as $member_fare_key) {
                        $drop_date   = $member_fare_list[$member_fare_key]['drop_date'];
                        $client_name = $member_fare_list[$member_fare_key]['client_name'];
                        $stack_place = $member_fare_list[$member_fare_key]['stack_place'];
                        $drop_place  = $member_fare_list[$member_fare_key]['drop_place'];
                        $tonnage     = $driver['tonnage'];
                        $claim_sales = $member_fare_list[$member_fare_key]['claim_sales'];
                        $highway_fee = $member_fare_list[$member_fare_key]['highway_fee'];
                        $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee);

                        self::outputRecord1($worksheet, $record_data, $output_row);
                        $output_row++;
                    }
                }
            }
            
            if ($conditions['fare_radio'] == 2) {
                //運賃合計を数値化
                $sum = $worksheet->getCell('C11')->getCalculatedValue();
                $worksheet->setCellValue('C11', $sum);
                
                for ($i = 18; $i < 111; $i++) {
                    $worksheet->setCellValue('G'.$i, '');
                }
                
            }
            
            //不要行の非表示化
            $row_start = $output_row;
            if ($output_row < 59)$row_start = 59;

            for ($i = $row_start; $i <= 111; $i++) {
                $worksheet->getRowDimension($i)->setVisible(false);
            }

            //改ページ設定
            if ($output_row > 59)$worksheet->setBreak("A58", Worksheet::BREAK_ROW);
        }
        
        try {
            \DB::start_transaction(T0060::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0016', \Config::get('m_TI0016'), '', T0060::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0060::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0060::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0060::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 行出力処理（3課）
     */
    public static function outputRecord1($worksheet, $record_data, $output_row) {
        
        //休日判定
        $week = date('w', strtotime($record_data[0]));
        $text_color = '';
        if ($week == 0) {
            $text_color = 'FFFF0000';
        } elseif ($week == 6) {
            $text_color = 'FF0000FF';
        }
        
        //積日
        $worksheet->setCellValue('A'.$output_row, substr($record_data[0], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('A'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //降日
        $worksheet->setCellValue('B'.$output_row, substr($record_data[1], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('B'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //荷主名
        $worksheet->setCellValue('C'.$output_row, $record_data[2]);
        if (!empty($text_color)) {
            $worksheet->getStyle('C'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //積地
        $worksheet->setCellValue('D'.$output_row, $record_data[3]);
        //降地
        $worksheet->setCellValue('E'.$output_row, $record_data[4]);
        //ｔ
        $worksheet->setCellValue('F'.$output_row, $record_data[5]);
        //運賃
        $worksheet->setCellValue('G'.$output_row, $record_data[6]);
        //高速
        $worksheet->setCellValue('H'.$output_row, $record_data[7]);
        //地場手当
        $worksheet->setCellValue('I'.$output_row, 0);
        //土曜手当
        $worksheet->setCellValue('J'.$output_row, 0);
        //日曜手当
        $worksheet->setCellValue('K'.$output_row, 0);
        //日発
        $worksheet->setCellValue('L'.$output_row, 0);
        //欠勤
        $worksheet->setCellValue('M'.$output_row, 0);
        //その他
        $worksheet->setCellValue('N'.$output_row, 0);
    }
    
    /**
     * エクセル作成処理（2課）
     */
    public static function createExcel2() {
        $conditions = T0060::getConditions();
        
        //ドライバーリスト取得
        $driver_list = T0060::getDriverList($conditions);
        $driver_count = 0;
        if (is_countable($driver_list)){
            $driver_count = count($driver_list);
        }
        if ($driver_count == 0) {
            return \Config::get('m_CI0004');
        }
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'templateドライバー別売上集計表（２課）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('氏名');
        
        //シート複製
        for ($i = 0; $i < $driver_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('氏名');
            $CloneSheet->setTitle($driver_list[$i]['member_name']);
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('氏名'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //行データ作成
        $record_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            array_push($record_list, array('stack_date' => $i, 'drop_date' => '', 'client_name' => '休み', 'stack_place' => '', 'drop_place' => '', 'claim_sales' => 0, 'highway_fee' => 0, 'stay' => 0, 'linking_wrap' => 0));
        }
        
        foreach ($driver_list as $driver) {
            //シート選択
            $worksheet = $spreadsheet->getSheetByName($driver['member_name']);
            
            //帳票ヘッダ項目出力
            //タイトル
            $worksheet->setCellValue('A1', $conditions['target_month']."月度運転作業月報");
            //乗務員
            $worksheet->setCellValue('C6', $driver['member_name']);
            //車番
            $worksheet->setCellValue('C7', $driver['car_number']);
            
            //社員運賃集計データ取得
            $member_fare_list = T0060::getMemberFareList($conditions, $driver['member_code']);
            
            //出力行数
            $output_row = 16;
            
            foreach ($record_list as $record) {
                $stack_date = $record['stack_date'];
                
                //社員運賃集計データから積日でレコード検索
                $member_fare_keys = array_keys(array_column($member_fare_list, 'stack_date'), $stack_date);
                
                if (empty($member_fare_keys)) {
                    //空行の出力
                    $drop_date   = $record['drop_date'];
                    $client_name = $record['client_name'];
                    $stack_place = $record['stack_place'];
                    $drop_place  = $record['drop_place'];
                    $tonnage     = '';
                    $delivery_category = '';
                    $claim_sales = $record['claim_sales'];
                    $highway_fee = $record['highway_fee'];
                    $stay        = $record['stay'];
                    $linking_wrap = $record['linking_wrap'];
                    $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $delivery_category, $claim_sales, $highway_fee, $stay, $linking_wrap);
                    
                    self::outputRecord2($worksheet, $record_data, $output_row);
                    $output_row++;
                } else {
                    //社員運賃集計データ出力
                    foreach ($member_fare_keys as $member_fare_key) {
                        $drop_date   = $member_fare_list[$member_fare_key]['drop_date'];
                        $client_name = $member_fare_list[$member_fare_key]['client_name'];
                        $stack_place = $member_fare_list[$member_fare_key]['stack_place'];
                        $drop_place  = $member_fare_list[$member_fare_key]['drop_place'];
                        $tonnage     = $driver['car_model_name'];
                        $delivery_category = $member_fare_list[$member_fare_key]['delivery_category'];
                        $claim_sales = $member_fare_list[$member_fare_key]['claim_sales'];
                        $highway_fee = $member_fare_list[$member_fare_key]['highway_fee'];
                        $stay        = $member_fare_list[$member_fare_key]['stay'];
                        $linking_wrap = $member_fare_list[$member_fare_key]['linking_wrap'];
                        $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $delivery_category, $claim_sales, $highway_fee, $stay, $linking_wrap);

                        self::outputRecord2($worksheet, $record_data, $output_row);
                        $output_row++;
                    }
                }
            }
            
            if ($conditions['fare_radio'] == 2) {
                //運賃合計を数値化
                $sum = $worksheet->getCell('C11')->getCalculatedValue();
                $worksheet->setCellValue('C11', $sum);
                $sum = $worksheet->getCell('P7')->getCalculatedValue();
                $worksheet->setCellValue('P7', $sum);
                $sum = $worksheet->getCell('P8')->getCalculatedValue();
                $worksheet->setCellValue('P8', $sum);
                
                for ($i = 16; $i < 141; $i++) {
                    $worksheet->setCellValue('G'.$i, '');
                    $worksheet->setCellValue('H'.$i, '');
                }
                
            }
            
            //不要行の非表示化
            $row_start = $output_row;
            if ($output_row < 73)$row_start = 73;

            for ($i = $row_start; $i <= 141; $i++) {
                $worksheet->getRowDimension($i)->setVisible(false);
            }

            //改ページ設定
            if ($output_row > 73)$worksheet->setBreak("A72", Worksheet::BREAK_ROW);
        }
        
        try {
            \DB::start_transaction(T0060::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0017', \Config::get('m_TI0017'), '', T0060::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0060::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0060::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0060::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 行出力処理（2課）
     */
    public static function outputRecord2($worksheet, $record_data, $output_row) {
        
        //休日判定
        $week = date('w', strtotime($record_data[0]));
        $text_color = '';
        if ($week == 0) {
            $text_color = 'FFFF0000';
        } elseif ($week == 6) {
            $text_color = 'FF0000FF';
        }
        
        //積日
        $worksheet->setCellValue('A'.$output_row, substr($record_data[0], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('A'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //降日
        $worksheet->setCellValue('B'.$output_row, substr($record_data[1], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('B'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //荷主名
        $worksheet->setCellValue('C'.$output_row, $record_data[2]);
        if (!empty($text_color)) {
            $worksheet->getStyle('C'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //積地
        $worksheet->setCellValue('D'.$output_row, $record_data[3]);
        //降地
        $worksheet->setCellValue('E'.$output_row, $record_data[4]);
        //車種
        $worksheet->setCellValue('F'.$output_row, $record_data[5]);
        //運賃
        if (empty($record_data[6])) {
            $worksheet->setCellValue('G'.$output_row, $record_data[7]);
            $worksheet->setCellValue('H'.$output_row, $record_data[7]);
        } elseif($record_data[6] == '2') {
            //長距離運賃
            $worksheet->setCellValue('G'.$output_row, $record_data[7]);
            $worksheet->setCellValue('H'.$output_row, 0);
        } else {
            //ローカル運賃
            $worksheet->setCellValue('G'.$output_row, 0);
            $worksheet->setCellValue('H'.$output_row, $record_data[7]);
        }
        
        //総高速料金
        $worksheet->setCellValue('I'.$output_row, 0);
        //高速顧客請求
        $worksheet->setCellValue('J'.$output_row, 0);
        //高速請求
        $worksheet->setCellValue('K'.$output_row, $record_data[8]);
        //泊まり
        $worksheet->setCellValue('L'.$output_row, $record_data[9]);
        //連結・ラップ
        $worksheet->setCellValue('M'.$output_row, $record_data[10]);
    }
    
    /**
     * エクセル作成処理（輸送所）
     */
    public static function createExcel3() {
        $conditions = T0060::getConditions();
        
        //ドライバーリスト取得
        $driver_list = T0060::getDriverList($conditions);
        $driver_count = 0;
        if (is_countable($driver_list)){
            $driver_count = count($driver_list);
        }
        if ($driver_count == 0) {
            return \Config::get('m_CI0004');
        }
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'templateドライバー別売上集計表（輸送所）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('氏名');
        
        //シート複製
        for ($i = 0; $i < $driver_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('氏名');
            $CloneSheet->setTitle($driver_list[$i]['member_name']);
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('氏名'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //行データ作成
        $record_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            array_push($record_list, array('stack_date' => $i, 'drop_date' => '', 'client_name' => '休み', 'stack_place' => '', 'drop_place' => '', 'claim_sales' => 0, 'highway_fee' => 0));
        }
        
        foreach ($driver_list as $driver) {
            //シート選択
            $worksheet = $spreadsheet->getSheetByName($driver['member_name']);
            
            //帳票ヘッダ項目出力
            //タイトル
            $worksheet->setCellValue('A1', $conditions['target_month']."月度運転作業月報");
            //乗務員
            $worksheet->setCellValue('C6', $driver['member_name']);
            //車番
            $worksheet->setCellValue('C7', $driver['car_number']);
            
            //社員運賃集計データ取得
            $member_fare_list = T0060::getMemberFareList($conditions, $driver['member_code']);
            
            //出力行数
            $output_row = 18;
            
            foreach ($record_list as $record) {
                $stack_date = $record['stack_date'];
                
                //社員運賃集計データから積日でレコード検索
                $member_fare_keys = array_keys(array_column($member_fare_list, 'stack_date'), $stack_date);
                
                if (empty($member_fare_keys)) {
                    //空行の出力
                    $drop_date   = $record['drop_date'];
                    $client_name = $record['client_name'];
                    $stack_place = $record['stack_place'];
                    $drop_place  = $record['drop_place'];
                    $tonnage     = '';
                    $claim_sales = $record['claim_sales'];
                    $highway_fee = $record['highway_fee'];
                    $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee);
                    
                    self::outputRecord3($worksheet, $record_data, $output_row);
                    $output_row++;
                } else {
                    //社員運賃集計データ出力
                    foreach ($member_fare_keys as $member_fare_key) {
                        $drop_date   = $member_fare_list[$member_fare_key]['drop_date'];
                        $client_name = $member_fare_list[$member_fare_key]['client_name'];
                        $stack_place = $member_fare_list[$member_fare_key]['stack_place'];
                        $drop_place  = $member_fare_list[$member_fare_key]['drop_place'];
                        $tonnage     = $driver['tonnage'];
                        $claim_sales = $member_fare_list[$member_fare_key]['claim_sales'];
                        $highway_fee = $member_fare_list[$member_fare_key]['highway_fee'];
                        $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee);

                        self::outputRecord3($worksheet, $record_data, $output_row);
                        $output_row++;
                    }
                }
            }
            
            if ($conditions['fare_radio'] == 2) {
                //運賃合計を数値化
                $sum = $worksheet->getCell('C11')->getCalculatedValue();
                $worksheet->setCellValue('C11', $sum);
                
                for ($i = 18; $i < 126; $i++) {
                    $worksheet->setCellValue('G'.$i, '');
                }
                
            }
            
            //不要行の非表示化
            $row_start = $output_row;
            if ($output_row < 67)$row_start = 67;

            for ($i = $row_start; $i <= 126; $i++) {
                $worksheet->getRowDimension($i)->setVisible(false);
            }

            //改ページ設定
            if ($output_row > 67)$worksheet->setBreak("A66", Worksheet::BREAK_ROW);
        }
        
        try {
            \DB::start_transaction(T0060::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0018', \Config::get('m_TI0018'), '', T0060::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0060::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0060::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0060::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 行出力処理（輸送所）
     */
    public static function outputRecord3($worksheet, $record_data, $output_row) {
        
        //休日判定
        $week = date('w', strtotime($record_data[0]));
        $text_color = '';
        if ($week == 0) {
            $text_color = 'FFFF0000';
        } elseif ($week == 6) {
            $text_color = 'FF0000FF';
        }
        
        //積日
        $worksheet->setCellValue('A'.$output_row, substr($record_data[0], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('A'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //降日
        $worksheet->setCellValue('B'.$output_row, substr($record_data[1], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('B'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //荷主名
        $worksheet->setCellValue('C'.$output_row, $record_data[2]);
        if (!empty($text_color)) {
            $worksheet->getStyle('C'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //積地
        $worksheet->setCellValue('D'.$output_row, $record_data[3]);
        //降地
        $worksheet->setCellValue('E'.$output_row, $record_data[4]);
        //ｔ
        $worksheet->setCellValue('F'.$output_row, $record_data[5]);
        //運賃
        $worksheet->setCellValue('G'.$output_row, $record_data[6]);
        //高速
        $worksheet->setCellValue('H'.$output_row, $record_data[7]);
        //地場手当
        $worksheet->setCellValue('I'.$output_row, 0);
        //土曜手当
        $worksheet->setCellValue('J'.$output_row, 0);
        //日曜手当
        $worksheet->setCellValue('K'.$output_row, 0);
        //日発
        $worksheet->setCellValue('L'.$output_row, 0);
        //欠勤
        $worksheet->setCellValue('M'.$output_row, 0);
        //その他
        $worksheet->setCellValue('N'.$output_row, 0);
    }

    /**
     * エクセル作成処理（1課）
     */
    public static function createExcel4() {
        $conditions = T0060::getConditions();
        
        //ドライバーリスト取得
        $driver_list = T0060::getDriverList($conditions);
        $driver_count = 0;
        if (is_countable($driver_list)){
            $driver_count = count($driver_list);
        }
        if ($driver_count == 0) {
            return \Config::get('m_CI0004');
        }
        
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'templateドライバー別売上集計表（1課）.xlsx');
        
        $worksheet = $spreadsheet->getSheetByName('氏名');
        
        //シート複製
        for ($i = 0; $i < $driver_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('氏名');
            $CloneSheet->setTitle($driver_list[$i]['member_name']);
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('氏名'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //行データ作成
        $record_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            array_push($record_list, array('stack_date' => $i, 'drop_date' => '', 'client_name' => '休み', 'stack_place' => '', 'drop_place' => '', 'claim_sales' => 0, 'highway_fee' => 0, 'allowance' => 0));
        }
        
        foreach ($driver_list as $driver) {
            //シート選択
            $worksheet = $spreadsheet->getSheetByName($driver['member_name']);
            
            //帳票ヘッダ項目出力
            //タイトル
            $worksheet->setCellValue('A1', $conditions['target_month']."月度運転作業月報");
            //乗務員
            $worksheet->setCellValue('C6', $driver['member_name']);
            //車番
            $worksheet->setCellValue('C7', $driver['car_number']);
            
            //社員運賃集計データ取得
            $member_fare_list = T0060::getMemberFareList($conditions, $driver['member_code']);
            
            //出力行数
            $output_row = 15;
            
            foreach ($record_list as $record) {
                $stack_date = $record['stack_date'];
                
                //社員運賃集計データから積日でレコード検索
                $member_fare_keys = array_keys(array_column($member_fare_list, 'stack_date'), $stack_date);
                
                if (empty($member_fare_keys)) {
                    //空行の出力
                    $drop_date   = $record['drop_date'];
                    $client_name = $record['client_name'];
                    $stack_place = $record['stack_place'];
                    $drop_place  = $record['drop_place'];
                    $tonnage     = '';
                    $claim_sales = $record['claim_sales'];
                    $highway_fee = $record['highway_fee'];
                    $allowance   = $record['allowance'];
                    $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee, $allowance);
                    
                    self::outputRecord4($worksheet, $record_data, $output_row);
                    $output_row++;
                } else {
                    //社員運賃集計データ出力
                    foreach ($member_fare_keys as $member_fare_key) {
                        $drop_date   = $member_fare_list[$member_fare_key]['drop_date'];
                        $client_name = $member_fare_list[$member_fare_key]['client_name'];
                        $stack_place = $member_fare_list[$member_fare_key]['stack_place'];
                        $drop_place  = $member_fare_list[$member_fare_key]['drop_place'];
                        $tonnage     = $driver['tonnage'];
                        $claim_sales = $member_fare_list[$member_fare_key]['claim_sales'];
                        $highway_fee = $member_fare_list[$member_fare_key]['highway_fee'];
                        $allowance   = $member_fare_list[$member_fare_key]['allowance'];
                        $record_data = array($stack_date, $drop_date, $client_name, $stack_place, $drop_place, $tonnage, $claim_sales, $highway_fee, $allowance);

                        self::outputRecord4($worksheet, $record_data, $output_row);
                        $output_row++;
                    }
                }
            }
            
            if ($conditions['fare_radio'] == 2) {
                //運賃合計を数値化
                $sum = $worksheet->getCell('C11')->getCalculatedValue();
                $worksheet->setCellValue('C11', $sum);
                
                for ($i = 15; $i < 108; $i++) {
                    $worksheet->setCellValue('G'.$i, '');
                }
                
            }
            
            //不要行の非表示化
            $row_start = $output_row;
            if ($output_row < 58)$row_start = 58;

            for ($i = $row_start; $i <= 108; $i++) {
                $worksheet->getRowDimension($i)->setVisible(false);
            }

            //改ページ設定
            if ($output_row > 58)$worksheet->setBreak("A57", Worksheet::BREAK_ROW);
        }
        
        try {
            \DB::start_transaction(T0060::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0023', \Config::get('m_TI0023'), '', T0060::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(T0060::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(T0060::$db);
            \Log::error($e->getMessage());
        }

        // Excelデータの作成
        ob_end_clean();
        $fileName = T0060::getExcelName().'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 行出力処理（1課）
     */
    public static function outputRecord4($worksheet, $record_data, $output_row) {
        
        //休日判定
        $week = date('w', strtotime($record_data[0]));
        $text_color = '';
        if ($week == 0) {
            $text_color = 'FFFF0000';
        } elseif ($week == 6) {
            $text_color = 'FF0000FF';
        }
        
        //積日
        $worksheet->setCellValue('A'.$output_row, substr($record_data[0], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('A'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //降日
        $worksheet->setCellValue('B'.$output_row, substr($record_data[1], -2));
        if (!empty($text_color)) {
            $worksheet->getStyle('B'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //荷主名
        $worksheet->setCellValue('C'.$output_row, $record_data[2]);
        if (!empty($text_color)) {
            $worksheet->getStyle('C'.$output_row)->getFont()->getColor()->setARGB($text_color);
        }
        //積地
        $worksheet->setCellValue('D'.$output_row, $record_data[3]);
        //降地
        $worksheet->setCellValue('E'.$output_row, $record_data[4]);
        //ｔ
        $worksheet->setCellValue('F'.$output_row, $record_data[5]);
        //運賃
        $worksheet->setCellValue('G'.$output_row, $record_data[6]);
        //高速
        $worksheet->setCellValue('H'.$output_row, $record_data[7]);
        //手当
        $worksheet->setCellValue('I'.$output_row, $record_data[8]);
    }
}