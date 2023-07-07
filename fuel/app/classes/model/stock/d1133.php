<?php
namespace Model\Stock;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

use PhpOffice\PhpSpreadsheet\Spreadsheet;               // スプレッドシート用
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;   // 拡張子xlsのExcelファイル読み込み用
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader; // 拡張子xlsxのExcelファイル読み込み用
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter; // 拡張子xlsxのExcelファイル書き込み用
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;        // 日付形式
use PhpOffice\PhpSpreadsheet\Style\Border;              // 罫線用
use PhpOffice\PhpSpreadsheet\Style\Alignment;           // 出力位置指定用
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;       // ワークシート用
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension; // セル幅用
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;           // セル用
use PhpOffice\PhpSpreadsheet\Cell\DataType;             // セルフォーマット用
// use PhpOffice\PhpSpreadsheet\IOFactory as Fact;         // スプレッドシート用ファイル操作クラス

ini_set("memory_limit", "1000M");

class D1133 extends \Model {

    public static $db       = 'MAKINO';

    public static $format_array = array(
                                  'xls'             => 'Excel5'
                                , 'xlsx'            => 'Excel2007'
                                , 'csv'             => 'CSV'
                                , 'tsv'             => 'TSV'
                            );

    public static $format_array_sp = array(
                                  'xls'             => 'Xls'
                                , 'xlsx'            => 'Xlsx'
                                , 'csv'             => 'Csv'
                            );

    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 保管料部署
            'division_code'         => array('name' => '保管料部署', 'max_lengths' => '10'),
            // 日付
            'closing_date'          => array('name' => '締日', 'max_lengths' => ''),
        );
    }

    /**
     * 得意先マスタ取得
     */
    public static function getClient($storage_in_charge = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array('m.closing_date', 'closing_date'),
                array('m.storage_fee', 'storage_fee'),
                array('m.storage_in_charge', 'storage_in_charge')
                );

        // テーブル
        $stmt->from(array('m_client', 'm'));

        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 保管担当部署
        if (!is_null($storage_in_charge) && $storage_in_charge != '000') {
            $stmt->where('m.storage_in_charge', '=', (int)$storage_in_charge);
        }

        $stmt->order_by('m.client_code', 'ASC');
        // 検索実行
        $ret = $stmt->execute($db)->as_array();

        if (!empty($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 保管料レコード検索（バリデーション用）
     */
    public static function getStorageFee($client_code, $closing_date , $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($client_code) || empty($closing_date)) {
            return false;
        }

        // 項目
        $stmt = \DB::select();

        // テーブル
        $stmt->from(array('t_storage_fee', 't'));
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);
        // 締日
        $stmt->where('t.closing_date', '=', $closing_date);
        // 得意先コード
        $stmt->where('t.client_code', '=', (int)$client_code);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

    /**
     * エクセル出力処理
     */
    public static function getExcel($kind) {

        // テンプレート読み込み
        $reader         = new XlsxReader();
        $tpl_dir        = DOCROOT.'assets/template/';

        switch ($kind) {
            case '2':
                $name   = "建材配車表_雛形";
                break;
            default:
                $name   = "共配便配車表_雛形";
                break;
        }
        $spreadsheet    = $reader->load($tpl_dir.$name.'.xlsx');

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
        $fileName = $name.'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');
        ob_end_clean();

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    //=========================================================================//
    //=========================   Excel作成処理   ==============================//
    //=========================================================================//
    /**
     * エクセル作成処理
     */
    public static function create_boder($version='xlsx', $title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            foreach ($data as $key => $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no                         = 0; // カラム番号は0から
                    if ($key == 0) { $header_col_no = 0; }
                    foreach ($val1 as $val2) {
                        // セルの幅設定を自動にする
                        $sheet->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($col_no))->setWidth(15);
                        // セルに書き込み
                        if (is_null($val2) || trim($val2) == '') {
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        }
                        // セルに罫線を引く
                        if ($header_col_no > $col_no) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（上）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                            // // セル（下）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                            // // セル（左）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
                            // // セル（右）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
                        }
                        if ($key == 0) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        }

                        $col_no++;
                        if ($key == 0) { $header_col_no++; }
                    }
                    // 処理結果表示
                    if ($key > 0) {
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col_no-1, $row_no)->getCoordinate())->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($col_no-1))->setAutoSize(true);
                    }
                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        ob_end_clean(); //バッファ消去
        // ファイル書込み
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');

        return;

        // Excelデータの作成
        // $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        // $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        // $name           = tempnam('', 'excel_');
        // $writer->save($name);
        // $content        = file_get_contents($name);
        // @unlink($name);

        // return $content;

    }

    /**
     * エクセル作成処理
     */
    public static function sp_create_boder($version='xlsx', $title='', $data=array()) {

        /**
         * Excel作成
         */
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();
        // シートタイトル
        $sheet->setTitle($title);

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            $col_no = 0; // カラム番号は0から
            foreach ($data as $key => $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    if ($key == 0) { $header_col_no = 0; }
                    foreach ($val1 as $val2) {

                        // セルの幅設定を自動にする
                        $sheet->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($col_no))->setWidth(15);
                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        }
                        // セルに罫線を引く
                        if ($header_col_no > $col_no) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（上）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（下）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（左）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
                            // // セル（右）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
                        }
                        if ($key == 0) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        }

                        $col_no++;
                        if ($key == 0) { $header_col_no++; }
                    }
                    // 処理結果表示
                    if ($key > 0) {
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col_no-1, $row_no)->getCoordinate())->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($col_no-1))->setAutoSize(true);
                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = Coordinate::stringFromColumnIndex($col_no);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        ob_end_clean(); //バッファ消去
        // ファイル書込み
        $format_name    = isset(self::$format_array_sp[$version]) ? self::$format_array_sp[$version] : self::$format_array_sp['xlsx'];
        // $writer         = Fact::createWriter($spreadsheet, $format_name);
        $writer         = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        return;

        // Excelデータの作成
        // $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        // $writer         = Fact::createWriter($spreadsheet, $format_name);
        // $name           = tempnam('', 'excel_');
        // $writer->save($name);
        // $content        = file_get_contents($name);
        // @unlink($name);

        // return $content;

    }

    //=========================================================================//
    //=========================   Excelデータ取得   ============================//
    //=========================================================================//
    /**
     * 既存のExcelデータからセル内容を取得し連想配列で返す
     */
    public static function get($file, $format = 'xlsx') {

        // 初期設定
        $reader         = new XlsxReader();
        $format_array   = self::$format_array_sp;

        /**
         * 返り値
         */
        $res = array('excel_type' => 'xlsx', 'header' => array(), 'data' => array());

        /**
         * データから読み込み
         */
        foreach (self::$format_array as $format_name) {
            try {
                $obj  = \PHPExcel_IOFactory::createReader($format_name);
                $book = $obj->load($file);
                // $obj  = Fact::createReader($format_array[$format]);
                // $book = $obj->load($file);
                // $book = $reader->load($file);
            } catch (\Exception $e) {
                $book = null;
            }

            unset($obj);
            if (is_object($book)) {
                break;
            }
        }

        if ($book) {

            // Excel種類を設定
            $type = array_flip(self::$format_array);
            $res['excel_type'] = $type[$format_name];
            // $res['excel_type'] = $format;

            // シート設定
            $book->setActiveSheetIndex(0);
            $sheet = $book->getActiveSheet();

            // ヘッダを取得
            $header = array();
            for ($i = 0; true; $i++) {  // ※列は0から行は1からはじまる
                // 行1のセルに空文字が存在するまで取り込む
                $val = (string)$sheet->getCellByColumnAndRow($i, 1)->getValue();
                if (trim($val) == '') {
                    break;
                }
                $header[] = $val;
            }

            $number_colums = count($header);

            $body = array();
            // ボディ（データ）の取り込み
            for ($i = 2; true; $i++) {  // ※列は0から行は1からはじまるのでヘッダ後の2から

                $count_empty = 0;
                $columns     = array();
                for ($j = 0; $j < $number_colums; $j++) {

                    /**
                     * 行1のセルに空文字が存在するまで取り込む
                     * ※日付のみ注意する
                     */
                    // まずデータタイプを取得
                    $datatype       = $sheet->getCellByColumnAndRow($j, $i)->getDataType();
                    $format         = $sheet->getCellByColumnAndRow($j, $i)->getStyle()->getNumberFormat()->getFormatCode();
                    $is_datetime    = false;
                    $is_date        = false;
                    $is_time        = false;
                    if (DataType::TYPE_NUMERIC == $datatype) {
                        if (false !== strpos($format, 'h') &&
                            false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
                            $is_datetime = true;
                        } else if (false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
                            $is_date = true;
                        } else if (false !== strpos($format, 'h') && false !== strpos($format, 'mm')) {
                            $is_time = true;
                        }
                    }
                    if ($is_datetime) {
                        // yyyy-mm-dd
                        $val = (string)NumberFormat::toFormattedString(
                                                                      $sheet->getCellByColumnAndRow($j, $i)->getValue()
                                                                    , NumberFormat::FORMAT_DATE_DATETIME2
                                                                    );
                    } else if ($is_date) {
                        // yyyy-mm-dd
                        $val = (string)NumberFormat::toFormattedString(
                                                                      $sheet->getCellByColumnAndRow($j, $i)->getValue()
                                                                    , NumberFormat::FORMAT_DATE_YYYYMMDD2
                                                                    );
                    } else if ($is_time) {
                        // hh:mm:ss
                        $val = (string)NumberFormat::toFormattedString(
                                                                      $sheet->getCellByColumnAndRow($j, $i)->getValue()
                                                                    , ($format == 'h:mm:ss') ? NumberFormat::FORMAT_DATE_TIME6:NumberFormat::FORMAT_DATE_TIME9
                                                                    );
                    } else {
                        $val = (string)$sheet->getCellByColumnAndRow($j, $i)->getValue();
                    }
                    if (trim($val) == '') {
                        $count_empty++;
                        $val = trim($val);
                    }
                    $columns[$header[$j]] = (string)$val;

                }
                // 全値が空の場合そこで読み込み終了
                if ($count_empty >= $number_colums) {
                    break;
                }
                $body[] = $columns;
            }

            if (!empty($header)) {
                $res['header'] = $header;
            }
            if (!empty($body)) {
                $res['data'] = $body;
            }

        }
        return $res;
    }

    //=========================================================================//
    //======================   配車共配便一括登録処理   ==========================//
    //=========================================================================//
    /**
     * 日付チェック
     */
    public static function valid_date_format($date) {

        $date = str_replace('/', '-', $date);
        if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $m)) {
            // yyyy-mm-dd形式でない場合
            return false;
        }
        return checkdate($m[2], $m[3], $m[1]);

   }
    /**
     * 日付チェック
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    /**
     * 日時チェック
     */
    public static function validateDateTime($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * インポートファイルカラム
     */
    public static function getCsvColumns($type = null){

        switch ($type) {
            case '1':   // 未分類
            default:
                return array(
                    'division_code'     => '課',
                    'client_code'       => '得意先コード',
                    'client_name'       => '得意先名',
                    'closing_date'      => '締日',
                    'storage_fee'       => '保管料',
                    'result'            => '処理結果',
                );
                break;
        }
    }

    /**
     * 入力された列を確認して定義にない列を返す
     */
    public static function checkHeader($row, $type) {

        $header  = self::getCsvColumns($type);
        $errors  = array();
        $columns = array_keys($row);

        foreach ($header as $head) {
            if (!in_array($head, $columns)) {
                $errors[] = '列名：['.$head.'] がありません';
            }
        }
        return $errors;
    }
    public static function getConvertData($data, $type) {
        $new_data = array();
        $header   = self::getCsvColumns($type);
        foreach ($header as $column => $head) {
            if (isset($data[$head])) {
                $new_data[$column] = $data[$head];
            }
        }
        return $new_data;
    }

    /**
     * データインポートする
     */
    public static function import($insert_or_update, $rows, $input_data, &$error_msg, $db) {

        \Config::load('message');
        $division_list  = GenerateList::getDivisionList(true, self::$db);

        $is_insert_only = $insert_or_update == 'insert' ? true : false;
        $errors = array();

        if (empty($rows)) {
            $error_msg = Config::get('m_CW0021');
            return false;
            // $errors[0][] = 'データがありません';
            // return $errors;
        }
        /**
         * $rows: self::getCsvColumns に対応したもの
         */
        $row_no = 0;
        foreach ($rows as $row) {
            $row_no++;

            /**
             * バリデーション
             */
            list ($errs, $is_insert) = self::validStorageFee($is_insert_only, $row, $input_data, $db);
            if (!empty($errs)) {
                $errors[$row_no] = $errs;
                continue;
            }
            /**
             * オプション登録
             */
            \DB::start_transaction($db);
            try {
                $row['result']      = self::setStorageFee($row, $input_data, $db);
                $errors[$row_no]    = array(
                    'division_code'     => (isset($division_list[$row['storage_in_charge']])) ? $division_list[$row['storage_in_charge']]:'',
                    'client_code'       => $row['client_code'],
                    'client_name'       => $row['client_name'],
                    'closing_date'      => date('Y/m/d', strtotime($input_data['closing_date'])),
                    'storage_fee'       => $row['storage_fee'],
                    'result'            => $row['result'],
                );

                \DB::commit_transaction($db);
            } catch (\Exception $e) {
                $row['result']      = \Config::get('m_DI0051');
                $errors[$row_no]    = array(
                    'division_code'     => (isset($division_list[$row['storage_in_charge']])) ? $division_list[$row['storage_in_charge']]:'',
                    'client_code'       => $row['client_code'],
                    'client_name'       => $row['client_name'],
                    'closing_date'      => date('Y/m/d', strtotime($input_data['closing_date'])),
                    'storage_fee'       => '',
                    'result'            => $row['result'],
                );
                \DB::rollback_transaction($db);
            }
            // 操作ログ出力
            $result = OpeLog::addOpeLog('DI0045', \Config::get('m_DI0045'), '保管料情報一括登録', $db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
        }

        return $errors;
    }

    // バリデーション
    public static function validStorageFee($is_insert_only, $data, $input_data, $db) {

        \Config::load('message');
        $errors         = array();
        $msg            = array();
        $is_insert      = true;
        $division_list  = GenerateList::getDivisionList(true, self::$db);

        // 締日
        $error_column = '締日';
        if (trim($input_data['closing_date']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (false === self::valid_date_format(str_replace('/', '-', $input_data['closing_date']))) {
                $msg[]                      = str_replace('XXXXX','納品日',\Config::get('m_CW0018'));
            }
        }

        // 保管料設定判定
        if (empty($data['storage_fee'])) {
            $msg[]                          = \Config::get('m_DI0052');
        }
        // 締日不一致判定
        if ($data['closing_date'] == '99') {
            $db_closing_date    = date('Y-m-d', strtotime('last day of ' . $input_data['closing_date']));
        } elseif ($data['closing_date'] == '50') {
            $db_closing_date    = date('Y-m-d', strtotime($input_data['closing_date']));
        } else {
            $tmp                = date('Y-m', strtotime($input_data['closing_date']));
            $db_closing_date    = date('Y-m-d', strtotime($tmp.'-'.$data['closing_date']));

        }
        if ($db_closing_date != $input_data['closing_date']) {
            $msg[]                          = \Config::get('m_DI0053');
        }
        // 登録済み判定
        if ($storage_fee_list = self::getStorageFee($data['client_code'], $input_data['closing_date'], $db)) {
            $msg[]                          = \Config::get('m_DI0054');
        }

        if (!empty($msg)) {
            $errors = array(
                'division_code'     => (isset($division_list[$data['storage_in_charge']])) ? $division_list[$data['storage_in_charge']]:'',
                'client_code'       => $data['client_code'],
                'client_name'       => $data['client_name'],
                'closing_date'      => date('Y/m/d', strtotime($input_data['closing_date'])),
                'storage_fee'       => '',
                'result'            => implode("\r\n", $msg),
            );
        }

        return array($errors, $is_insert);
    }

    public static function setStorageFee($data, $input_data, $db=null) {

        \Config::load('message');

        // 売上ステータス
        $ins_data['sales_status']               = 1;
        // 課コード
        $ins_data['division_code']              = $data['storage_in_charge'];
        // 得意先コード
        $ins_data['client_code']                = $data['client_code'];
        // 締日
        $ins_data['closing_date']               = $input_data['closing_date'];
        // 保管料区分コード
        $ins_data['storage_fee_code']           = 1;
        // 保管料
        $ins_data['storage_fee']                = $data['storage_fee'];

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_storage_fee')->set(array_merge($ins_data, self::getEtcData(true)))->execute($db);

        if(!$insert_id) {
            // throw new \Exception(\Config::get('m_DW0030'));
            throw new \Exception();
        }
        return \Config::get('m_DI0050');
    }

}