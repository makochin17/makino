<?php
namespace Model\Printing;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\OpeLog;
use \Model\Common\CommonSql;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

ini_set("memory_limit", "1000M");

class T0070 extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 出力対象得意先取得
     */
    public static function getClientList($conditions) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //課コード
        $division_code = $conditions['division'];
        //得意先コード
        $client_code = $conditions['client_code'];
        //対象日付
        $target_date = date('Y-m-d', strtotime($conditions['target_date']));
        
        //配車集計-------------------------------------------------------------
        //項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name')
                );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'))
            ->join(array('m_client', 'm'), 'inner')
                ->on('t.client_code', '=', 'm.client_code')
                ->on('m.start_date', '<=', '"'.date("Y-m-d").'"')
                ->on('m.end_date', '>', '"'.date("Y-m-d").'"');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 対象日付
        $stmt->where('t.stack_date', '=', $target_date);
        
        // グループ化
        $stmt->group_by('m.client_code');
        
        // ソート
        $stmt->order_by('m.client_name', 'ASC');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        //項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.official_name),"'.$encrypt_key.'")'), 'official_name')
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
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        if (trim($client_code) != '') {
            $stmt->where('m.client_code', '=', $client_code);
        }
        // 対象日付
        $stmt->where('tc.stack_date', '=', $target_date);
        
        // グループ化
        $stmt->group_by('m.client_code');
        
        // ソート
        $stmt->order_by('m.client_name', 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計と分載集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[$dispatch_charter['client_code']] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $merge_list[$carrying_charter['client_code']] = $carrying_charter;
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
     * 車番案内データ取得
     */
    public static function getDetailData($division_code, $client_code, $target_date) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        //配車集計-------------------------------------------------------------
        // 項目
        $stmt = \DB::select(
            array('t.stack_date', 'stack_date'),
            array('t.drop_date', 'drop_date'),
            array(\DB::expr('AES_DECRYPT(UNHEX(t.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
            array(\DB::expr('AES_DECRYPT(UNHEX(t.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
            array(\DB::expr('
                (SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= CURDATE() AND end_date > CURDATE())'
            ), 'car_model_name'),
            array(\DB::expr('
                CASE
                    WHEN AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'") != \'\' THEN AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")
                    ELSE (SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= CURDATE() AND end_date > CURDATE())
                END'
            ), 'carrier_name'),
            array('t.car_code', 'car_number'),
            array(\DB::expr('
                CASE
                    WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")
                    ELSE (SELECT AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'") FROM m_member WHERE member_code = t.member_code AND start_date <= CURDATE() AND end_date > CURDATE())
                END'
            ), 'driver_name'),
            array(\DB::expr('AES_DECRYPT(UNHEX(t.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
            array('t.remarks', 'remarks')
        );
        
        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'));
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        $stmt->where('t.client_code', '=', $client_code);
        // 対象日付
        $stmt->where('t.stack_date', '=', $target_date);
        
        // ソート
        $stmt->order_by('t.stack_date', 'ASC')
                ->order_by('t.drop_date', 'ASC');
        
        // 検索実行
        $dispatch_charter_list = $stmt->execute(self::$db)->as_array();
        
        //分載集計-------------------------------------------------------------
        // 項目
        $stmt = \DB::select(
            array('tc.stack_date', 'stack_date'),
            array('tc.drop_date', 'drop_date'),
            array(\DB::expr('AES_DECRYPT(UNHEX(tc.stack_place),"'.$encrypt_key.'")'), 'stack_place'),
            array(\DB::expr('AES_DECRYPT(UNHEX(tc.drop_place),"'.$encrypt_key.'")'), 'drop_place'),
            array(\DB::expr('
                (SELECT car_model_name FROM m_car_model WHERE car_model_code = tc.car_model_code AND start_date <= CURDATE() AND end_date > CURDATE())'
            ), 'car_model_name'),
            array(\DB::expr('
                CASE
                    WHEN AES_DECRYPT(UNHEX(tc.destination),"'.$encrypt_key.'") != \'\' THEN AES_DECRYPT(UNHEX(tc.destination),"'.$encrypt_key.'")
                    ELSE (SELECT carrier_name FROM m_carrier WHERE carrier_code = tc.carrier_code AND start_date <= CURDATE() AND end_date > CURDATE())
                END'
            ), 'carrier_name'),
            array('tc.car_code', 'car_number'),
            array(\DB::expr('
                CASE
                    WHEN tc.member_code IS NULL THEN AES_DECRYPT(UNHEX(tc.driver_name),"'.$encrypt_key.'")
                    ELSE (SELECT AES_DECRYPT(UNHEX(driver_name),"'.$encrypt_key.'") FROM m_member WHERE member_code = tc.member_code AND start_date <= CURDATE() AND end_date > CURDATE())
                END'
            ), 'driver_name'),
            array(\DB::expr('AES_DECRYPT(UNHEX(tc.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
            array('t.remarks', 'remarks')
        );
        
        // テーブル
        $stmt->from(array('t_carrying_charter', 'tc'))
            ->join(array('t_dispatch_charter', 't'), 'inner')
                ->on('t.dispatch_number', '=', 'tc.dispatch_number');
        
        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 分載
        $stmt->where('t.carrying_count', '!=', '0');
        // 課コード
        $stmt->where('t.division_code', '=', $division_code);
        // 得意先コード
        $stmt->where('tc.client_code', '=', $client_code);
        // 対象日付
        $stmt->where('tc.stack_date', '=', $target_date);
        
        // ソート
        $stmt->order_by('tc.stack_date', 'ASC')
                ->order_by('tc.drop_date', 'ASC');
        
        // 検索実行
        $carrying_charter_list = $stmt->execute(self::$db)->as_array();
        
        //配車集計と分載集計を結合
        $merge_list = array();
        foreach ($dispatch_charter_list as $dispatch_charter) {
            $merge_list[] = $dispatch_charter;
        }
        foreach ($carrying_charter_list as $carrying_charter) {
            $merge_list[] = $carrying_charter;
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
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t0070_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $result = array('division' => $conditions['division'],
                        'client_code' => $conditions['client_code'],
                        'target_date' => $conditions['target_date']);
        
        return $result;
    }
        
    /**
     * エクセルファイル名取得
     */
    public static function getExcelName() {
        $conditions = self::getConditions();
        
        $division_list = GenerateList::getDivisionList(false, self::$db);
        $division_name = $division_list[$conditions['division']];
        
        $filename = "【".$division_name."】車番案内書（".date('Ymd',  strtotime($conditions['target_date']))."）";
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
        $spreadsheet = $reader->load($tpl_dir.'template車番案内書.xlsx');
        
        //携帯電話が空なら非表示にして担当を上に詰める
        $company_data = CommonSql::getCompanyData($conditions['division'], self::$db);
        if (empty($company_data['mobile_phone_number'])) {
            $worksheet = $spreadsheet->getSheetByName('車番案内');
            $worksheet->setCellValue('I8', '担当');
            $worksheet->setCellValue('J8', '=共通項目!B8');
            $worksheet->setCellValue('I9', '');
            $worksheet->setCellValue('J9', '');
        }
        
        //シート複製
        for ($i = 0; $i < $client_count; $i++) {
            $CloneSheet = clone $spreadsheet->getSheetByName('車番案内');
            $CloneSheet->setTitle($client_list[$i]['client_name'].sprintf('%05d', $client_list[$i]['client_code']));
            $spreadsheet->addSheet($CloneSheet);
        }
        
        //テンプレートシート削除
        $sel_index = $spreadsheet->getIndex($spreadsheet->getSheetByName('車番案内'));
        $spreadsheet->removeSheetByIndex($sel_index);
        
        //共通項目シート出力
        $worksheet = $spreadsheet->getSheetByName('共通項目');
        self::outputCommon($worksheet, $conditions['target_date'], $company_data);
        
        //明細出力ループ
        foreach ($client_list as $client) {
            $worksheet = $spreadsheet->getSheetByName($client['client_name'].sprintf('%05d', $client['client_code']));
            if (self::outputDetail($worksheet, $conditions['division'], $conditions['target_date'], $client)) {
                //出力したシートをアクティブに
                $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
            } else {
                //出力レコードがない場合はシート削除
                $sel_index = $spreadsheet->getIndex($worksheet);
                $spreadsheet->removeSheetByIndex($sel_index);
            }
        }
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0019', \Config::get('m_TI0019'), '', self::$db);
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
        $worksheet->setCellValue('B2', date('Y/m/d',  strtotime($target_date)));
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
     * 共通項目シート出力
     */
    public static function outputDetail($worksheet, $division_code, $target_date, $client_data) {
        
        //出力データ取得
        $detail_data = self::getDetailData($division_code, $client_data['client_code'], $target_date);
        $detail_count = 0;
        if (is_countable($detail_data)){
            $detail_count = count($detail_data);
        }
        
        //出力データが0件なら処理中断
        if ($detail_count == 0)return false;
        
        //帳票ヘッダ項目出力
        $worksheet->setCellValue('B1', $client_data['official_name']);
        
        //明細出力
        $row = 12;
        foreach ($detail_data as $detail) {
            //積日
            $worksheet->setCellValue('B'.$row, date('m/d',  strtotime($detail['stack_date'])));
            //卸日
            $worksheet->setCellValue('C'.$row, date('m/d',  strtotime($detail['drop_date'])));
            //積地
            $worksheet->setCellValue('D'.$row, $detail['stack_place']);
            //卸地
            $worksheet->setCellValue('E'.$row, $detail['drop_place']);
            //車種
            $worksheet->setCellValue('F'.$row, $detail['car_model_name']);
            //傭車先・運行先
            $worksheet->setCellValue('G'.$row, $detail['carrier_name']);
            //車番
            $worksheet->setCellValue('H'.$row, sprintf('%04d', $detail['car_number']));
            //乗務員
            $worksheet->setCellValue('I'.$row, $detail['driver_name']);
            //携帯番号
            $worksheet->setCellValue('J'.$row, $detail['phone_number']);
            //備考
            $worksheet->setCellValue('K'.$row, $detail['remarks']);
            
            $row++;
        }
        
        return true;
    }
}