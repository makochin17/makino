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

class T1122 extends \Model {

    public static $db       = 'ONISHI';

    /**
     * エクセル作成処理
     */
    public static function outputReport($input_list) {
        $tpl_dir = DOCROOT.'assets/template/';
        
        // テンプレート読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tpl_dir.'template納品書（JFE）.xlsx');
        
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
        
        $filename = "納品書（JFEロックファイバー株式会社）".date('Ymd');
        return $filename;
        
    }

    /**
     * ヘッダ部出力
     */
    public static function outputHeader($worksheet, $input_data) {
        //納入先
        $worksheet->setCellValue('G3', $input_data['delivery_place']);
        //車種
        $carmodel_list = GenerateList::getCarModelList(false, self::$db);
        $worksheet->setCellValue('E7', $carmodel_list[$input_data['car_model_code']]);
        //運送会社
        $worksheet->setCellValue('G7', self::getCarrierCompany($input_data['carrier_code']));
        //納入日
        $worksheet->setCellValue('C9', date('Y/m/d',  strtotime($input_data['delivery_date'])));
        //お客様
        $worksheet->setCellValue('A11', $input_data['delivery_place']);
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
        $add_sheet_c = floor(($detail_count - 1) / 4);
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
            
            //品名の出力
            $worksheet->setCellValue('D'.(12 + $row), $detail['product_name']);
            //入数の出力
            $worksheet->setCellValue('I'.(12 + $row), floatval($detail['volume']));
            //備考の出力準備
            if (empty($detail['remarks'])) {
                $remarks .= $row."．※なし\n";
            } else {
                $remarks .= $row."．".$detail['remarks']."\n";
            }
            
            //最終行の出力が終わった場合
            if ($row == 4) {
                //備考の出力
                $worksheet->setCellValue('H19', $remarks);
                
                $row = 0;
                $sheet_no++;
            }
            
            $row++;
            $detail_count++;
        }
        
        //備考の出力
        $worksheet->setCellValue('H19', $remarks);
        
        return true;
    }
}