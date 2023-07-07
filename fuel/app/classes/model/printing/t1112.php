<?php
namespace Model\Printing;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\OpeLog;
use \Model\Common\CommonSql;
use \Model\Common\closingdate;
use \Model\Printing\T1111;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;             // セルフォーマット用

ini_set("memory_limit", "1000M");

class T1112 extends \Model {

    public static $db       = 'MAKINO';
    
    // 地区リスト
    private static $area_list                  = array();
    // 単位リスト
    private static $unit_list                  = array();
    // 車種リスト
    private static $car_model_list             = array();
    // 配送区分リスト
    private static $delivery_list              = array();
    

    /**
     * エクセルファイル名取得
     */
    public static function getExcelName($conditions, $category) {
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        
        $filename = "【".$division_name."】請求明細書_".$category."（".date('Ym',  strtotime($conditions['target_date']."-01"))."）";
        return $filename;
        
    }
    
    /**
     * 分類名取得
     */
    public static function getCategoryName($category) {
        switch ($category){
            case '1':
                return "共配便";
                break;
            case '2':
                return "チャーター便";
                break;
            case '3':
                return "入庫";
                break;
            case '4':
                return "出庫";
                break;
            case '5':
                return "保管料";
                break;
            case '6':
                return "その他";
                break;
            default:
                return "";
        }
    }
    
