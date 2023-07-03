<?php
/**
 * responseの引数にいれるための内容を作成
 */
namespace Model\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;               // スプレッドシート用
use PhpOffice\PhpSpreadsheet\IOFactory;                 // IOFactory
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

class Data extends \Model {

    public static $db_m = 'DB_M';
    public static $db_s = 'default';

    public static $format_array = array(
                                  'xls'     => 'Excel5'
                                , 'xlsx'    => 'Excel2007'
                                , 'csv'     => 'CSV'
                                , 'tsv'     => 'TSV'
                            );

    /**
     * エクスポート用データの作成
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_salescorrection($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:T1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(15);
            $sheet->getColumnDimension('V')->setWidth(15);
            $sheet->getColumnDimension('W')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':W'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':W'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:W2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':W'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':W'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:W2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

        // $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        // $name           = tempnam('', 'excel_');
        // $writer->save($name);
        // $content        = file_get_contents($name);
        // @unlink($name);

        // return $content;

    }

    /**
     * 配車表出力（共配便）
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_dispatch_share($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:AK1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(15);
            $sheet->getColumnDimension('V')->setWidth(15);
            $sheet->getColumnDimension('W')->setWidth(15);
            $sheet->getColumnDimension('X')->setWidth(15);
            $sheet->getColumnDimension('Y')->setWidth(15);
            $sheet->getColumnDimension('Z')->setWidth(15);
            $sheet->getColumnDimension('AA')->setWidth(15);
            $sheet->getColumnDimension('AB')->setWidth(15);
            $sheet->getColumnDimension('AC')->setWidth(15);
            $sheet->getColumnDimension('AD')->setWidth(15);
            $sheet->getColumnDimension('AE')->setWidth(15);
            $sheet->getColumnDimension('AF')->setWidth(15);
            $sheet->getColumnDimension('AG')->setWidth(15);
            $sheet->getColumnDimension('AH')->setWidth(15);
            $sheet->getColumnDimension('AI')->setWidth(15);
            $sheet->getColumnDimension('AJ')->setWidth(15);
            $sheet->getColumnDimension('AK')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':AK'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':AK'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:AK2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':AK'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':AK'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:AK2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

    }
    
    /**
     * 請求情報表出力（共配便）
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_bill_share($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:AI1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(15);
            $sheet->getColumnDimension('V')->setWidth(15);
            $sheet->getColumnDimension('W')->setWidth(15);
            $sheet->getColumnDimension('X')->setWidth(15);
            $sheet->getColumnDimension('Y')->setWidth(15);
            $sheet->getColumnDimension('Z')->setWidth(15);
            $sheet->getColumnDimension('AA')->setWidth(15);
            $sheet->getColumnDimension('AB')->setWidth(15);
            $sheet->getColumnDimension('AC')->setWidth(15);
            $sheet->getColumnDimension('AD')->setWidth(15);
            $sheet->getColumnDimension('AE')->setWidth(15);
            $sheet->getColumnDimension('AF')->setWidth(15);
            $sheet->getColumnDimension('AG')->setWidth(15);
            $sheet->getColumnDimension('AH')->setWidth(15);
            $sheet->getColumnDimension('AI')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':AI'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':AI'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:AI2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':AI'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':AI'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:AI2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

    }

    // 配車表出力（チャーター便）
    public static function create_dispatch_carrying($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(41, 1, '分載レコード', \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:BN1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(15);
            $sheet->getColumnDimension('V')->setWidth(15);
            $sheet->getColumnDimension('W')->setWidth(15);
            $sheet->getColumnDimension('X')->setWidth(15);
            $sheet->getColumnDimension('Y')->setWidth(15);
            $sheet->getColumnDimension('Z')->setWidth(15);
            $sheet->getColumnDimension('AA')->setWidth(15);
            $sheet->getColumnDimension('AB')->setWidth(15);
            $sheet->getColumnDimension('AC')->setWidth(15);
            $sheet->getColumnDimension('AD')->setWidth(15);
            $sheet->getColumnDimension('AE')->setWidth(15);
            $sheet->getColumnDimension('AF')->setWidth(15);
            $sheet->getColumnDimension('AG')->setWidth(15);
            $sheet->getColumnDimension('AH')->setWidth(15);
            $sheet->getColumnDimension('AI')->setWidth(15);
            $sheet->getColumnDimension('AJ')->setWidth(15);
            $sheet->getColumnDimension('AK')->setWidth(15);
            $sheet->getColumnDimension('AL')->setWidth(15);
            $sheet->getColumnDimension('AM')->setWidth(15);
            $sheet->getColumnDimension('AN')->setWidth(15);
            $sheet->getColumnDimension('AO')->setWidth(15);
            // 分載位置
            $sheet->getColumnDimension('AP')->setWidth(15);
            $sheet->getColumnDimension('AQ')->setWidth(15);
            $sheet->getColumnDimension('AR')->setWidth(15);
            $sheet->getColumnDimension('AS')->setWidth(15);
            $sheet->getColumnDimension('AT')->setWidth(15);
            $sheet->getColumnDimension('AU')->setWidth(15);
            $sheet->getColumnDimension('AV')->setWidth(15);
            $sheet->getColumnDimension('AW')->setWidth(15);
            $sheet->getColumnDimension('AX')->setWidth(15);
            $sheet->getColumnDimension('AY')->setWidth(15);
            $sheet->getColumnDimension('AZ')->setWidth(15);
            $sheet->getColumnDimension('BA')->setWidth(15);
            $sheet->getColumnDimension('BB')->setWidth(15);
            $sheet->getColumnDimension('BC')->setWidth(15);
            $sheet->getColumnDimension('BD')->setWidth(15);
            $sheet->getColumnDimension('BE')->setWidth(15);
            $sheet->getColumnDimension('BF')->setWidth(15);
            $sheet->getColumnDimension('BG')->setWidth(15);
            $sheet->getColumnDimension('BH')->setWidth(15);
            $sheet->getColumnDimension('BI')->setWidth(15);
            $sheet->getColumnDimension('BJ')->setWidth(15);
            $sheet->getColumnDimension('BK')->setWidth(15);
            $sheet->getColumnDimension('BL')->setWidth(15);
            $sheet->getColumnDimension('BM')->setWidth(15);
            $sheet->getColumnDimension('BN')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':BN'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':BN'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            if ($col_no > 40) {
                                // 背景色
                                $sheet->getStyle('AP2:BN2')
                                    ->getFill()
                                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('98fb98')
                                ;
                            } else {
                                // 背景色
                                $sheet->getStyle('A2:AO2')
                                    ->getFill()
                                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('e0ffff')
                                ;
                            }
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':BN'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':BN'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            if ($col_no > 40) {
                                // 背景色
                                $sheet->getStyle('AP2:BN2')
                                    ->getFill()
                                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('98fb98')
                                ;
                            } else {
                                // 背景色
                                $sheet->getStyle('A2:AO2')
                                    ->getFill()
                                    ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('e0ffff')
                                ;
                            }
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

        // $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        // $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        // $name           = tempnam('', 'excel_');
        // $writer->save($name);
        // $content        = file_get_contents($name);
        // @unlink($name);

        // return $content;

    }
    
    /**
     * 在庫一覧表出力
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_stock($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:R1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':N'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':N'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:N2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':N'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':N'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:N2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

    }
    
    /**
     * 入出庫一覧表出力
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_stock_change($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:R1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':R'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':R'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:R2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':R'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':R'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:R2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

    }

    /**
     * 保管料情報一覧表出力
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $title         : ワークシートのタイトル
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function create_storage_fee($version='xlsx', $title='', $sheet_title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            if (!empty($sheet_title)) {
                $sheet->setCellValueExplicitByColumnAndRow(0, 1, $sheet_title, \PHPExcel_Cell_DataType::TYPE_STRING);
                // 文字装飾
                $sheet->getStyle('A1:T1')
                    ->getFont()
                    ->setName('游ゴシック Regular (本文)')
                    ->setSize(14)
                ;
            }
            // 列の幅設定
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(15);
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);

            // 行の繰り返し
            $row_no = 2; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '')
                                ->getStyle('A'.$row_no.':T'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':T'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:T2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING)
                                ->getStyle('A'.$row_no.':T'.$row_no)
                                ->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                            ;
                            // 文字装飾
                            $sheet->getStyle('A'.$row_no.':T'.$row_no)
                                ->getFont()
                                ->setName('游ゴシック Regular (本文)')
                                ->setSize(11)
                            ;
                            // 背景色
                            $sheet->getStyle('A2:T2')
                                ->getFill()
                                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('e0ffff')
                            ;
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.'.$version.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save('php://output');
        exit;
        return true;

    }

    public static function create($version='xlsx', $title='', $fpath='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new \PHPExcel;
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {
            // $sheet->fromArray($data, null, 'A1');
            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, \PHPExcel_Cell_DataType::TYPE_STRING);
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = \PHPExcel_Cell::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        $writer         = \PHPExcel_IOFactory::createWriter($excel, $format_name);
        $writer->save($fpath);
        return true;

    }

    public static function create3($version='xlsx', $title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            $col_no = 1; // カラム番号は0から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    if (is_null($val1) || trim($val1) == '') {
                        // セルに書き込み
                        $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                    } else {
                        // セルに書き込み
                        $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val1, DataType::TYPE_STRING);
                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = Coordinate::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        $writer         = IOFactory::createWriter($excel, $format_name);
        $name           = tempnam('', 'excel_');
        $writer->save($name);
        $content        = file_get_contents($name);
        @unlink($name);

        return $content;

    }

    public static function create_utf8($version='xlsx', $title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 1; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, mb_convert_encoding($val2, 'UTF-8'), DataType::TYPE_STRING);
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = Coordinate::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        $writer         = IOFactory::createWriter($excel, $format_name);
        $name           = tempnam('', 'excel_');
        $writer->save($name);
        $content        = file_get_contents($name);
        @unlink($name);

        return $content;

    }

    public static function create_sjis($version='xlsx', $title='', $data=array()) {

        /**
         * Excel作成
         */
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle($title);    // シートタイトル

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 1; // 行番号は1から
            foreach ($data as $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 0; // カラム番号は0から
                    foreach ($val1 as $val2) {

                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, mb_convert_encoding($val2, 'Shift_JIS', 'UTF-8'), DataType::TYPE_STRING);
                        }

