<?php
namespace Model\Allocation;
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

class D1020 extends \Model {

    public static $db       = 'ONISHI';

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

    public static $code_list = array(
                                //未分類
                                1 => array(),
                                //BS
                                2 => array(),
                                //建材
                                3 => array(
                                'volume_koizumi'    => '小泉',
                                'volume_anneru'     => 'アンネル',
                                'volume_dream'      => 'ドリーム'),
                                //建材
                                4 => array(
                                'volume_wo'         => 'WO',
                                'volume_nankai'     => '南海',
                                'volume_eidai'      => '永大',
                                'volume_aika'       => 'アイカ',
                                'volume_hokukei'    => '北恵',
                                'volume_st'         => 'ST',
                                'volume_seven'      => 'セブン',
                                'volume_asahi'      => '旭化成'),
                                //電材
                                5 => array(
                                'volume_panasonic'  => 'パナソニック電工',
                                'volume_o-derikku'  => 'オーデリック',
                                'volume_koizumi'    => 'コイズミ照明',
                                'volume_daikou'     => '大光電機',
                                'volume_iwasaki'    => '岩崎電機',
                                'volume_tousiba'    => '東芝ライテック',
                                'volume_nec'        => 'NEC',
                                'volume_taiya'      => 'タイヤ',
                                'volume_nittou'     => '日東')
                            );

