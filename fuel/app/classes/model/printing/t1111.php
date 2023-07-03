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

class T1111 extends \Model {

    public static $db       = 'ONISHI';

    /**
     * エクセル作成処理
     */
    public static function outputReport($conditions) {
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
        $spreadsheet = $reader->load($tpl_dir.'template請求明細書（集計）.xlsx');
        
        //明細出力ループ
        foreach ($client_list as $client) {
            
            //集計開始日と集計終了日の計算
            $closing_date = closingdate::getFromToDate($conditions['target_date'], $conditions['target_date_day'], $client);
            $from_date = $closing_date['from_date'];
            $to_date = $closing_date['to_date'];
            
            //明細データ取得
            $detail_data = self::getDetailData($conditions['division'], $from_date, $to_date, $client);
            $zero_flag = true;
            foreach ($detail_data as $key => $value) {
                IF ($value != 0)$zero_flag = false;
            }
            
            //金額がすべて0なら出力しないで次の得意先へ
            IF ($zero_flag)continue;
            
            //シート複製
            $sheetName = sprintf('%05d', $client['client_code']).$client['client_name'];
            $CloneSheet = clone $spreadsheet->getSheetByName('雛形');
            $CloneSheet->setTitle($sheetName);
            $spreadsheet->addSheet($CloneSheet);
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            
            //ヘッダ部出力
            self::outputHeader($worksheet, $client, $conditions['target_date'], date('Y-m-d',  strtotime($to_date.' -1 day')));
            
            //明細部出力
            self::outputDetail($worksheet, $detail_data);
            
            //出力したシートをアクティブに
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
        }
        
        //シートが1件なら処理中断（雛形シートのみで実質出力なしのため）
        if ($spreadsheet->getSheetCount() == 1)return \Config::get('m_CI0004');
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('雛形'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
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
        $fileName = self::getExcelName($conditions).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 出力対象得意先取得
     */
    public static function getClientList($conditions) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //得意先コード
        $client_code = $conditions['client_code'];
        //対象年月
        $target_date = $conditions['target_date'].'-01';
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array('m.closing_date', 'closing_date'),
                array('m.closing_date_1', 'closing_date_1'),
                array('m.closing_date_2', 'closing_date_2'),
                array('m.closing_date_3', 'closing_date_3'),
                array(\DB::expr('m.criterion_closing_date'), 'criterion_closing_date')
                );
        
        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 適用開始日
        $stmt->where('m.start_date', '<=', $target_date);
        // 適用終了日
        $stmt->where('m.end_date', '>=', $target_date);
        
        // グループ化
        //$stmt->group_by('m.client_code');
        
        // 検索実行
        $result_list = $stmt->execute(self::$db)->as_array();
                
        return $result_list;
    }
    
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName($conditions) {
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        
        $filename = "【".$division_name."】請求明細書（".date('Ym',  strtotime($conditions['target_date']."-01"))."）";
        return $filename;
        
    }

    /**
     * ヘッダ部出力
     */
    public static function outputHeader($worksheet, $client, $target_date, $closing_date) {
        
        //タイトル
        $worksheet->setCellValue('A1', date('Y年m月請求',  strtotime($target_date."-01")));
        //得意先名
        $worksheet->setCellValue('A3', $client['official_name']);
        //締日
        $closing_date_tmp = $client['closing_date']."日締";
        if ($client['closing_date'] == "99") {
            $closing_date_tmp = "月末締";
        } elseif ($client['closing_date'] == "50") {
            $closing_date_tmp = "都度締";
        } elseif ($client['closing_date'] == "51" || $client['closing_date'] == "52") {
            $closing_date_tmp = date('n月j日締',  strtotime($closing_date));
        }
        $worksheet->setCellValue('H3', $closing_date_tmp);
    }
    
    /**
     * 明細データ取得
     */
    public static function getDetailData($division_code, $from_date, $to_date, $client_data) {
        
        //出力データ取得
        $client_code = $client_data['client_code'];
        
        $detail_data = array(
            "共配便" => self::getShareData($division_code, $client_code, $from_date, $to_date, null, 2),
            "チャーター便" => self::getCharterData($division_code, $client_code, $from_date, $to_date, 2),
            "入庫料" => self::getPushData($division_code, $client_code, $from_date, $to_date, 2),
            "出庫料" => self::getPullData($division_code, $client_code, $from_date, $to_date, 2),
            "保管料" => self::getStorageData($division_code, $client_code, $from_date, $to_date, 2)
        );
        $etc_list = self::getEtcData($division_code, $client_code, $from_date, $to_date, 2);
        foreach ($etc_list as $etc) {
            $detail_data += array($etc['sales_category_value'] => $etc['total_price']);
        }
        
        return $detail_data;
    }
    
