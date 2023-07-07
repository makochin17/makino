<?php
namespace Model\Bill;
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
use PhpOffice\PhpSpreadsheet\Style\Fill;                // セルの装飾用
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;       // ワークシート用
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension; // セル幅用
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;           // セル用
use PhpOffice\PhpSpreadsheet\Cell\DataType;             // セルフォーマット用
// use PhpOffice\PhpSpreadsheet\IOFactory as Fact;         // スプレッドシート用ファイル操作クラス

ini_set("memory_limit", "1000M");

class B1020 extends \Model {

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

    /**
     * 配車データの取得（存在チェック用）
     */
    public static function getDispatchShare($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 請求データの取得（存在チェック用）
     */
    public static function getBillShare($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.bill_number', 'bill_number'));

        // テーブル
        $stmt->from(array('t_bill_share_link', 't'));

        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

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
                $name   = "建材請求表_雛形";
                break;
            default:
                $name   = "共配便請求表_雛形";
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
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no))->setWidth(15);
                        // セルに書き込み
                        if (is_null($val2) || trim($val2) == '') {
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, DataType::TYPE_STRING);
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
                            // 枠線
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            // セルの背景色
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
                            // セルの横位置
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        $col_no++;
                        if ($key == 0) { $header_col_no++; }
                    }
                    // 処理結果表示
                    if ($key > 0) {
                        // 除外セル
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col_no-1, $row_no)->getCoordinate())->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no-1))->setAutoSize(true);
                        // エラー用セルの横幅
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no))->setAutoSize(true);
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
                            // 枠線
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            // // セルの背景色
                            // $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
                            // // セルの横位置
                            // $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        $col_no++;
                        if ($key == 0) { $header_col_no++; }
                    }
                    // 処理結果表示
                    if ($key > 0) {
                        // 除外セル
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col_no-1, $row_no)->getCoordinate())->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no-1))->setAutoSize(true);
                        // エラー用セルの横幅
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no))->setAutoSize(true);
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
    //======================   請求共配便一括登録処理   ==========================//
    //=========================================================================//
    /**
     * 売上ステータスデータ更新
     */
    public static function updDispatchShareSalesStatus($dispatch_number, $sales_status = 1, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

        // 配車番号
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 請求データ紐付けの登録
     */
    public static function addBillShareLink($dispatch_number, $bill_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number) || empty($bill_number)) {
            return false;
        }

        // 項目セット
        $set = array('bill_number' => $bill_number
                    ,'dispatch_number' => $dispatch_number);

        // テーブル
        $stmt = \DB::insert('t_bill_share_link')->set($set);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 数字チェック（小数点を含む判定）
     * $mode true:カンマを取り除く false:カンマを取り除かない
     */
    public static function is_numeric_decimal($val, $deci, $mode=false) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }

        // カンマを取り除く
        if ($mode) {
            $val = str_replace(',', '', $val);
        }

        // 小数点を含む数字のみか？
        if (preg_match( '/^[0-9]+(.[0-9]{1,' . $deci . '})?$/', $val)) {
            return true;
        } elseif(preg_match( '/^[0-9]+$/', $val)) {
            // 小数点がない場合も数字のみなら通す
            return true;
        }

        return false;

    }

    /**
     * 数字チェック（小数点を含まない判定）
     */
    public static function is_numeric_conma($val) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }

        // カンマを取り除く
        $val = str_replace(',', '', $val);

        // 小数点を含む数字のみか？
        if (preg_match( '/^[0-9]+$/', $val)) {
            return true;
        }

        return false;

    }

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
     * 社員マスタ運転手取得
     */
    public static function getDriverByMember($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array(\DB::expr('AES_DECRYPT(UNHEX(m.driver_name),"'.$encrypt_key.'")'), 'driver_name')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));

        // 社員コード
        $stmt->where('m.member_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        // 検索実行
        $ret = $stmt->execute($db)->current();

        if (!empty($ret)) {
            return $ret['driver_name'];
        }
        return null;
    }

    /**
     * インポートファイルカラム
     */
    public static function getCsvColumns($type = null){

        switch ($type) {
            case '1':   // 未分類
            default:
                return array(
                    'dispatch_number'   => '配車番号',
                    'division_code'     => '課コード',
                    'area_code'         => '地区コード',
                    'delivery_code'     => '配送区分コード',
                    'destination_date'  => '運行日付',
                    'destination'       => '運行先',
                    'client_code'       => '得意先コード',
                    'carrier_code'      => '庸車先コード',
                    'product_name'      => '商品名',
                    'price'             => '金額',
                    'unit_price'        => '単価',
                    'volume'            => '数量',
                    'unit_code'         => '単位コード',
                    'rounding_code'     => '端数処理コード',
                    'car_model_code'    => '車種コード',
                    'car_code'          => '車両番号',
                    'member_code'       => '社員コード',
                    'driver_name'       => 'ドライバー',
                    'onsite_flag'       => '現場',
                    'requester'         => '依頼者',
                    'inquiry_no'        => '問い合わせNo',
                    'delivery_address'  => '納品先住所',
                    'remarks1'          => '備考1',
                    'remarks2'          => '備考2',
                    'remarks3'          => '備考3',
                    'disable'           => '除外',
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
    public static function import($insert_or_update, $rows, $type, &$error_msg, $db) {

        \Config::load('message');
        $is_insert_only = $insert_or_update == 'insert' ? true : false;
        $errors = array();
        if (empty($rows)) {
            $error_msg = Config::get('m_CW0008');
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
             * カラムの確認（最初のみ）
             */
            if ($row_no == 1) {
                $errs = self::checkHeader($row, $type);
                if (!empty($errs)) {
                    $error_msg = \Config::get('m_CW0020');
                    return false;
                    // $errors[0] = $errs;
                    // return $errors;
                }
            }
            $new_row = self::getConvertData($row, $type);

            /**
             * バリデーション
             */
            // 除外フラグ
            if (!empty($new_row['disable']) && $new_row['disable'] == '○') {
                continue;
            }
            
            //値チェック
            list ($errs, $is_insert) = self::validBillShare($is_insert_only, $new_row, $type, $db);
            
            //重複チェック
            if (empty($errs) && $new_row['disable'] != '登録') {
                $errs = self::existsBillShare($new_row, $db);
            }
            
            if (!empty($errs)) {
                $errors[$row_no] = $errs;
                continue;
            } else {
                // 売上ステータス
                // $new_row['sales_status']    = 1;
            }
            /**
             * オプション登録
             */
            \DB::start_transaction($db);
            try {
                $new_row['disable'] = self::setBillShare($new_row, $type, $db);
                $errors[$row_no] = array_merge($new_row, array('登録完了'));

                \DB::commit_transaction($db);
            } catch (\Exception $e) {
                $errors[$row_no] = array_merge($new_row, array($e->getMessage()));
                \DB::rollback_transaction($db);
            }
        }
        // 操作ログ出力
        $result = OpeLog::addOpeLog('BI0008', \Config::get('m_BI0008'), '請求共配便一括登録', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
        }

        return $errors;
    }

    // バリデーション
    public static function validBillShare($is_insert_only, $data, $type, $db) {

        \Config::load('message');
        $errors         = array();
        $msg            = array();
        $is_insert      = true;
        $volume_cnt     = 0;
        $require_flg    = false;
        $header         = self::getCsvColumns($type);

        // 配車番号
        $error_column = '配車番号';
        if (trim($data['dispatch_number']) != '') {
            $dispatch_numbers = explode(",", $data['dispatch_number']);
            foreach($dispatch_numbers as $dispatch_number) {
                if (!is_numeric($dispatch_number)) {
                    $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
                }
                if (mb_strlen($dispatch_number) > 10) {
                    $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 10), \Config::get('m_BW0007'));
                }
                //配車データ存在チェック
                if (!self::getDispatchShare((int)$dispatch_number, $db)) {
                    $msg[]                      = \Config::get('m_BW0016');
                }
                //請求データ重複チェック
                if (self::getBillShare((int)$dispatch_number, $db)) {
                    $msg[]                      = \Config::get('m_BW0015');
                }
            }
        }
        // 課コード
        $error_column = '課コード';
        if (trim($data['division_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['division_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['division_code']) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_BW0007'));
            }
        }
        // 請求区分コード
        $error_column = '請求区分コード';
        if (empty($type)) {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($type)) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($type) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_BW0007'));
            }
        }
        // 配送区分コード
        $error_column = '配送区分コード';
        if (trim($data['delivery_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['delivery_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['delivery_code']) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_BW0007'));
            }
        }
        // 地区コード
        $error_column = '地区コード';
        if (trim($data['area_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['area_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['area_code']) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_BW0007'));
            }
        }
        // 運行日付
        $error_column = '運行日付';
        if (trim($data['destination_date']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (false === self::valid_date_format(str_replace('/', '-', $data['destination_date']))) {
                $msg[]                      = str_replace('XXXXX',$error_column,\Config::get('m_BW0008'));
            }
        }
        // 運行先
        $error_column = '運行先';
        if (trim($data['destination']) != '') {
            if (mb_strlen($data['destination']) > 30) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 30), \Config::get('m_BW0007'));
            }
        }
        // 得意先コード
        $error_column = '得意先コード';
        if (trim($data['client_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['client_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['client_code']) > 5) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_BW0007'));
            }
        }
        // 庸車先コード
        $error_column = '庸車先コード';
        if (trim($data['carrier_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['carrier_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['carrier_code']) > 5) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_BW0007'));
            }
        }
        // 商品名
        $error_column = '商品名';
        if (trim($data['product_name']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (mb_strlen($data['product_name']) > 30) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 30), \Config::get('m_BW0007'));
            }
        }
        // 金額
        $error_column = '金額';
        if (trim($data['price']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!self::is_numeric_conma($data['price'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
        }
        // 単価
        $error_column   = '単価';
        $unit_price_flg = true;
        if (trim($data['unit_price']) != '') {
            if (!self::is_numeric_decimal($data['unit_price'], 2, true)) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
                $unit_price_flg             = false;
            } else {
                $unit_price                 = $data['unit_price'];
                if (mb_strlen(intval($unit_price)) > 10) {
                    $msg[]                  = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 10), \Config::get('m_BW0007'));
                    $unit_price_flg         = false;
                }
            }
        } else {
            $unit_price_flg                 = false;
        }
        // 数量
        $error_column   = '数量';
        $volume_flg     = true;
        if (trim($data['volume']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
            $volume_flg                     = false;
        } else {
            if (!self::is_numeric_decimal($data['volume'], 6, true)) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
                $volume_flg                 = false;
            } else {
                if (mb_strlen(intval($data['volume'])) > 10) {
                    $msg[]                  = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 10), \Config::get('m_BW0007'));
                    $volume_flg             = false;
                }
            }
        }
        // 単位コード
        $error_column = '単位コード';
        if (trim($data['unit_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['unit_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['unit_code']) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_BW0007'));
            }
        }
        // 端数処理コード
        $error_column = '端数処理コード';
        if (trim($data['rounding_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['rounding_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['rounding_code']) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_BW0007'));
            }
        }
        // 金額整合性チェック
        if ($unit_price_flg === true && $volume_flg === true) {
            $rounding_code                  = $data['rounding_code'];
            $price                          = str_replace(',', '', $data['price']);
            $unit_price                     = str_replace(',', '', $data['unit_price']);
            $volume                         = str_replace(',', '', $data['volume']);
            if ((float)$unit_price > 0) {
                $calculation                = (float)$unit_price * (float)$volume;
                switch ((int)$rounding_code) {
                    case 1:   // 四捨五入
                        $calculation = round($calculation);
                        break;
                    case 2:   // 切り上げ
                        $calculation = ceil($calculation);
                        break;
                    case 3:   // 切り捨て
                        $calculation = floor($calculation);
                        break;
                }

                if ($calculation != (int)$price) {
                    $msg[]                  = \Config::get('m_BW0014');
                }
            }
        }
        // 車種コード
        $error_column = '車種コード';
        if (trim($data['car_model_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['car_model_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['car_model_code']) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_BW0007'));
            }
        }
        // 車両番号
        $error_column = '車両番号';
        if (trim($data['car_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!is_numeric($data['car_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['car_code']) > 4) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 4), \Config::get('m_BW0007'));
            }
        }
        // 社員コード
        $error_column = '社員コード';
        if (trim($data['member_code']) != '') {
            if (!is_numeric($data['member_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_BW0009'));
            }
            if (mb_strlen($data['member_code']) > 5) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_BW0007'));
            }
        }
        // 運転手
        $error_column = '運転手';
        if (trim($data['driver_name']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (mb_strlen($data['driver_name']) > 6) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 6), \Config::get('m_BW0007'));
            }
        }
        // 現場フラグ
        $error_column = '現場フラグ';
        if (trim($data['onsite_flag']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_BW0005'));
        } else {
            if (!preg_match('/[0-1]/', $data['onsite_flag'], $m)) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, '0もしくは1'), \Config::get('m_BW0010'));
            }
        }
        // 依頼者
        $error_column = '依頼者';
        if (trim($data['requester']) != '') {
            if (mb_strlen($data['requester']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_BW0007'));
            }
        }
        // 問い合わせNo
        $error_column = '問い合わせNo';
        if (trim($data['inquiry_no']) != '') {
            if (mb_strlen($data['inquiry_no']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_BW0007'));
            }
        }
        // 納品先住所
        $error_column = '納品先住所';
        if (trim($data['delivery_address']) != '') {
            if (mb_strlen($data['delivery_address']) > 40) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 40), \Config::get('m_BW0007'));
            }
        }
        // 備考1
        $error_column = '備考1';
        if (trim($data['remarks1']) != '') {
            if (mb_strlen($data['remarks1']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_BW0007'));
            }
        }
        // 備考2
        $error_column = '備考2';
        if (trim($data['remarks2']) != '') {
            if (mb_strlen($data['remarks2']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_BW0007'));
            }
        }
        // 備考3
        $error_column = '備考3';
        if (trim($data['remarks3']) != '') {
            if (mb_strlen($data['remarks3']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_BW0007'));
            }
        }

        if (!empty($msg)) {
            $errors = array_merge($data, array(implode("\r\n", $msg)));
        }

        return array($errors, $is_insert);
    }
    
    // レコード存在チェック
    public static function existsBillShare($data, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 項目
        $stmt = \DB::select(\DB::expr('COUNT(t.bill_number) AS count'));
        // テーブル
        $stmt->from(array('t_bill_share', 't'));
        // 地区
        $stmt->where('t.area_code', '=', $data['area_code']);
        // 運行日
        $stmt->where('t.destination_date', '=', $data['destination_date']);
        // 運行先
        $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), '=', $data['destination']);
        // 得意先
        $stmt->where('t.client_code', '=', $data['client_code']);
        // 商品
        $stmt->where('t.product_name', '=', $data['product_name']);
        // 数量
        $stmt->where('t.volume', '=', $data['volume']);
        // 金額
        $stmt->where('t.price', '=', $data['price']);
        $stmt->where('t.delete_flag', '=', '0');
        
        $res = $stmt->execute($db)->as_array();
        
        if ($res[0]['count'] > 0) {
            return array_merge($data, array(\Config::get('m_BW0020')));
        }
        return array();
    }

    public static function setBillShare($data, $type, $db=null) {

        \Config::load('message');

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 配車番号
        $ins_data['dispatch_number']            = null;
        // 課コード
        $ins_data['division_code']              = $data['division_code'];
        // 売上ステータス
        $ins_data['sales_status']               = 1;
        // 配送区分コード
        $ins_data['delivery_code']              = $data['delivery_code'];
        // 地区コード
        $ins_data['area_code']                  = $data['area_code'];
        // 運行日付
        $ins_data['destination_date']           = date('Y-m-d', strtotime($data['destination_date']));
        // 運行先
        if (trim($data['destination']) != '') {
            $ins_data['destination']            = \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))');
        }
        // 得意先コード
        $ins_data['client_code']                = $data['client_code'];
        // 庸車先コード
        $ins_data['carrier_code']               = $data['carrier_code'];
        // 商品名
        $ins_data['product_name']               = $data['product_name'];
        // 金額
        $ins_data['price']                      = str_replace(',', '', $data['price']);
        // 単価
        if (trim($data['unit_price']) != '') {
            $ins_data['unit_price']             = str_replace(',', '', $data['unit_price']);
        }
        // 数量
        $ins_data['volume']                     = str_replace(',', '', $data['volume']);
        // 単位コード
        $ins_data['unit_code']                  = $data['unit_code'];
        // メーカー名
        $ins_data['rounding_code']              = $data['rounding_code'];
        // 車種コード
        $ins_data['car_model_code']             = $data['car_model_code'];
        // 車両番号
        $ins_data['car_code']                   = $data['car_code'];
        // 社員コード
        if (trim($data['member_code']) != '') {
            $ins_data['member_code']            = $data['member_code'];
        }
        // 運転手
        $ins_data['driver_name']                = \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))');
        // 車両番号
        $ins_data['car_code']                   = $data['car_code'];
        // 現場フラグ
        $ins_data['onsite_flag']                = $data['onsite_flag'];
        // 依頼者
        if (trim($data['requester']) != '') {
            $ins_data['requester']              = $data['requester'];
        }
        // 問い合わせNo
        if (trim($data['inquiry_no']) != '') {
            $ins_data['inquiry_no']             = $data['inquiry_no'];
        }
        // 納品先住所
        if (trim($data['delivery_address']) != '') {
            $ins_data['delivery_address']       = \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_address'].'","'.$encrypt_key.'"))');
        }
        // 備考1
        if (trim($data['remarks1']) != '') {
            $ins_data['remarks']                = $data['remarks1'];
        }
        // 備考2
        if (trim($data['remarks2']) != '') {
            $ins_data['remarks2']                = $data['remarks2'];
        }
        // 備考3
        if (trim($data['remarks3']) != '') {
            $ins_data['remarks3']                = $data['remarks3'];
        }

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_bill_share')->set(array_merge($ins_data, self::getEtcData(true)))->execute($db);

        if(!$insert_id) {
            throw new \Exception(\Config::get('m_BW0011'));
        }
        // 配車情報関連の更新処理
        if (!empty($data['dispatch_number'])) {
            $dispatch_numbers = explode(",", $data['dispatch_number']);
            foreach($dispatch_numbers as $dispatch_number) {
                // 配車情報の売上ステータスを更新
                $result = self::updDispatchShareSalesStatus($dispatch_number, 2, $db);
                if (!$result) {
                    throw new \Exception(\Config::get('m_BW0017'));
                }
                
                // 請求データと配車番号の紐付けを登録
                $result = self::addBillShareLink($dispatch_number, $insert_id, $db);
                if (!$result) {
                    throw new \Exception(\Config::get('m_BW0021'));
                }
            }
        }
        return '○';
    }

}