    /**
     * 共配便得意先マスタ
     */
    public static function getShareClient($type, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        $res    = array();
        $list   = array_flip(self::$code_list[$type]);

        // 項目
        $stmt = \DB::select()
        ->from('m_share_client')
        ->where('dispatch_code', $type)
        ->order_by('client_code', 'ASC')
        ->execute($db)->as_array();

        if (!empty($stmt)) {
            foreach ($stmt as $key => $val) {
                $res[$list[$val['client_name']]] = $val['client_code'];
            }
        }
        return $res;
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

//        try {
//            \DB::start_transaction(self::$db);
//
//            // 操作ログ出力
//            $result = OpeLog::addOpeLog('DI0015', \Config::get('m_DI0015'), '', self::$db);
//            if (!$result) {
//                \Log::error(\Config::get('m_CE0007'));
//            }
//
//            \DB::commit_transaction(self::$db);
//        } catch (Exception $e) {
//            // トランザクションクエリをロールバックする
//            \DB::rollback_transaction(self::$db);
//            \Log::error($e->getMessage());
//        }

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
            case '3':   // 家具
                return array(
                    'delivery_date'     => '納品日',
                    'delivery_place'    => '納品先',
                    'pickup_date'       => '引取日',
                    'pickup_place'      => '引取先',
                    'area_code'         => '地区コード',
                    'delivery_code'     => '配送区分コード',
                    'volume_koizumi'    => '小泉',
                    'volume_anneru'     => 'アンネル',
                    'volume_dream'      => 'ドリーム',
                    'volume_other'      => 'その他',
                    'client_code'       => '得意先コード',
                    'unit'              => '単位',
                    'product_name'      => '商品名',
                    'maker_name'        => 'メーカー名',
                    'driver_name'       => 'ドライバー',
                    'car_code'          => '車両番号',
                    'car_model_code'    => '車種コード',
                    'carrier_code'      => '庸車先コード',
                    'course'            => 'コース',
                    'requester'         => '依頼者',
                    'inquiry_no'        => '問合せNo',
                    'carrier_payment'   => '庸車費用',
                    'onsite_flag'       => '現場',
                    'delivery_address'  => '納品先住所',
                    'remarks1'          => '備考1',
                    'remarks2'          => '備考2',
                    'remarks3'          => '備考3',
                    'sum_disable'       => '集計除外',
                    'disable'           => '登録除外',
                );
                break;
            case '4':   // 建材
                return array(
                    'delivery_date'     => '納品日',
                    'delivery_place'    => '納品先',
                    'pickup_date'       => '引取日',
                    'pickup_place'      => '引取先',
                    'area_code'         => '地区コード',
                    'delivery_code'     => '配送区分コード',
                    'volume_wo'         => 'WO',
                    'volume_nankai'     => '南海',
                    'volume_eidai'      => '永大',
                    'volume_aika'       => 'アイカ',
                    'volume_hokukei'    => '北恵',
                    'volume_st'         => 'ST',
                    'volume_seven'      => 'セブン',
                    'volume_asahi'      => '旭化成',
                    'volume_other'      => 'その他',
                    'client_code'       => '得意先コード',
                    'unit'              => '単位',
                    'product_name'      => '商品名',
                    'maker_name'        => 'メーカー名',
                    'driver_name'       => 'ドライバー',
                    'car_code'          => '車両番号',
                    'car_model_code'    => '車種コード',
                    'carrier_code'      => '庸車先コード',
                    'course'            => 'コース',
                    'requester'         => '依頼者',
                    'inquiry_no'        => '問合せNo',
                    'carrier_payment'   => '庸車費用',
                    'onsite_flag'       => '現場',
                    'delivery_address'  => '納品先住所',
                    'remarks1'          => '備考1',
                    'remarks2'          => '備考2',
                    'remarks3'          => '備考3',
                    'sum_disable'       => '集計除外',
                    'disable'           => '登録除外',
                );
                break;
            case '5':   // 電材
                return array(
                    'delivery_date'     => '納品日',
                    'delivery_place'    => '納品先',
                    'pickup_date'       => '引取日',
                    'pickup_place'      => '引取先',
                    'area_code'         => '地区コード',
                    'delivery_code'     => '配送区分コード',
                    'volume_panasonic'  => 'パナソニック電工',
                    'volume_o-derikku'  => 'オーデリック',
                    'volume_koizumi'    => 'コイズミ照明',
                    'volume_daikou'     => '大光電機',
                    'volume_iwasaki'    => '岩崎電機',
                    'volume_tousiba'    => '東芝ライテック',
                    'volume_nec'        => 'NEC',
                    'volume_taiya'      => 'タイヤ',
                    'volume_nittou'     => '日東',
                    'volume_other'      => 'その他',
                    'client_code'       => '得意先コード',
                    'unit'              => '単位',
                    'product_name'      => '商品名',
                    'maker_name'        => 'メーカー名',
                    'driver_name'       => 'ドライバー',
                    'car_code'          => '車両番号',
                    'car_model_code'    => '車種コード',
                    'carrier_code'      => '庸車先コード',
                    'course'            => 'コース',
                    'requester'         => '依頼者',
                    'inquiry_no'        => '問合せNo',
                    'carrier_payment'   => '庸車費用',
                    'onsite_flag'       => '現場',
                    'delivery_address'  => '納品先住所',
                    'remarks1'          => '備考1',
                    'remarks2'          => '備考2',
                    'remarks3'          => '備考3',
                    'sum_disable'       => '集計除外',
                    'disable'           => '登録除外',
                );
                break;
            case '1':   // 未分類
            case '2':   // BS
            default:
                return array(
                    'delivery_date'     => '納品日',
                    'delivery_place'    => '納品先',
                    'pickup_date'       => '引取日',
                    'pickup_place'      => '引取先',
                    'area_code'         => '地区コード',
                    'delivery_code'     => '配送区分コード',
                    'client_code'       => '得意先コード',
                    'volume'            => '数量',
                    'unit'              => '単位',
                    'product_name'      => '商品名',
                    'maker_name'        => 'メーカー名',
                    'driver_name'       => 'ドライバー',
                    'car_code'          => '車両番号',
                    'car_model_code'    => '車種コード',
                    'carrier_code'      => '庸車先コード',
                    'course'            => 'コース',
                    'requester'         => '依頼者',
                    'inquiry_no'        => '問合せNo',
                    'carrier_payment'   => '庸車費用',
                    'onsite_flag'       => '現場',
                    'delivery_address'  => '納品先住所',
                    'remarks1'          => '備考1',
                    'remarks2'          => '備考2',
                    'remarks3'          => '備考3',
                    'sum_disable'       => '集計除外',
                    'disable'           => '登録除外',
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
    //単位名称から単位コードを取得して返す
    public static function getUnitCode($unit, $db) {
        $unit_code = -1;
        $unit_list = GenerateList::getUnitList(false, $db);
        
        foreach ($unit_list as $code => $name) {
            if ($name == $unit)$unit_code = $code;
        }
        
        return $unit_code;
    }

    /**
     * データインポートする
     */
    public static function import($insert_or_update, $rows, $type, $division, &$error_msg, $db) {

        \Config::load('message');
        $client_list    = self::getShareClient($type, $db);
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
                    // var_dump($errs);exit;
                    $error_msg = \Config::get('m_CW0020');
                    return false;
                    // $errors[0] = $errs;
                    // return $errors;
                }
            }
            $new_row = self::getConvertData($row, $type);
            
            //単位のコード変換（単位は名称で読み込まれるため）
            $new_row["unit_code"] = self::getUnitCode($new_row["unit"], $db);

            /**
             * バリデーション
             */
            // 除外フラグ
            if (!empty($new_row['disable']) && $new_row['disable'] == '○') {
                continue;
            }
            
            //値チェック
            list ($errs, $is_insert, $client_code, $volume) = self::validDispatchShare($is_insert_only, $new_row, $type, $division, $db);
            
            //重複チェック
            if (empty($errs) && $new_row['disable'] != '登録') {
                $errs = self::existsDispatchShare($new_row, $type, $client_code, $volume, $db);
            }
            
            if (!empty($errs)) {
                $errors[$row_no] = $errs;
                continue;
            } else {
                // 得意先コード反映？
                // switch ($type) {
                //     case '4':   // 建材
                //         foreach ($new_row as $key => $val) {
                //             switch ($key) {
                //                 case 'volume_wo':
                //                 case 'volume_nankai':
                //                 case 'volume_eidai':
                //                 case 'volume_aika':
                //                 case 'volume_hokukei':
                //                 case 'volume_st':
                //                 case 'volume_seven':
                //                 case 'volume_asahi':
                //                     if (!empty($new_row[$key]) && is_numeric($new_row[$key])) {
                //                         $new_row['client_code']    = $client_list[$key];
                //                     }
                //                     break;
                //                 case 'volume_other':
                //                     break;
                //             }
                //         }
                //         break;
                //     case '1':   // 未分類
                //     case '2':   // BS
                //     case '3':   // 家具
                //     case '5':   // 電材
                //     default:
                //         break;
                // }
            }
            /**
             * オプション登録
             */
            \DB::start_transaction($db);
            try {
                $new_row['disable'] = self::setDispatchShare($new_row, $type, $division, $db);
                unset($new_row["unit_code"]);
                $errors[$row_no] = array_merge($new_row, array('登録完了'));

                \DB::commit_transaction($db);
            } catch (\Exception $e) {
                unset($new_row["unit_code"]);
                $errors[$row_no] = array_merge($new_row, array(\Config::get('m_DW0030')));
                \DB::rollback_transaction($db);
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0026', \Config::get('m_DI0026'), '配車共配便一括登録', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
        }

        return $errors;
    }

