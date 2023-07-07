<?php
namespace Model\Allocation;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as Worksheet;

ini_set("memory_limit", "1000M");

class D1030 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * エクセル出力処理
     */
    public static function getExcel($kind) {

        // テンプレート読み込み
        $reader         = new XlsxReader();
        $tpl_dir        = DOCROOT.'assets/template/';

        switch ($kind) {
            case '3':
                $name   = "共配便配車表_家具_雛形";
                $fileName = "配車雛形_家具";
                break;
            case '4':
                $name   = "共配便配車表_建材_雛形";
                $fileName = "配車雛形_建材";
                break;
            case '5':
                $name   = "共配便配車表_電材_雛形";
                $fileName = "配車雛形_電材";
                break;
            default:
                $name   = "共配便配車表_共通_雛形";
                $fileName = "配車雛形_共通";
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
        $fileName .= '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');
        ob_end_clean();

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}