                        $col_no++;

                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = Coordinate::stringFromColumnIndex($col_no - 1);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        }

        // Excelデータの作成
        $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        $writer         = IOFactory::createWriter($excel, $format_name);
        $name           = tempnam('', 'excel_');
        $writer->save($name);
        $content        = file_get_contents($name);
        @unlink($name);

        return $content;

    }

    public static function create_csv($version='csv', $title='', $fpath, $data=array()) {

        switch ($version) {
            case 'csv':
                $content = Data::create_sjis($version, $title, $data);
                $fname   = $fpath;
                if (false === $fp = fopen($fname, 'wb')) {
                    throw new \Exception('ファイルの作成('.$fname.')に失敗しました');
                }
                fwrite($fp, $content);
                fclose($fp);
                return $fname;
                break;
            case 'tsv':
                $fname   = $fpath;

                //文字コード変換
                // $title = mb_convert_encoding($title, 'SJIS-win', 'UTF-8');
                // mb_convert_variables('SJIS-win', 'UTF-8', $data);

                //ファイルへの書き込み
                //「php://output」に出力することで、一時ファイルを作成せずに書き込みができる。
                // $handle = fopen('php://temp', 'wb');
                $handle = fopen($fname, 'w+b');
                foreach ($data as $key => $value) {
                    // $tmp[] = fputcsv($handle, $value, "\t").PHP_EOL;
                    fputcsv($handle, $value, "\t").PHP_EOL;
                }
                // $content = str_replace(PHP_EOL, "\r\n", substr($tmp[0], 0, strrpos($tmp[0], "\n")));
                rewind($handle);
                fclose($handle);
                return $fname;

                break;
            default:
                break;
        }

        return false;
    }

    /**
     * アウトプット
     */
    public static function output_body($version='csv', $title='', $fpath, $data=array(), $multi_flg=null) {

        switch ($version) {
            case 'csv':
            case 'tsv':
                return self::create_csv($version, $title, $fpath, $data);
                break;
            default:
                if (is_null($multi_flg)) {
                    return self::create2($version, $title, $fpath, $data);
                } else {
                    return self::create_multi_sheet($version, $title, $fpath, $data);
                }
                break;
        }

        return false;
    }

    /**
     * 既存のExcelデータからセル内容を取得し連想配列で返す
     *
     * @param
     * $file         : ファイル名（パス含む）
     *
     * @return
     * $excel_type   : xls or xlsx
     * $header       : ヘッダ
     * $data[行][列] : array(array(値,値,..),array(値,値,..),..)
     *
     */
    public static function import($file) {

        /**
         * 返り値
         */
        $res = array('excel_type' => 'xls', 'header' => array(), 'data' => array());

        /**
         * データから読み込み
         */
        foreach (self::$format_array as $format_name) {
            try {

                $obj  = \PHPExcel_IOFactory::createReader($format_name);
                $book = $obj->load($file);

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
                    if (\PHPExcel_Cell_DataType::TYPE_NUMERIC == $datatype) {
                        if (false !== strpos($format, 'h') &&
                            false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
                            $is_datetime = true;
                        } else if (false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
                            $is_date = true;
                        }
                    }
                    if ($is_datetime) {
                        // yyyy-mm-dd
                        $val = (string)\PHPExcel_Style_NumberFormat::toFormattedString(
                                                                      $sheet->getCellByColumnAndRow($j, $i)->getValue()
                                                                    , \PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME2
                                                                    );
                    } else if ($is_date) {
                        // yyyy-mm-dd
                        $val = (string)\PHPExcel_Style_NumberFormat::toFormattedString(
                                                                      $sheet->getCellByColumnAndRow($j, $i)->getValue()
                                                                    , \PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
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

    /**
     * 既存のExcelデータからセル内容を取得し連想配列で返す
     *
     * @param
     * $file         : ファイル名（パス含む）
     *
     * @return
     * $excel_type   : xls or xlsx
     * $header       : ヘッダ
     * $data[行][列] : array(array(値,値,..),array(値,値,..),..)
     *
     */
    // public static function import_sp($file) {

    //     // タイムアウトを一時的に解除
    //     ini_set('max_execution_time', 0);
    //     // 最大メモリー数を増幅
    //     ini_set('memory_limit', '5120M');
    //     // MySQLの接続タイム設定
    //     ini_set('mysql.connect_timeout', 300);
    //     ini_set('default_socket_timeout', 300);

    //     /**
    //      * 返り値
    //      */
    //     $res = array('excel_type' => 'xls', 'header' => array(), 'data' => array());

    //     /**
    //      * データから読み込み
    //      */
    //     foreach (self::$format_array as $format_name) {
    //         try {
    //             $obj  = IOFactory::createReader($format_name);
    //             $book = $obj->load($file);
    //         } catch (\Exception $e) {
    //             $book = null;
    //         }
    //         unset($obj);
    //         if (is_object($book)) {
    //             break;
    //         }
    //     }

    //     if ($book) {

    //         // Excel種類を設定
    //         $type = array_flip(self::$format_array);
    //         $res['excel_type'] = $type[$format_name];

    //         // シート設定
    //         $book->setActiveSheetIndex(0);
    //         $sheet          = $book->getActiveSheet();

    //         $lastrow        = $sheet->getHighestRow();                          //200などの数字
    //         $lastcolname    = $sheet->getHighestColumn();                       //AZなどの文字列
    //         $lastcol        = Coordinate::columnIndexFromString($lastcolname);  //これで数字になる

    //         // ヘッダを取得
    //         $header         = array();
    //         $body           = array();
    //         $row            = 0;
    //         foreach($sheet->getRowIterator() as $eachRow) {
    //             $cell       = array();
    //             $cnt        = 0;
    //             foreach($sheet->getColumnIterator() as $column) {
    //                 if (!empty((string)$sheet->getCell($column->getColumnIndex() . $row)->getValue())) {
    //                     // ヘッダー取得
    //                     if ($row == 1) {
    //                         $header[]   = (string)$sheet->getCell($column->getColumnIndex() . $row)->getValue();
    //                         $cnt++;
    //                     }
    //                 }
    //             }
    //             $row++;
    //         }

    //         $row            = 2;
    //         foreach($sheet->getRowIterator() as $eachRow) {
    //             $cellIterator   = $eachRow->getCellIterator();
    //             // $cellIterator->setIterateOnlyExistingCells(false);              //空セルも取得するようにする
    //             if ($eachRow->getRowIndex() < $row) {
    //                 continue;
    //             }
    //             $cell       = array();
    //             $cnt        = 0;
    //             foreach($sheet->getColumnIterator() as $column) {
    //                 // ヘッダーの数だけループ
    //                 if (isset($header[$cnt])) {
    //                     // まずデータタイプを取得
    //                     $datatype       = $sheet->getCell($column->getColumnIndex() . $row)->getDataType();
    //                     $format         = $sheet->getCell($column->getColumnIndex() . $row)->getStyle()->getNumberFormat()->getFormatCode();
    //                     $is_datetime    = false;
    //                     $is_date        = false;
    //                     $is_time        = false;
    //                     if (DataType::TYPE_NUMERIC == $datatype) {
    //                         if (false !== strpos($format, 'h') &&
    //                             false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
    //                             $is_datetime = true;
    //                         } else if (false !== strpos($format, 'y') && false !== strpos($format, 'm') && false !== strpos($format, 'd')) {
    //                             $is_date = true;
    //                         } else if (false !== strpos($format, 'h') && false !== strpos($format, 'mm')) {
    //                             $is_time = true;
    //                         }
    //                     }
    //                     if ($is_datetime) {
    //                         // yyyy-mm-dd
    //                         $cell[$header[$cnt]] = (string)NumberFormat::toFormattedString(
    //                                                                       $sheet->getCell($column->getColumnIndex() . $row)->getValue()
    //                                                                     , NumberFormat::FORMAT_DATE_DATETIME2
    //                                                                     );
    //                     } else if ($is_date) {
    //                         // yyyy-mm-dd
    //                         $cell[$header[$cnt]] = (string)NumberFormat::toFormattedString(
    //                                                                       $sheet->getCell($column->getColumnIndex() . $row)->getValue()
    //                                                                     , NumberFormat::FORMAT_DATE_YYYYMMDD2
    //                                                                     );
    //                     } else if ($is_time) {
    //                         // hh:mm:ss
    //                         $cell[$header[$cnt]] = (string)NumberFormat::toFormattedString(
    //                                                                       $sheet->getCell($column->getColumnIndex() . $row)->getValue()
    //                                                                     , ($format == 'h:mm:ss') ? NumberFormat::FORMAT_DATE_TIME6:NumberFormat::FORMAT_DATE_TIME9
    //                                                                     );
    //                     } else {
    //                         $cell[$header[$cnt]] = (string)$sheet->getCell($column->getColumnIndex() . $row)->getValue();
    //                     }
    //                     $cnt++;
    //                 }

    //             }
    //             if (!empty($cell)) {
    //                 $body[] = $cell;
    //             }
    //             $row++;
    //         }

    //         if (!empty($header)) {

    //             $res['header'] = $header;

    //         }
    //         if (!empty($body)) {

    //             $res['data'] = $body;

    //         }

    //     }
    //     return $res;

    // }

}