    // バリデーション
    public static function validDispatchShare($is_insert_only, $data, $type, $division, $db) {

        \Config::load('message');
        $errors         = array();
        $msg            = array();
        $is_insert      = true;
        $volume         = 0;
        $volume_cnt     = 0;
        $require_flg    = false;
        $header         = self::getCsvColumns($type);
        $client_list    = self::getShareClient($type, $db);

        // 課コード
        $error_column = '課コード';
        if (trim($division) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($division)) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($division) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_DW0024'));
            }
        }
        // 配車区分コード
        $error_column = '配車区分コード';
        if (empty($type)) {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($type)) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($type) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_DW0024'));
            }
        }
        // 配送区分コード
        $error_column = '配送区分コード';
        if (trim($data['delivery_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($data['delivery_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['delivery_code']) > 2) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 2), \Config::get('m_DW0024'));
            }
        }
        // 地区コード
        $error_column = '地区コード';
        if (trim($data['area_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($data['area_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['area_code']) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_DW0024'));
            }
        }
        // コース
        switch ($type) {
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '4':   // 建材
            case '5':   // 電材
            default:
                $error_column = 'コース';
                if (trim($data['course']) != '') {
                    if (mb_strlen($data['course']) > 5) {
                        $msg[]                  = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_DW0024'));
                    }
                }
                break;
        }
        // 納品日＆引取日
        $error_column = '';
        if (trim($data['delivery_date']) == '' && trim($data['pickup_date']) == '') {
            $msg[]                          = \Config::get('m_DW0027');
        } else {
            if (trim($data['delivery_date']) != '' && trim($data['pickup_date']) == '') {
                if (false === self::valid_date_format(str_replace('/', '-', $data['delivery_date']))) {
                    $msg[]                  = str_replace('XXXXX','納品日',\Config::get('m_DW0025'));
                }
            }
            if (trim($data['delivery_date']) == '' && trim($data['pickup_date']) != '') {
                if (false === self::valid_date_format(str_replace('/', '-', $data['pickup_date']))) {
                    $msg[]                  = str_replace('XXXXX','引取日',\Config::get('m_DW0025'));
                }
            }
            if (trim($data['delivery_date']) != '' && trim($data['pickup_date']) != '') {
                if (false === self::valid_date_format(str_replace('/', '-', $data['delivery_date']))) {
                    $msg[]                  = str_replace('XXXXX','納品日',\Config::get('m_DW0025'));
                }
                if (false === self::valid_date_format(str_replace('/', '-', $data['pickup_date']))) {
                    $msg[]                  = str_replace('XXXXX','引取日',\Config::get('m_DW0025'));
                }
            }
        }
        // 納品先
        $error_column = '納品先';
        if (trim($data['delivery_place']) != '') {
            if (mb_strlen($data['delivery_place']) > 30) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 30), \Config::get('m_DW0024'));
            }
        }
        // 引取先
        $error_column = '引取先';
        if (trim($data['pickup_place']) != '') {
            if (mb_strlen($data['pickup_place']) > 30) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 30), \Config::get('m_DW0024'));
            }
        }
        // 数量
        $error_column = '数量';
        switch ($type) {
            case '3':   // 家具
                if (empty($data['volume_koizumi']) && empty($data['volume_anneru']) && empty($data['volume_dream']) && empty($data['volume_other'])) {
                    $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
                } else {
                    foreach ($data as $key => $val) {
                        switch ($key) {
                            case 'volume_koizumi';
                            case 'volume_anneru';
                            case 'volume_dream';
                                if (!empty($data[$key])) {
                                    if (isset($client_list[$key])) {
                                        $data['client_code'] = $client_list[$key];
                                    }
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                            case 'volume_other':
                                if (!empty($data[$key])) {
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $require_flg = true;
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                        }
                    }
                    if ($volume_cnt > 1) {
                        $msg[]                  = \Config::get('m_DW0028');
                    }
                }
                break;
            case '4':   // 建材
                if (empty($data['volume_wo']) && empty($data['volume_nankai']) && empty($data['volume_eidai']) && empty($data['volume_aika']) && empty($data['volume_hokukei']) && empty($data['volume_st']) && empty($data['volume_seven']) && empty($data['volume_asahi']) && empty($data['volume_other'])) {
                    $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
                } else {
                    foreach ($data as $key => $val) {
                        switch ($key) {
                            case 'volume_wo':
                            case 'volume_nankai':
                            case 'volume_eidai':
                            case 'volume_aika':
                            case 'volume_hokukei':
                            case 'volume_st':
                            case 'volume_seven':
                            case 'volume_asahi':
                                if (!empty($data[$key])) {
                                    if (isset($client_list[$key])) {
                                        $data['client_code'] = $client_list[$key];
                                    }
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                            case 'volume_other':
                                if (!empty($data[$key])) {
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $require_flg = true;
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                        }
                    }
                    if ($volume_cnt > 1) {
                        $msg[]                  = \Config::get('m_DW0028');
                    }
                }
                break;
            case '5':   // 電材
                if (empty($data['volume_panasonic']) && empty($data['volume_o-derikku']) && empty($data['volume_koizumi']) && empty($data['volume_daikou']) && empty($data['volume_iwasaki']) && empty($data['volume_tousiba']) && empty($data['volume_nec']) && empty($data['volume_taiya']) && empty($data['volume_nittou']) && empty($data['volume_other'])) {
                    $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
                } else {
                    foreach ($data as $key => $val) {
                        switch ($key) {
                            case 'volume_panasonic';
                            case 'volume_o-derikku';
                            case 'volume_koizumi';
                            case 'volume_daikou';
                            case 'volume_iwasaki';
                            case 'volume_tousiba';
                            case 'volume_nec';
                            case 'volume_taiya';
                            case 'volume_nittou';
                                if (!empty($data[$key])) {
                                    if (isset($client_list[$key])) {
                                        $data['client_code'] = $client_list[$key];
                                    }
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                            case 'volume_other':
                                if (!empty($data[$key])) {
                                    if (!is_numeric($data[$key])) {
                                        $msg[]  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                                    }
                                    $require_flg = true;
                                    $volume = $val;
                                    $volume_cnt++;
                                }
                                break;
                        }
                    }
                    if ($volume_cnt > 1) {
                        $msg[]                  = \Config::get('m_DW0028');
                    }
                }
                break;
            case '1':   // 未分類
            case '2':   // BS
            default:
                // 家具・建材・電材以外の場合
                if (trim($data['volume']) == '') {
                    $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
                } else {
                    if (!is_numeric($data['volume'])) {
                        $msg[]                  = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                    }
                }
                $volume = $data['volume'];
                break;
        }
        // 得意先コード
        $error_column = '得意先コード';
        switch ($type) {
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '4':   // 建材
            case '5':   // 電材
            default:
                if (trim($data['client_code']) == '') {
                    $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
                } else {
                    if (!is_numeric($data['client_code'])) {
                        $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
                    }
                    if (mb_strlen($data['client_code']) > 5) {
                        $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_DW0024'));
                    }
                }
                break;
        }
        // 商品名
        $error_column = '商品名';
        if (trim($data['product_name']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (mb_strlen($data['product_name']) > 30) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 30), \Config::get('m_DW0024'));
            }
        }
        // メーカー名
        $error_column = 'メーカー名';
        if (trim($data['maker_name']) != '') {
            if (mb_strlen($data['maker_name']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
            }
        }
        // 単位コード
        $error_column = '単位';
        if (trim($data['unit']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if ($data['unit_code'] == -1) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0038'));
            }
        }
        // 車種コード
        $error_column = '車種コード';
        if (trim($data['car_model_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($data['car_model_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['car_model_code']) > 3) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 3), \Config::get('m_DW0024'));
            }
        }
        // 車両番号
        $error_column = '車両番号';
        if (trim($data['car_code']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($data['car_code'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['car_code']) > 4) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 4), \Config::get('m_DW0024'));
            }
        }
