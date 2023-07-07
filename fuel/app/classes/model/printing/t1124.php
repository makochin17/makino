<?php
namespace Model\Printing;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\OpeLog;
use \Model\Common\CommonSql;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class T1124 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * エクセル作成処理
     */
    public static function outputReport($input_list) {
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template納品書（OSCAR）.xlsx');
        
        $i = 1;
        //帳票出力ループ
        foreach ($input_list as $input_data) {
            
            //シート複製
            $sheetName = sprintf('%03d', $i).'_'.date('Ymd',  strtotime($input_data['delivery_date'])).'_'.mb_substr($input_data['delivery_place'], 0, 8).'_'.$input_data['division_name'];
            $CloneSheet = clone $spreadsheet->getSheetByName('雛形');
            $CloneSheet->setTitle($sheetName);
            $spreadsheet->addSheet($CloneSheet);
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $sheetList = array($sheetName);
            
            //ヘッダ部出力
            self::outputHeader($worksheet, $input_data);
            
            //明細データ取得
            $detail_data = self::getDetailData($input_data);
            $detail_count = 0;
            if (is_countable($detail_data)){
                $detail_count = count($detail_data);
            }

            //明細データが0件なら次のインプットレコードへ
            if ($detail_count == 0)continue;
            
            //出力シート複製
            $sheetList = self::copySheet($spreadsheet, $sheetList, $detail_count);
            
            //明細部出力
            self::outputDetail($spreadsheet, $sheetList, $detail_data);
            
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
        $fileName = self::getExcelName($input_list).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName($input_list) {
        
        $filename = "納品書（株式会社OSCAR）".date('Ymd');
        return $filename;
        
    }

    /**
     * ヘッダ部出力
     */
    public static function outputHeader($worksheet, $input_data) {
        //配達先
        $worksheet->setCellValue('B4', "配達先：　".$input_data['delivery_place']);
        //依頼主
        $worksheet->setCellValue('B7', "依頼主：　".$input_data['delivery_place']);
        //納入日
        $week = array( "（日）", "（月）", "（火）", "（水）", "（木）", "（金）", "（土）" );
        $week_text = $week[date("w", strtotime($input_data['delivery_date']))];
        $worksheet->setCellValue('B8', "納入日：　".date('Y年n月j日',  strtotime($input_data['delivery_date'])).$week_text);
    }
    
    /**
     * 庸車先会社名取得
     */
    public static function getCarrierCompany($carrier_code) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        // 項目
        $stmt = \DB::select(array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'carrier_name_company'));

        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 庸車先コード
        $stmt->where('m.carrier_code', '=', $carrier_code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        return $result[0]['carrier_name_company'];
    }
    
    /**
     * 出力シート複製
     */
    public static function copySheet($spreadsheet, $sheetList, $detail_count) {
        $add_sheet_c = floor(($detail_count - 1) / 7);
        for($j = 0; $j < $add_sheet_c; $j++){
            //シート複製
            $sheetName = $sheetList[0]."_".($j + 2);
            $CloneSheet = clone $spreadsheet->getSheetByName($sheetList[0]);
            $CloneSheet->setTitle($sheetName);
            $spreadsheet->addSheet($CloneSheet);
            $sheetList[] = $sheetName;
        }
        
        return $sheetList;
    }
    
    /**
     * 明細データ取得
     */
    public static function getDetailData($input_data) {
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //項目
        $stmt = \DB::select(
                array('t.product_name', 'product_name'),
                array('t.volume', 'volume'),
                array(\DB::expr('(SELECT m_unit.unit_name FROM m_unit WHERE m_unit.unit_code = t.unit_code)'), 'unit_name'),
                array('t.remarks', 'remarks')
        );
        
        //テーブル
        $stmt->from(array('t_dispatch_share', 't'));
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        //売上ステータス
        //$stmt->where('t.sales_status', '=', '2');
        //課コード
        $stmt->where('t.division_code', '=', $input_data['division_code']);
        //得意先コード
        $stmt->where('t.client_code', '=', $input_data['client_code']);
        //納品日
        $stmt->where('t.delivery_date', '=', $input_data['delivery_date']);
        //納品先
        $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), '=', (!empty($input_data['delivery_place']) ? $input_data['delivery_place'] : null));
        //庸車先コード
        $stmt->where('t.carrier_code', '=', $input_data['carrier_code']);
        //車種コード
        $stmt->where('t.car_model_code', '=', $input_data['car_model_code']);
        //車両番号
        $stmt->where('t.car_code', '=', $input_data['car_code']);
        
        //ソート
        $stmt->order_by('t.product_name', 'ASC');
        
        //検索実行
        $result = $stmt->execute(self::$db)->as_array();
        
        return $result;
    }
    
    /**
     * 明細部出力
     */
    public static function outputDetail($spreadsheet, $sheetList, $detail_data) {
        //明細出力
        $row = 1;
        $detail_count = 1;
        $remarks = "";
        $sheet_no = 0;
        foreach ($detail_data as $detail) {
            
            //シート切替
            if ($row == 1) {
                $worksheet = $spreadsheet->getSheetByName($sheetList[$sheet_no]);
                $remarks = "";
            }
            
            //商品の出力
            $worksheet->setCellValue('B'.(9 + $row), $detail['product_name']);
            //数量の出力
            $worksheet->setCellValue('E'.(9 + $row), floatval($detail['volume']));
            //単位の出力
            $worksheet->setCellValue('F'.(9 + $row), $detail['unit_name']);
            //備考の出力準備
            if (!empty($detail['remarks'])) {
                $remarks .= $detail['remarks']."\n";
            }
            
            //最終行の出力が終わった場合
            if ($row == 7) {
                //備考の出力
                $worksheet->setCellValue('A20', $remarks);
                
                $row = 0;
                $sheet_no++;
            }
            
            $row++;
            $detail_count++;
        }
        
        //備考の出力
        $worksheet->setCellValue('A20', $remarks);
        
        return true;
    }
}