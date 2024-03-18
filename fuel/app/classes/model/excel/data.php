<?php
/**
 * responseの引数にいれるための内容を作成
 */
namespace Model\Excel;
use \Model\Common\GenerateList;
use \Model\Common\BarcodeConfig;

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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;         // 画像操作
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;           // セル用
use PhpOffice\PhpSpreadsheet\Cell\DataType;             // セルフォーマット用
// use PhpOffice\PhpSpreadsheet\IOFactory as Fact;         // スプレッドシート用ファイル操作クラス

// QRコード
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;

class Data extends \Model {

    public static $db           = 'MAKINO';

    public static $format_array = array(
                                  'xls'     => 'Excel5'
                                , 'xlsx'    => 'Excel2007'
                                , 'csv'     => 'CSV'
                                , 'tsv'     => 'TSV'
                            );
    /**
     * ディレクトリ検索
     * ファイルパスを返す
     */
    public static function search_dir($data = array()) {

        $search_dir = @implode('/', $data);
        return $search_dir;
    }

    /**
     * ディレクトリ作成
     * 設定したディレクトリ分階層を作成しファイルパスを返す
     */
    public static function create_dir($data, $current_path) {

        $create_dir = @implode('/', $data);
        if(!file_exists($current_path.$create_dir)){
            //存在しないときの処理
            \File::create_dir($current_path, $create_dir, 0775);
        }
        return $current_path.$create_dir;
    }