//        // 社員コード
//        $error_column = '社員コード';
//        if (trim($data['member_code']) != '') {
//            if (!is_numeric($data['member_code'])) {
//                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
//            }
//            if (mb_strlen($data['member_code']) > 5) {
//                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_DW0024'));
//            }
//        }
        // ドライバー
        $error_column = 'ドライバー';
        if (trim($data['driver_name']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (mb_strlen($data['driver_name']) > 6) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 6), \Config::get('m_DW0024'));
            }
        }
        // 庸車先コード
        $error_column = '庸車先コード';
        if (trim($data['carrier_code']) == '') {
            $msg[]                           = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!is_numeric($data['carrier_code'])) {
                $msg[]                       = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['carrier_code']) > 5) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 5), \Config::get('m_DW0024'));
            }
        }
        // 庸車費用
        $error_column = '庸車費用';
        if (trim($data['carrier_payment']) != '') {
            if (!is_numeric($data['carrier_payment'])) {
                $msg[]                      = str_replace('XXXXX',$error_column, \Config::get('m_DW0026'));
            }
            if (mb_strlen($data['carrier_payment']) > 8) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 8), \Config::get('m_DW0024'));
            }
        }
        // 依頼者
        switch ($type) {
            case '4':   // 建材
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '5':   // 電材
            default:
                $error_column = '依頼者';
                if (trim($data['requester']) != '') {
                    if (mb_strlen($data['requester']) > 15) {
                    $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
                    }
                }
                break;
        }
        // 問合せNo
        switch ($type) {
            case '4':   // 建材
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '5':   // 電材
            default:
                $error_column = '問い合わせNo';
                if (trim($data['inquiry_no']) != '') {
                    if (mb_strlen($data['inquiry_no']) > 15) {
                    $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
                    }
                }
                break;
        }
        // 現場フラグ
        $error_column = '現場';
        if (trim($data['onsite_flag']) == '') {
            $msg[]                          = str_replace('XXXXX',$error_column, \Config::get('m_DW0022'));
        } else {
            if (!preg_match('/[0-1]/', $data['onsite_flag'], $m)) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, '0もしくは1'), \Config::get('m_DW0040'));
            }
        }
        // 納品先住所
        $error_column = '納品先住所';
        if (trim($data['delivery_address']) != '') {
            if (mb_strlen($data['delivery_address']) > 40) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 40), \Config::get('m_DW0024'));
            }
        }
        // 備考1
        $error_column = '備考1';
        if (trim($data['remarks1']) != '') {
            if (mb_strlen($data['remarks1']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
            }
        }
        // 備考2
        $error_column = '備考2';
        if (trim($data['remarks2']) != '') {
            if (mb_strlen($data['remarks2']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
            }
        }
        // 備考3
        $error_column = '備考3';
        if (trim($data['remarks3']) != '') {
            if (mb_strlen($data['remarks3']) > 15) {
                $msg[]                      = str_replace(array('XXXXX', 'xxxxx'),array($error_column, 15), \Config::get('m_DW0024'));
            }
        }

        if (!empty($msg)) {
            unset($data["unit_code"]);
            $errors = array_merge($data, array(implode("\r\n", $msg)));
        }

        return array($errors, $is_insert, $data['client_code'], $volume);
    }
    
    // レコード存在チェック
    public static function existsDispatchShare($data, $type, $client_code, $volume, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 項目
        $stmt = \DB::select(\DB::expr('COUNT(t.dispatch_number) AS count'));
        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));
        // 配車区分
        $stmt->where('t.dispatch_code', '=', $type);
        // 地区
        $stmt->where('t.area_code', '=', $data['area_code']);
        // 納品日
        if (!empty($data['delivery_date']) && trim($data['delivery_date']) != '') {
            $stmt->where('t.delivery_date', '=', $data['delivery_date']);
        }
        // 引取日
        if (!empty($data['pickup_date']) && trim($data['pickup_date']) != '') {
            $stmt->where('t.pickup_date', '=', $data['pickup_date']);
        }
        // 納品先
        if (!empty($data['delivery_place']) && trim($data['delivery_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), '=', $data['delivery_place']);
        }
        // 引取先
        if (!empty($data['pickup_place']) && trim($data['pickup_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), '=', $data['pickup_place']);
        }
        // 得意先
        $stmt->where('t.client_code', '=', $client_code);
        // 商品
        $stmt->where('t.product_name', '=', $data['product_name']);
        // 数量
        $stmt->where('t.volume', '=', $volume);
        $stmt->where('t.delete_flag', '=', '0');
        
        $res = $stmt->execute($db)->as_array();
        
        if ($res[0]['count'] > 0) {
            unset($data["unit_code"]);
            return array_merge($data, array(\Config::get('m_DW0039')));
        }
        return array();
    }

    public static function setDispatchShare($data, $type, $division, $db=null) {

        \Config::load('message');
        $client_list = self::getShareClient($type, $db);
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 課コード
        $ins_data['division_code']              = $division;
        // 配車区分コード
        $ins_data['dispatch_code']              = $type;
        // 配送区分コード
        $ins_data['delivery_code']              = $data['delivery_code'];
        // 地区コード
        $ins_data['area_code']                  = $data['area_code'];
        // コース
        switch ($type) {
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '4':   // 建材
            case '5':   // 電材
            default:
                if (trim($data['course']) != '') {
                    $ins_data['course']         = $data['course'];
                }
                break;
        }
        // 納品日＆引取日
        if (trim($data['delivery_date']) != '' && trim($data['pickup_date']) == '') {
            $ins_data['delivery_date']          = date('Y-m-d', strtotime($data['delivery_date']));
        }
        if (trim($data['delivery_date']) == '' && trim($data['pickup_date']) != '') {
            $ins_data['pickup_date']            = date('Y-m-d', strtotime($data['pickup_date']));
        }
        if (trim($data['delivery_date']) != '' && trim($data['pickup_date']) != '') {
            $ins_data['delivery_date']          = date('Y-m-d', strtotime($data['delivery_date']));
            $ins_data['pickup_date']            = date('Y-m-d', strtotime($data['pickup_date']));
        }
        // 納品先
        if (trim($data['delivery_place']) != '') {
            $ins_data['delivery_place']         = \DB::expr('HEX(AES_ENCRYPT("'.$data['delivery_place'].'","'.$encrypt_key.'"))');
        }
        // 引取先
        if (trim($data['pickup_place']) != '') {
            $ins_data['pickup_place']           = \DB::expr('HEX(AES_ENCRYPT("'.$data['pickup_place'].'","'.$encrypt_key.'"))');
        }
        // 数量
        $volume = 0;
        switch ($type) {
            case '3':   // 家具
                foreach ($data as $key => $val) {
                    switch ($key) {
                        case 'volume_koizumi';
                        case 'volume_anneru';
                        case 'volume_dream';
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $data['client_code']    = $client_list[$key];
                                $volume                 = $val;
                            }
                            break;
                        case 'volume_other':
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $volume                 = $val;
                            }
                            break;
                    }
                }
                break;
            case '4':   // 建材
                foreach ($data as $key => $val) {
                    switch ($key) {
                        case 'volume_wo':
                        case 'volume_nankai':
                        case 'volume_eidai':
                        case 'volume_aika':
                        case 'volume_hokukei':
                        case 'volume_st':
                        case 'volume_seven':
                        case 'volume_asahi':
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $data['client_code']    = $client_list[$key];
                                $volume                 = $val;
                            }
                            break;
                        case 'volume_other':
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $volume                 = $val;
                            }
                            break;
                    }
                }
                break;
            case '5':   // 電材
                foreach ($data as $key => $val) {
                    switch ($key) {
                        case 'volume_panasonic';
                        case 'volume_o-derikku';
                        case 'volume_koizumi';
                        case 'volume_daikou';
                        case 'volume_iwasaki';
                        case 'volume_tousiba';
                        case 'volume_nec';
                        case 'volume_taiya';
                        case 'volume_nittou';
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $data['client_code']    = $client_list[$key];
                                $volume                 = $val;
                            }
                            break;
                        case 'volume_other':
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $volume                 = $val;
                            }
                            break;
                    }
                }
                break;
            case '1':   // 未分類
            case '2':   // BS
            default:
                $volume = $data['volume'];
                break;
        }
        $ins_data['volume']                     = $volume;
        // 得意先コード
        $ins_data['client_code']                = $data['client_code'];
        // 商品名
        $ins_data['product_name']               = $data['product_name'];
        // メーカー名
        if (trim($data['maker_name']) != '') {
            $ins_data['maker_name']             = $data['maker_name'];
        }
        // 単位コード
        $ins_data['unit_code']                  = $data['unit_code'];
        // 車種コード
        $ins_data['car_model_code']             = $data['car_model_code'];
        // 車両番号
        $ins_data['car_code']                   = $data['car_code'];