    /**
     * 明細部出力
     */
    public static function outputDetail($worksheet, $detail_data) {
        //明細出力
        $row = 6;
        foreach ($detail_data as $key => $value) {
            //見出し
            $worksheet->setCellValue('B'.$row, $key);
            //金額
            $worksheet->setCellValue('C'.$row, $value);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 44; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
        
        return true;
    }
    
    /**
     * 共配便のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getShareData($division_code, $client_code, $from_date, $to_date, $area_code, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        if ($mode == 2) {
            $stmt = \DB::select(array(\DB::expr('IFNULL(SUM(CASE WHEN t.delivery_code = 3 THEN t.price * -1 ELSE t.price END),0)'), 'total_price'));
        } else {
            $stmt = \DB::select(
                    array('t.bill_number', 'bill_number'),
                    array('t.delivery_code', 'delivery_code'),
                    array('t.area_code', 'area_code'),
                    array('t.destination_date', 'destination_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                    array('t.product_name', 'product_name'),
                    //array('t.price', 'price'),
                    array(\DB::expr('(CASE WHEN t.delivery_code = 3 THEN t.price * -1 ELSE t.price END)'), 'price'),
                    array('t.volume', 'volume'),
                    array('t.unit_code', 'unit_code'),
                    array('t.car_model_code', 'car_model_code'),
                    array('t.car_code', 'car_code'),
                    array('t.requester', 'requester'),
                    array('t.inquiry_no', 'inquiry_no'),
                    array('t.remarks', 'remarks'),
                    array('t.remarks2', 'remarks2'),
                    array('t.remarks3', 'remarks3')
                    );
        }
        
        //テーブル
        $stmt->from(array('t_bill_share', 't'));
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        //課コード
        $stmt->where('t.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        //地区コード
        if (!empty($area_code) && $area_code != "00") {
            $stmt->where('t.area_code', '=', $area_code);
        }
        //運行日付
        $stmt->where('t.destination_date', '>=', $from_date);
        $stmt->where('t.destination_date', '<', $to_date);
        
        //ソート
        if ($mode == 1)$stmt->order_by('t.destination_date', 'DESC')->order_by('t.client_code', 'DESC')->order_by('t.bill_number', 'DESC');
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        if ($mode == 2)return $result[0]['total_price'];
        
        return $result;
    }
    
    /**
     * チャーター便のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getCharterData($division_code, $client_code, $from_date, $to_date, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //配車集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('t.stack_date', 'stack_date'),
                array('t.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array('t.car_model_code', 'car_model_code'),
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
                ->on('m_client.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        //分載
        $stmt->where('t.carrying_count', '=', '0');
        //課コード
        $stmt->where('t.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        //集計開始日
        $date_case = '(CASE WHEN t.drop_appropriation = 2 THEN t.drop_date ELSE '
                . '(CASE WHEN m_client.criterion_closing_date = 1 THEN t.stack_date ELSE t.drop_date END) END)';
        $stmt->where(\DB::expr($date_case), '>=', $from_date);
        //集計終了日
        $stmt->where(\DB::expr($date_case), '<', $to_date);
        
        //検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        $stmt = \DB::select(
                array('tc.dispatch_number', 'dispatch_number'),
                array('tc.carrying_number', 'carrying_number'),
                array('tc.stack_date', 'stack_date'),
                array('tc.drop_date', 'drop_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(tc.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
                array('tc.car_model_code', 'car_model_code'),
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
            ->join(array('m_car_model', 'm_car_model'), 'inner');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('t.sales_status', '=', '2');
        //分載
        $stmt->where('t.carrying_count', '!=', '0');
        //課コード
        $stmt->where('t.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('tc.client_code', '=', $client_code);
        //集計開始日
        $stmt->where(\DB::expr('(CASE WHEN m_client.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '>=', $from_date);
        //集計終了日
        $stmt->where(\DB::expr('(CASE WHEN m_client.criterion_closing_date = 1 THEN tc.stack_date ELSE tc.drop_date END)'), '<', $to_date);
        //ソート
        $stmt->order_by('tc.dispatch_number', 'ASC')->order_by('tc.carrying_number', 'ASC');
        
        //検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        //レコード集約
        $carrying_charter_list = self::aggregateCarryingCharter($carrying_charter_list);
        
        //配車集計、分載集計を結合
        $merge_list = array();
        $total_price = 0;
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
            $total_price += $dispatch_charter['claim_sales'] + $dispatch_charter['claim_sales_tax_free'] + $dispatch_charter['highway_fee'];
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $merge_list[] = $carrying_charter;
            $total_price += $carrying_charter['claim_sales'] + $carrying_charter['claim_sales_tax_free'] + $carrying_charter['highway_fee'];
        }
        
        if ($mode == 2)return $total_price;
        
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
     * 入庫のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getPushData($division_code, $client_code, $from_date, $to_date, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        if ($mode == 2) {
            $stmt = \DB::select(array(\DB::expr('IFNULL(SUM(tsc.fee),0)'), 'total_price'));
        } else {
            $stmt = \DB::select(
                    array('tsc.stock_change_number', 'stock_change_number'),
                    array('tsc.stock_number', 'stock_number'),
                    array('ts.product_name', 'product_name'),
                    array('tsc.stock_change_code', 'stock_change_code'),
                    array(\DB::expr('(SELECT output_name FROM m_stock_change WHERE stock_change_code = tsc.stock_change_code)'), 'stock_change_name'),
                    array('tsc.destination_date', 'destination_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(tsc.destination),"'.$encrypt_key.'")'), 'destination'),
                    array('tsc.volume', 'volume'),
                    array('ts.unit_code', 'unit_code'),
                    array('tsc.fee', 'fee'),
                    array('tsc.remarks', 'remarks'),
                    );
        }
        
        //テーブル
        $stmt->from(array('t_stock_change', 'tsc'));
        //在庫データ
        $stmt->join(array('t_stock', 'ts'), 'INNER')
            ->on('tsc.stock_number', '=', 'ts.stock_number');
        
        //削除フラグ
        $stmt->where('tsc.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('tsc.sales_status', '=', '2');
        //入出庫区分コード
        $stmt->where('tsc.stock_change_code', 'IN', array('1','3','5'));
        //課コード
        $stmt->where('ts.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('ts.client_code', '=', $client_code);
        //運行日付
        $stmt->where('tsc.destination_date', '>=', $from_date);
        $stmt->where('tsc.destination_date', '<', $to_date);
        
        //ソート
        if ($mode == 1)$stmt->order_by('tsc.destination_date', 'DESC')->order_by('ts.division_code', 'ASC')->order_by('ts.client_code', 'ASC');
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        if ($mode == 2)return $result[0]['total_price'];
        
        return $result;
    }
    
    /**
     * 出庫のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getPullData($division_code, $client_code, $from_date, $to_date, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        if ($mode == 2) {
            $stmt = \DB::select(array(\DB::expr('IFNULL(SUM(tsc.fee),0)'), 'total_price'));
        } else {
            $stmt = \DB::select(
                    array('tsc.stock_change_number', 'stock_change_number'),
                    array('tsc.stock_number', 'stock_number'),
                    array('ts.product_name', 'product_name'),
                    array('tsc.stock_change_code', 'stock_change_code'),
                    array(\DB::expr('(SELECT output_name FROM m_stock_change WHERE stock_change_code = tsc.stock_change_code)'), 'stock_change_name'),
                    array('tsc.destination_date', 'destination_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(tsc.destination),"'.$encrypt_key.'")'), 'destination'),
                    array('tsc.volume', 'volume'),
                    array('ts.unit_code', 'unit_code'),
                    array('tsc.fee', 'fee'),
                    array('tsc.remarks', 'remarks'),
                    );
        }
        
        //テーブル
        $stmt->from(array('t_stock_change', 'tsc'));
        //在庫データ
        $stmt->join(array('t_stock', 'ts'), 'INNER')
            ->on('tsc.stock_number', '=', 'ts.stock_number');
        
        //削除フラグ
        $stmt->where('tsc.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('tsc.sales_status', '=', '2');
        //入出庫区分コード
        $stmt->where('tsc.stock_change_code', 'IN', array('2','4','6'));
        //課コード
        $stmt->where('ts.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('ts.client_code', '=', $client_code);
        //運行日付
        $stmt->where('tsc.destination_date', '>=', $from_date);
        $stmt->where('tsc.destination_date', '<', $to_date);
        
        //ソート
        if ($mode == 1)$stmt->order_by('tsc.destination_date', 'DESC')->order_by('ts.division_code', 'ASC')->order_by('ts.client_code', 'ASC');
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        if ($mode == 2)return $result[0]['total_price'];
        
        return $result;
    }
    
    /**
     * 保管料のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getStorageData($division_code, $client_code, $from_date, $to_date, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        if ($mode == 2) {
            $stmt = \DB::select(array(\DB::expr('IFNULL(SUM(tsf.storage_fee),0)'), 'total_price'));
        } else {
            $stmt = \DB::select(
                    array('tsf.storage_fee_number', 'storage_fee_number'),
                    array('tsf.closing_date', 'closing_date'),
                    array('tsf.storage_fee', 'storage_fee'),
                    array('tsf.unit_price', 'unit_price'),
                    array('tsf.volume', 'volume'),
                    array('tsf.unit_code', 'unit_code'),
                    array('tsf.product_name', 'product_name'),
                    array('tsf.maker_name', 'maker_name'),
                    array('tsf.remarks', 'remarks'),
                    );
        }
        
        //テーブル
        $stmt->from(array('t_storage_fee', 'tsf'));
        
        //削除フラグ
        $stmt->where('tsf.delete_flag', '=', '0');
        //売上ステータス
        $stmt->where('tsf.sales_status', '=', '2');
        //課コード
        $stmt->where('tsf.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('tsf.client_code', '=', $client_code);
        //運行日付
        $stmt->where('tsf.closing_date', '>=', $from_date);
        $stmt->where('tsf.closing_date', '<', $to_date);
        
        //ソート
        if ($mode == 1)$stmt->order_by('tsf.closing_date', 'DESC')->order_by('tsf.client_code', 'DESC')->order_by('tsf.storage_fee_number', 'DESC');
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        if ($mode == 2)return $result[0]['total_price'];
        
        return $result;
    }
    
    /**
     * その他のデータ取得
     * $mode　1:明細レコード取得　2:合計金額取得 
     */
    public static function getEtcData($division_code, $client_code, $from_date, $to_date, $mode = 1) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        if ($mode == 2) {
            $stmt = \DB::select(
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'sales_category_value'),
                    array(\DB::expr('IFNULL(SUM(CASE WHEN t.highway_fee_claim = 2 THEN (t.sales + t.highway_fee) ELSE t.sales END),0)'), 'total_price')
                    );
        } else {
            $stmt = \DB::select(
                    array('t.sales_date', 'sales_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'sales_category_value'),
                    array('t.carrier_code', 'carrier_code'),
                    array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                    array('t.car_model_code', 'car_model_code'),
                    array('t.car_code', 'car_number'),
                    array(\DB::expr('CASE WHEN t.highway_fee_claim = 2 THEN (t.sales + t.highway_fee) ELSE t.sales END'), 'claim_sales'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.remarks),"'.$encrypt_key.'")'), 'remarks'),
                    );
        }
        
        // テーブル
        $stmt->from(array('t_sales_correction', 't'));
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上区分コード
        $stmt->where('t.sales_category_code', '=', '99');
        //課コード
        $stmt->where('t.division_code', '=', $division_code);
        //得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        //集計開始日
        $stmt->where('t.sales_date', '>=', $from_date);
        //集計終了日
        $stmt->where('t.sales_date', '<', $to_date);
        
        //ソート
        if ($mode == 2) {
            $stmt->order_by(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'DESC');
        } else {
            $stmt->order_by(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'), 'DESC')->order_by('t.sales_date', 'DESC')->order_by('t.sales_correction_number', 'DESC');
        }
        
        // グループ化
        if ($mode == 2)$stmt->group_by(\DB::expr('AES_DECRYPT(UNHEX(t.sales_category_value),"'.$encrypt_key.'")'));
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        return $result;
    }
}