    //=========================================================================//
    //==============================   入庫シール  =============================//
    //=========================================================================//
    /**
     * [PHPExcel]シートの指定行を完全コピーする
     *
     * @param PHPExcel_Worksheet $sheet
     * @param int $loop_offset １ページのループカウント数
     * @param int $page_offset 複数ページのループカウント数
     */
    public static function setExcelReceiptStickerDesign($sheet, $val, $loop_offset = 0, $page_offset = 0, $db) {

        if (empty($page_offset)) {
            $row_no = 2;
        } else {
            $row_no = (2 + $page_offset);
        }
        // 保管場所リスト
        $location_list  = GenerateList::getLocationList(true, $db);

        // お客様名
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + $loop_offset),
            'お客様名',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + $loop_offset).':A'.($row_no + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(11)
        ;
        // 結合
        $sheet->mergeCells('A'.($row_no + 1 + $loop_offset).':L'.($row_no + 2 + $loop_offset));
        // 文字位置
        $sheet->getStyle('A'.($row_no + 1 + $loop_offset).':L'.($row_no + 2 + $loop_offset))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 罫線
        $sheet->getStyle('A'.($row_no + $loop_offset).':L'.($row_no + 2 + $loop_offset))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + 1 + $loop_offset),
            (!empty($val['customer_name'])) ? $val['customer_name'].' 様':'',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + 1 + $loop_offset).':A'.($row_no + 1 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(24)
        ;

        // 車番(登録番号)
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + 3 + $loop_offset),
            '車番',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + 3 + $loop_offset).':A'.($row_no + 3 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(11)
        ;
        // 結合
        $sheet->mergeCells('A'.($row_no + 4 + $loop_offset).':E'.($row_no + 5 + $loop_offset));
        // 文字位置
        $sheet->getStyle('A'.($row_no + 4 + $loop_offset).':E'.($row_no + 5 + $loop_offset))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 罫線
        $sheet->getStyle('A'.($row_no + 3 + $loop_offset).':E'.($row_no + 5 + $loop_offset))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        // 車番(登録番号)
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + 4 + $loop_offset),
            (!empty($val['car_code'])) ? $val['car_code']:'',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + 4 + $loop_offset).':A'.($row_no + 4 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(20)
        ;
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, ($row_no + 4 + $loop_offset))->getCoordinate())->getAlignment()->setShrinkToFit(true);

        // 車種
        $sheet->setCellValueExplicitByColumnAndRow(
            6,
            ($row_no + 3 + $loop_offset),
            '車種',
            DataType::TYPE_STRING
        )
            ->getStyle('F'.($row_no + 3 + $loop_offset).':F'.($row_no + 3 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(11)
        ;
        // 結合
        $sheet->mergeCells('F'.($row_no + 4 + $loop_offset).':L'.($row_no + 5 + $loop_offset));
        // 文字位置
        $sheet->getStyle('F'.($row_no + 4 + $loop_offset).':L'.($row_no + 5 + $loop_offset))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 罫線
        $sheet->getStyle('F'.($row_no + 3 + $loop_offset).':L'.($row_no + 5 + $loop_offset))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        // 車種
        $sheet->setCellValueExplicitByColumnAndRow(
            6,
            ($row_no + 4 + $loop_offset),
            (!empty($val['car_name'])) ? $val['car_name']:'',
            DataType::TYPE_STRING
        )
            ->getStyle('F'.($row_no + 4 + $loop_offset).':F'.($row_no + 4 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(20)
        ;
        $sheet->getStyle($sheet->getCellByColumnAndRow(6, ($row_no + 4 + $loop_offset))->getCoordinate())->getAlignment()->setShrinkToFit(true);

        // 保管場所
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + 6 + $loop_offset),
            '保管場所',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + 6 + $loop_offset).':A'.($row_no + 6 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(11)
        ;
        // 結合
        $sheet->mergeCells('A'.($row_no + 7 + $loop_offset).':D'.($row_no + 8 + $loop_offset));
        // 文字位置
        $sheet->getStyle('A'.($row_no + 7 + $loop_offset).':D'.($row_no + 8 + $loop_offset))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 罫線
        $sheet->getStyle('A'.($row_no + 6 + $loop_offset).':D'.($row_no + 8 + $loop_offset))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        // 保管場所
        $sheet->setCellValueExplicitByColumnAndRow(
            1,
            ($row_no + 7 + $loop_offset),
            (isset($location_list[$val['location_id']])) ? $location_list[$val['location_id']]:'',
            DataType::TYPE_STRING
        )
            ->getStyle('A'.($row_no + 7 + $loop_offset).':A'.($row_no + 7 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(20)
        ;
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, ($row_no + 7 + $loop_offset))->getCoordinate())->getAlignment()->setShrinkToFit(true);

        // 備考
        $sheet->setCellValueExplicitByColumnAndRow(
            5,
            ($row_no + 6 + $loop_offset),
            '備考',
            DataType::TYPE_STRING
        )
            ->getStyle('E'.($row_no + 6 + $loop_offset).':E'.($row_no + 6 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(11)
        ;
        // 結合
        $sheet->mergeCells('E'.($row_no + 7 + $loop_offset).':L'.($row_no + 8 + $loop_offset));
        // 文字位置
        $sheet->getStyle('E'.($row_no + 7 + $loop_offset).':L'.($row_no + 8 + $loop_offset))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 罫線
        $sheet->getStyle('E'.($row_no + 6 + $loop_offset).':L'.($row_no + 8 + $loop_offset))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        // 備考
        $nut            = ($val['nut_flg'] == 'YES') ? 'ナット有':'ナット無';
        $consumer_name  = (!empty($val['consumer_name'])) ? $val['consumer_name']:'';
        $mark           = (!empty($nut) && !empty($consumer_name)) ? ' ,':'';
        $word           = (!empty($consumer_name)) ? $nut.$mark.$consumer_name:$nut;
        $sheet->setCellValueExplicitByColumnAndRow(
            5,
            ($row_no + 7 + $loop_offset),
            $word,
            DataType::TYPE_STRING
        )
            ->getStyle('E'.($row_no + 7 + $loop_offset).':E'.($row_no + 7 + $loop_offset))
            ->getFont()
            // ->setName('游ゴシック Regular (本文)')
            ->setName('Meiryo UI')
            ->setSize(20)
        ;
        $sheet->getStyle($sheet->getCellByColumnAndRow(5, ($row_no + 7 + $loop_offset))->getCoordinate())->getAlignment()->setShrinkToFit(true);

    }

    /**
     * 入庫シールの作成
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function setReceiptSticker($data=array(), $db = null) {

        // タイムアウトを一時的に解除
        ini_set('max_execution_time', 0);
        // 最大メモリー数を増幅
        ini_set('memory_limit', '5120M');
        // MySQLの接続タイム設定
        ini_set('mysql.connect_timeout', 300);
        ini_set('default_socket_timeout', 300);

        /**
         * Excel作成
         */
        $tpl_dir        = DOCROOT.'template/';
        $temp_file      = 'receipt_sticker.xlsx';
        $gen_dir        = APPPATH.'tmp/';
        $gen_file       = date('Ymd').'_receipt_sticker.xlsx';

        // テンプレートファイルを読み込み
        $spreadsheet    = IOFactory::load($tpl_dir.$temp_file);
        $sheet          = $spreadsheet->getActiveSheet();

        $page_offset    = 0;
        $sticker_offset = 38;

        $data_cnt       = 0;
        if (!empty($data)) {
            $data_cnt   = count($data);
        }

        foreach ($data as $key => $val) {
            // 行の高さを調節
            for($i = 1;$i <= ($sticker_offset * ($key + 1));$i++) {
                $sheet->getRowDimension($i)->setRowHeight(25.0);
            }

            $row_no         = 3; // 行番号は1から
            $col_no         = 1; // カラム番号は0から
            $loop_offset    = 0;
            for($i = 0;$i < 4;$i++) {
                // Excelフォーマット生成
                self::setExcelReceiptStickerDesign($sheet, $val, $loop_offset, $page_offset, $db);
                $loop_offset    = ($loop_offset + 9);
            }
            $page_offset        = ($page_offset + $sticker_offset);
            // 印刷範囲指定
            $sheet->getPageSetup()
                ->setFitToPage(true)
                ->setFitToWidth(1)
                ->setFitToHeight($key + 1)
                ;
        }
        // 印刷範囲指定
        $sheet->getPageSetup()
            ->setPrintArea('A1:L'.$page_offset)
            ;
        // 別名で保存
        // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        // $writer->save($gen_dir.$gen_file);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$gen_file.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
        return true;

    }

    //=========================================================================//
    //==============================   出庫指示書  =============================//
    //=========================================================================//
    /**
     * 出庫指示書の作成
     *
     * @param
     * $version       : Excel5(xls) or Excel2007(xlsx)
     * $data[行][列]  : セルにセットする値 ※連想配列
     *
     * @return
     * Excelデータ
     */
    public static function setOutboundInstructions($data=array(), $db = null) {

        // タイムアウトを一時的に解除
        ini_set('max_execution_time', 0);
        // 最大メモリー数を増幅
        ini_set('memory_limit', '5120M');
        // MySQLの接続タイム設定
        ini_set('mysql.connect_timeout', 300);
        ini_set('default_socket_timeout', 300);

        /**
         * Excel作成
         */
        $tpl_dir            = DOCROOT.'template/';
        $temp_file          = 'outbound_instructions.xlsx';
        $gen_dir            = APPPATH.'tmp/';
        $gen_file           = date('Ymd').'_outbound_instructions.xlsx';
        // テンプレートファイルを読み込み
        $spreadsheet        = IOFactory::load($tpl_dir.$temp_file);
        $sheet              = $spreadsheet->getActiveSheet();

        $total_offset       = 0;
        $page_offset        = 0;
        $line_offset        = 23;

        $data_cnt           = 0;
        if (!empty($data)) {
            $data_cnt       = count($data);
            $page_offset    = ceil($data_cnt / 5);
        }

        // 出庫指示書デザイン作成
        self::setOutboundInstructionsSheet($sheet, $page_offset, $line_offset);
        // 出庫指示書データ作成
        self::setOutboundInstructionsData($sheet, $data, $page_offset, $line_offset);

        // 印刷範囲指定
        $total_offset = ($line_offset * $page_offset);
        $sheet->getPageSetup()
            ->setPrintArea('A1:O'.$total_offset)
            ;
        // 別名で保存
        // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        // $writer->save($gen_dir.$gen_file);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$gen_file.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();     //ファイル破損エラー防止

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
        return true;

    }

    /**
     * [PHPSpreadsheet]シートのデザインをコピーする
     *
     * @param PHPExcel_Worksheet $sheet
     * @param int $page_offset 複数ページのループカウント数
     * @param int $line_offset １ページのループカウント数
     */
    public static function setOutboundInstructionsSheet($sheet, $page_offset, $line_offset, $col_offset = 15) {

        $countup_no = 0;
        for($i = 1;$i <= $page_offset;$i++) {
            for($row = 1;$row <= $line_offset;$row++) {
                if ($row == 2) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(28.0);
                } elseif ($row == 14) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(33.0);
                } elseif ($row == 19) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(36.0);
                } elseif ($row == 20) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(6.0);
                } elseif ($row == 22) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(31.0);
                } elseif ($row == 23) {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(31.0);
                } else {
                    // 行の幅を設定
                    $sheet->getRowDimension(($row + $countup_no))->setRowHeight(18.0);
                }
                for($col = 1;$col <= $col_offset;$col++) {
                    if ($row == 2) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            // 罫線
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                            $sheet->setCellValueExplicitByColumnAndRow(
                                $col,
                                ($row + $countup_no),
                                '出庫指示書',
                                DataType::TYPE_STRING
                            )
                                ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setUnderline(true)
                                // ->setName('游ゴシック Regular (本文)')
                                ->setName('Meiryo UI')
                                ->setSize(16)
                            ;
                        }
                    } elseif ($row == 4) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            // 罫線
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            $sheet->getStyle($column_name.($row + $countup_no).':'.$column_name.($row + 15 + $countup_no))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                            $sheet->setCellValueExplicitByColumnAndRow(
                                $col,
                                ($row + $countup_no),
                                '出庫日時',
                                DataType::TYPE_STRING
                            )
                                ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setUnderline(true)
                                ->setName('Meiryo UI')
                                ->setSize(9)
                            ;
                        }
                    } elseif ($row == 6 || $row == 9 || $row == 12 || $row == 15 || $row == 18) {
                        switch ($row) {
                            case 6:
                                $str = 'お客様';
                                break;
                            case 9:
                                $str = '車種名';
                                break;
                            case 12:
                                $str = '登録番号';
                                break;
                            case 15:
                                $str = 'メモ';
                                break;
                            case 18:
                                $str = '出庫完了';
                                break;
                        }
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $sheet->setCellValueExplicitByColumnAndRow(
                                $col,
                                ($row + $countup_no),
                                $str,
                                DataType::TYPE_STRING
                            )
                                ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setUnderline(true)
                                ->setName('Meiryo UI')
                                ->setSize(9)
                            ;
                        }
                    } elseif ($row == 19) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $sheet->setCellValueExplicitByColumnAndRow(
                                $col,
                                ($row + $countup_no),
                                '□',
                                DataType::TYPE_STRING
                            )
                                ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setName('Meiryo UI')
                                ->setSize(36)
                            ;
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())->getAlignment()->setHorizontal('right');
                        }
                    } elseif ($row == 21) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            // 罫線
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setName('Meiryo UI')
                                ->setSize(11)
                            ;
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())->getAlignment()->setHorizontal('center');
                        }
                    } elseif ($row == 7) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            // セルの結合
                            $sheet->mergeCells($column_name.($row + $countup_no).':'.$column_name.($row + 1 + $countup_no));
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getAlignment()
                                ->setWrapText(true)
                            ;
                        }
                    } elseif ($row == 10) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            // セルの結合
                            $sheet->mergeCells($column_name.($row + $countup_no).':'.$column_name.($row + 1 + $countup_no));
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getAlignment()
                                ->setWrapText(true)
                            ;
                        }
                    } elseif ($row == 13) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            // セルの結合
                            $sheet->mergeCells($column_name.($row + $countup_no).':'.$column_name.($row + 1 + $countup_no));
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getAlignment()
                                ->setWrapText(true)
                                ;
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getFont()
                                ->setName('Meiryo UI')
                                ->setSize(16)
                            ;
                        }
                    } elseif ($row == 16) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            // セルの結合
                            $sheet->mergeCells($column_name.($row + $countup_no).':'.$column_name.($row + 1 + $countup_no));
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                ->getAlignment()
                                ->setWrapText(true)
                            ;
                        }
                    } elseif ($row == 22) {
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            $column_name = Coordinate::stringFromColumnIndex($col);
                            // セルの結合
                            $sheet->mergeCells($column_name.($row + $countup_no).':'.$column_name.($row + 1 + $countup_no));
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())->getAlignment()->setHorizontal('center');
                            // $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                            //     ->getFont()
                            //     ->setName('CODE39')
                            //     ->setSize(36)
                            // ;
                        }
                    }
                }
            }
            $countup_no = $countup_no + ($line_offset * $i);
        }
    }

    /**
     * [PHPSpreadsheet]シートにデータをコピーする
     *
     * @param PHPExcel_Worksheet $sheet
     * @param int $page_offset 複数ページのループカウント数
     * @param int $line_offset １ページのループカウント数
     */
    public static function setOutboundInstructionsData($sheet, $data, $page_offset, $line_offset, $col_offset = 15) {

        // 保管場所リスト
        $location_list  = GenerateList::getLocationList(true, self::$db);

        $countup_no = 0;
        $col_cnt    = 5;
        $dcnt       = 0;
        for($i = 1;$i <= $page_offset;$i++) {
            for($row = 1;$row <= $line_offset;$row++) {
                if ($i > 1) {
                    $dcnt = ($col_cnt * ($i - 1));
                } else {
                    $dcnt = 0;
                }
                for($col = 1;$col <= $col_offset;$col++) {
                    if ($row == 5) {
                        // 出庫日時
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    (!empty($data[$dcnt]['delivery_schedule_date'])) ? $data[$dcnt]['delivery_schedule_date'].' '.$data[$dcnt]['delivery_schedule_time']:'',
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setShrinkToFit(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setName('Meiryo UI')
                                    ->setSize(11)
                                    ->setBold(false)
                                ;
                                $dcnt++;
                            }
                        }
                    } elseif ($row == 7) {
                        // お客様
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    $data[$dcnt]['customer_name'],
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setWrapText(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setName('Meiryo UI')
                                    ->setSize(11)
                                    ->setBold(false)
                                ;
                                $dcnt++;
                            }
                        }
                    } elseif ($row ==  10) {
                        // 車種
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    $data[$dcnt]['car_name'],
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setWrapText(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setName('Meiryo UI')
                                    ->setSize(11)
                                    ->setBold(false)
                                ;
                                $dcnt++;
                            }
                        }
                    } elseif ($row ==  13) {
                        // 登録番号
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    $data[$dcnt]['car_code'],
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setWrapText(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setName('Meiryo UI')
                                    ->setSize(18)
                                    ->setBold(false)
                                ;
                                $dcnt++;
                            }
                        }
                    } elseif ($row ==  16) {
                        // メモ
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    // (!empty($data[$dcnt]['request_memo'])) ? $data[$dcnt]['request_memo']:'',
                                    // (!empty($data[$dcnt]['memo'])) ? $data[$dcnt]['memo']:'',
                                    (!empty($data[$dcnt]['set_memo'])) ? $data[$dcnt]['set_memo']:'',
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setWrapText(true)
                                    // ->setShrinkToFit(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setName('Meiryo UI')
                                    ->setSize(8)
                                    ->setBold(false)
                                ;
                                $dcnt++;
                            }
                        }
                    } elseif ($row ==  21) {
                        // 保管場所
                        if ($col == 2 || $col == 5 || $col == 8 || $col == 11 || $col == 14) {
                            if (isset($data[$dcnt])) {
                                $sheet->setCellValueExplicitByColumnAndRow(
                                    $col,
                                    ($row + $countup_no),
                                    (isset($location_list[$data[$dcnt]['location_id']])) ? $location_list[$data[$dcnt]['location_id']]:'不明',
                                    DataType::TYPE_STRING
                                )
                                    ->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getAlignment()
                                    ->setShrinkToFit(true)
                                    ;
                                $sheet->getStyle($sheet->getCellByColumnAndRow($col, ($row + $countup_no))->getCoordinate())
                                    ->getFont()
                                    ->setSize(9)
                                    ->setBold(false)
                                ;
                                // バーコード生成
                                if (isset($location_list[$data[$dcnt]['location_id']])) {
                                    // 文字変換
                                    if ($barcode_name       = self::getBarcodeData($data[$dcnt]['location_id'], self::$db)) {
                                        $column_name        = Coordinate::stringFromColumnIndex($col);
                                        $create_dir_data    = array($data[$dcnt]['location_id']);
                                        $create_dir         = self::create_dir($create_dir_data, DOCROOT.'template/barcode/');
                                        // QRコード生成
                                        // $qrCode             = Builder::create()
                                        //                       ->writer(new PngWriter())
                                        //                       ->writerOptions([])
                                        //                       ->data($location_list[$data[$dcnt]['location_id']])
                                        //                       ->encoding(new Encoding('UTF-8'))
                                        //                       ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                                        //                       ->size(200)
                                        //                       ->margin(10)
                                        //                       ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                                        //                       ->build();
                                        // $qrCode->saveToFile(DOCROOT.'template/barcode/'.$data[$dcnt]['location_id'].'/'.date('Ymd').'_barcode.png');
                                        //画像の貼り付け
                                        // $drawing            = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                        // $drawing->setName($location_list[$data[$dcnt]['location_id']]);
                                        // $drawing->setDescription($location_list[$data[$dcnt]['location_id']]);
                                        // $drawing->setPath(DOCROOT.'template/barcode/'.$data[$dcnt]['location_id'].'/'.date('Ymd').'_barcode.png');
                                        // $drawing->setHeight(70);
                                        // // $drawing->setWidth(500);
                                        // $drawing->setOffsetX(35);
                                        // $drawing->setOffsetY(5);
                                        // $drawing->setCoordinates($column_name.($row + 1 + $countup_no));
                                        // $drawing->setWorksheet($sheet);

                                        // バーコード生成
                                        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                        file_put_contents(DOCROOT.'template/barcode/'.$data[$dcnt]['location_id'].'/'.date('Ymd').'_barcode.png', $generator->getBarcode($barcode_name, $generator::TYPE_ITF_8, 8, 200));
                                        //画像の貼り付け
                                        $drawing            = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                        $drawing->setName($location_list[$data[$dcnt]['location_id']]);
                                        $drawing->setDescription($location_list[$data[$dcnt]['location_id']]);
                                        $drawing->setPath(DOCROOT.'template/barcode/'.$data[$dcnt]['location_id'].'/'.date('Ymd').'_barcode.png');
                                        $drawing->setHeight(40);
                                        $drawing->setWidth(130);
                                        $drawing->setOffsetX(10);
                                        $drawing->setOffsetY(18);
                                        $drawing->setCoordinates($column_name.($row + 1 + $countup_no));
                                        $drawing->setWorksheet($sheet);
                                    }
                                }
                                $dcnt++;
                            }
                        }
                    }
                }
            }
            $countup_no = $countup_no + ($line_offset * $i);
        }
    }

    // バーコードの値を取得
    public static function getBarcodeData($location_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $list = array();
        $stmt = \DB::select(
                array('m.id', 'location_id'),
                array(\DB::expr('(SELECT name FROM m_storage_column WHERE id = m.storage_column_id)'), 'storage_column'),
                array(\DB::expr('(SELECT name FROM m_storage_depth WHERE id = m.storage_depth_id)'), 'storage_depth'),
                array(\DB::expr('(SELECT name FROM m_storage_height WHERE id = m.storage_height_id)'), 'storage_height'),
                array(\DB::expr('CONCAT(
                    (SELECT name FROM m_storage_column WHERE id = m.storage_column_id),
                    " - ",
                    (SELECT name FROM m_storage_depth WHERE id = m.storage_depth_id),
                    " - ",
                    (SELECT name FROM m_storage_height WHERE id = m.storage_height_id)
                    )'), 'location')
                );

        // テーブル
        $stmt->from(array('rel_storage_location', 'm'));
        // 条件
        $stmt->where('m.id', $location_id);
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->current();

        if (!empty($result)) {
            // $storage_column = sprintf('%02d', preg_replace('/[^0-9a-zA-Z]/', '', trim(mb_convert_kana($result['storage_column'], 'as', 'UTF-8'))));
            // $storage_depth  = sprintf('%03d', preg_replace('/[^0-9a-zA-Z]/', '', trim(mb_convert_kana($result['storage_depth'], 'as', 'UTF-8'))));
            // $storage_height = sprintf('%03d', preg_replace('/[^0-9a-zA-Z]/', '', trim(mb_convert_kana($result['storage_height'], 'as', 'UTF-8'))));

            $column         = preg_replace('/[^0-9]/', '', trim(mb_convert_kana($result['storage_column'], 'as', 'UTF-8')));
            $depth          = preg_replace('/[^0-9]/', '', trim(mb_convert_kana($result['storage_depth'], 'as', 'UTF-8')));
            $height         = preg_replace('/[^0-9]/', '', trim(mb_convert_kana($result['storage_height'], 'as', 'UTF-8')));
            if (empty($column) || empty($depth) || empty($height)) {
                return false;
            } else {
                $height = substr($height, 0, -1);
            }

            $storage_column = sprintf('%02d', $column);
            $storage_depth  = sprintf('%03d', $depth);
            $storage_height = sprintf('%03d', $height);

            return $storage_column.$storage_depth.$storage_height;
        }

        return false;
    }

}