    /**
     * エクセル作成処理（共配便）
     */
    public static function outputReport($conditions, $category) {
        $tpl_dir = DOCROOT.'assets/template/';
        $category_name = self::getCategoryName($category);
        
        //分類名が取得できない場合は処理中断
        if (empty($category_name))return \Config::get('m_CE0001');
        
        //得意先リスト取得
        $client_list = T1111::getClientList($conditions);
        $client_count = 0;
        if (is_countable($client_list)){
            $client_count = count($client_list);
        }
        
        //得意先リストが0件なら処理中断
        if ($client_count == 0)return \Config::get('m_CI0004');
        
        //各種リスト取得
        // 地区リスト取得
        self::$area_list                = GenerateList::getAreaList(false, self::$db);
        // 単位リスト取得
        self::$unit_list                = GenerateList::getUnitList(false, self::$db);
        // 車種リスト
        self::$car_model_list           = GenerateList::getCarModelList(false, self::$db);
        // 配送区分リスト取得
        self::$delivery_list            = GenerateList::getShareDeliveryCategoryList(false);
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template請求明細書（'.$category_name.'）.xlsx');
        
        //明細出力ループ
        foreach ($client_list as $client) {
            
            //集計開始日と集計終了日の計算
            $closing_date = closingdate::getFromToDate($conditions['target_date'], $conditions['target_date_day'], $client);
            $from_date = $closing_date['from_date'];
            $to_date = $closing_date['to_date'];
            
            //明細データ取得
            $detail_data = self::getDetailData($category, $conditions['division'], $client['client_code'], $from_date, $to_date, $conditions['area_code']);
            $detail_count = 0;
            if (is_countable($detail_data)){
                $detail_count = count($detail_data);
            }
            
            //明細データがなければ出力しないで次の得意先へ
            IF ($detail_count == 0)continue;
            
            //シート複製
            $sheetName = sprintf('%05d', $client['client_code']).$client['client_name'];
            $CloneSheet = clone $spreadsheet->getSheetByName('雛形');
            $CloneSheet->setTitle($sheetName);
            $spreadsheet->addSheet($CloneSheet);
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            
            //ヘッダ部出力
            self::outputHeader($worksheet, $client, $conditions['target_date'],date('Y-m-d',  strtotime($to_date.' -1 day')) , $category);
            
            //明細部出力
            self::outputDetail($worksheet, $detail_data, $category);
            
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
        $fileName = self::getExcelName($conditions, $category_name).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * ヘッダ部出力
     */
    public static function outputHeader($worksheet, $client, $target_date, $closing_date, $category) {
        
        $cell_coordinate = array();
        switch ($category){
            case '1':
                //共配便
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "N3";
                break;
            case '2':
                //チャーター便
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "K3";
                break;
            case '3':
                //入庫
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "I3";
                break;
            case '4':
                //出庫
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "I3";
                break;
            case '5':
                //保管料
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "I3";
                break;
            case '6':
                //その他
                $cell_coordinate[1] = "A1";
                $cell_coordinate[2] = "A3";
                $cell_coordinate[3] = "H3";
                break;
            default:
                
        }
        
        //タイトル
        $worksheet->setCellValue($cell_coordinate[1], date('Y年m月請求',  strtotime($target_date."-01")));
        //得意先名
        $worksheet->setCellValue($cell_coordinate[2], $client['official_name']);
        //締日
        $closing_date_tmp = $client['closing_date']."日締";
        if ($client['closing_date'] == "99") {
            $closing_date_tmp = "月末締";
        } elseif ($client['closing_date'] == "50") {
            $closing_date_tmp = "都度締";
        } elseif ($client['closing_date'] == "51" || $client['closing_date'] == "52") {
            $closing_date_tmp = date('n月j日締',  strtotime($closing_date));
        }
        $worksheet->setCellValue($cell_coordinate[3], $closing_date_tmp);
    }
    
    /**
     * 明細データ取得
     */
    public static function getDetailData($category, $division_code, $target_date, $target_day, $client_data, $area_code) {
        switch ($category){
            case '1':
                //共配便
                return T1111::getShareData($division_code, $target_date, $target_day, $client_data, $area_code);
                break;
            case '2':
                //チャーター便
                return T1111::getCharterData($division_code, $target_date, $target_day, $client_data);
                break;
            case '3':
                //入庫
                return T1111::getPushData($division_code, $target_date, $target_day, $client_data);
                break;
            case '4':
                //出庫
                return T1111::getPullData($division_code, $target_date, $target_day, $client_data);
                break;
            case '5':
                //保管料
                return T1111::getStorageData($division_code, $target_date, $target_day, $client_data);
                break;
            case '6':
                //その他
                return T1111::getEtcData($division_code, $target_date, $target_day, $client_data);
                break;
            default:
                return "";
        }
    }
    
    /**
     * 明細部出力
     */
    public static function outputDetail($worksheet, $detail_data, $category) {
        //明細出力
        switch ($category){
            case '1':
                //共配便
                self::outputShareDetail($worksheet, $detail_data);
                break;
            case '2':
                //チャーター便
                self::outputCharterDetail($worksheet, $detail_data);
                break;
            case '3':
                //入庫
                self::outputPushDetail($worksheet, $detail_data);
                break;
            case '4':
                //出庫
                self::outputPullDetail($worksheet, $detail_data);
                break;
            case '5':
                //保管料
                self::outputStorageDetail($worksheet, $detail_data);
                break;
            case '6':
                //その他
                self::outputEtcDetail($worksheet, $detail_data);
                break;
            default:
        }
    }
    
    /**
     * 明細部出力（共配便）
     */
    public static function outputShareDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //日付
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['destination_date'])));
            //配送区分
            $worksheet->setCellValue('B'.$row, self::$delivery_list[$detail['delivery_code']]);
            //地区
            $worksheet->setCellValue('C'.$row, self::$area_list[$detail['area_code']]);
            //運行先
            $worksheet->setCellValue('D'.$row, $detail['destination']);
            //商品
            $worksheet->setCellValue('E'.$row, $detail['product_name']);
            //数量
            $worksheet->setCellValue('F'.$row, floatval($detail['volume']));
            //単位
            $worksheet->setCellValue('G'.$row, self::$unit_list[$detail['unit_code']]);
            //車種
            $worksheet->setCellValue('H'.$row, self::$car_model_list[$detail['car_model_code']]);
            //車番
            $worksheet->setCellValue('I'.$row, sprintf('%04d', $detail['car_code']));
            //運賃
            $worksheet->setCellValue('J'.$row, $detail['price']);
            //依頼者
            $worksheet->setCellValue('K'.$row, $detail['requester']);
            //問い合わせNo
            $worksheet->setCellValueExplicit('L'.$row, $detail['inquiry_no'], DataType::TYPE_STRING);
            //備考
            $remarks = $detail['remarks'];
            $line_count = 1;
            if (!empty($detail['remarks2'])) {
                $remarks .= "\r\n".$detail['remarks2'];
                $line_count++;
            }
            if (!empty($detail['remarks3'])) {
                $remarks .= "\r\n".$detail['remarks3'];
                $line_count++;
            }
            
            $cell_hight = 39.75;
            if ($line_count == 2)$cell_hight = 80.25;
            if ($line_count == 3)$cell_hight = 118.50;
            
            $worksheet->setCellValue('M'.$row, $remarks);
            $worksheet->getStyle('M'.$row)->getAlignment()->setWrapText(true);
            $worksheet->getRowDimension($row)->setRowHeight($cell_hight);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
    /**
     * 明細部出力（チャーター便）
     */
    public static function outputCharterDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //積日
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['stack_date'])));
            //卸日
            $worksheet->setCellValue('B'.$row, date('m/d',  strtotime($detail['drop_date'])));
            //積地
            $worksheet->setCellValue('C'.$row, $detail['stack_place']);
            //卸地
            $worksheet->setCellValue('D'.$row, $detail['drop_place']);
            //車種
            $worksheet->setCellValue('E'.$row, self::$car_model_list[$detail['car_model_code']]);
            //車番
            $worksheet->setCellValue('F'.$row, sprintf('%04d', $detail['car_number']));
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
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
    /**
     * 明細部出力（入庫）
     */
    public static function outputPushDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //日付
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['destination_date'])));
            //区分
            $worksheet->setCellValue('B'.$row, $detail['stock_change_name']);
            //運行先
            $worksheet->setCellValue('C'.$row, $detail['destination']);
            //商品
            $worksheet->setCellValue('D'.$row, $detail['product_name']);
            //数量
            $worksheet->setCellValue('E'.$row, floatval($detail['volume']));
            //単位
            $worksheet->setCellValue('F'.$row, self::$unit_list[$detail['unit_code']]);
            //入庫料
            $worksheet->setCellValue('G'.$row, $detail['fee']);
            //備考
            $worksheet->setCellValue('H'.$row, $detail['remarks']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
    /**
     * 明細部出力（出庫）
     */
    public static function outputPullDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //日付
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['destination_date'])));
            //区分
            $worksheet->setCellValue('B'.$row, $detail['stock_change_name']);
            //運行先
            $worksheet->setCellValue('C'.$row, $detail['destination']);
            //商品
            $worksheet->setCellValue('D'.$row, $detail['product_name']);
            //数量
            $worksheet->setCellValue('E'.$row, floatval($detail['volume']));
            //単位
            $worksheet->setCellValue('F'.$row, self::$unit_list[$detail['unit_code']]);
            //出庫料
            $worksheet->setCellValue('G'.$row, $detail['fee']);
            //備考
            $worksheet->setCellValue('H'.$row, $detail['remarks']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
    /**
     * 明細部出力（保管料）
     */
    public static function outputStorageDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //日付
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['closing_date'])));
            //商品
            $worksheet->setCellValue('B'.$row, $detail['product_name']);
            //メーカー
            $worksheet->setCellValue('C'.$row, $detail['maker_name']);
            //単価
            $worksheet->setCellValue('D'.$row, $detail['unit_price']);
            //数量
            $worksheet->setCellValue('E'.$row, floatval($detail['volume']));
            //単位
            $worksheet->setCellValue('F'.$row, self::$unit_list[$detail['unit_code']]);
            //保管料
            $worksheet->setCellValue('G'.$row, $detail['storage_fee']);
            //備考
            $worksheet->setCellValue('H'.$row, $detail['remarks']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
    /**
     * 明細部出力（その他）
     */
    public static function outputEtcDetail($worksheet, $detail_data) {
        $row = 7;
        foreach ($detail_data as $detail) {
            //日付
            $worksheet->setCellValue('A'.$row, date('m/d',  strtotime($detail['sales_date'])));
            //分類
            $worksheet->setCellValue('B'.$row, $detail['sales_category_value']);
            //傭車先
            $worksheet->setCellValue('C'.$row, $detail['carrier_name']);
            //車種
            $worksheet->setCellValue('D'.$row, self::$car_model_list[$detail['car_model_code']]);
            //車番
            $worksheet->setCellValue('E'.$row, sprintf('%04d', $detail['car_number']));
            //金額
            $worksheet->setCellValue('F'.$row, $detail['claim_sales']);
            //備考
            $worksheet->setCellValue('G'.$row, $detail['remarks']);
            
            $row++;
        }
        
        //不要行の非表示化
        $row_start = $row;
        for ($i = $row_start; $i <= 306; $i++) {
            $worksheet->getRowDimension($i)->setVisible(false);
        }
    }
    
}