//        // 社員コード
//        if (trim($data['member_code']) != '') {
//            $ins_data['member_code']            = $data['member_code'];
//        }
        // ドライバー
        $ins_data['driver_name']                = \DB::expr('HEX(AES_ENCRYPT("'.$data['driver_name'].'","'.$encrypt_key.'"))');
        // 庸車先コード
        $ins_data['carrier_code']               = $data['carrier_code'];
        // 庸車費用
        if (trim($data['carrier_payment']) != '') {
            $ins_data['carrier_payment']        = $data['carrier_payment'];
        }
        // 依頼者
        switch ($type) {
            case '4':   // 建材
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '5':   // 電材
            default:
                if (trim($data['requester']) != '') {
                    $ins_data['requester']      = $data['requester'];
                }
                break;
        }
        // 問合せNo
        switch ($type) {
            case '4':   // 建材
            case '1':   // 未分類
            case '2':   // BS
            case '3':   // 家具
            case '5':   // 電材
            default:
                if (trim($data['inquiry_no']) != '') {
                    $ins_data['inquiry_no']     = $data['inquiry_no'];
                }
                break;
        }
        // 現場フラグ
        $ins_data['onsite_flag']                = $data['onsite_flag'];
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
            $ins_data['remarks2']               = $data['remarks2'];
        }
        // 備考3
        if (trim($data['remarks3']) != '') {
            $ins_data['remarks3']               = $data['remarks3'];
        }

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_dispatch_share')->set(array_merge($ins_data, self::getEtcData(true)))->execute($db);

        if(!$insert_id) {
            // throw new \Exception(\Config::get('m_DW0030'));
            throw new \Exception();
        }
        return '○';
    }

}