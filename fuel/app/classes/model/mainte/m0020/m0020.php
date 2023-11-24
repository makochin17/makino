<?php
namespace Model\Mainte\M0020;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0025;
use \Model\Search\S0020;

class M0020 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * ユニットマスタレコード取得
     */
    public static function getUnit($code, $db) {

        // 項目
        $stmt = \DB::select(
                array('m.id', 'unit_code'),
                array('m.name', 'unit_name'),
                array('m.schedule_type', 'schedule_type'),
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));
        // ユニットコード
        $stmt->where('m.id', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * ユニットマスタレコード取得
     */
    public static function getUnitByName($name, $db) {

        // 項目
        $stmt = \DB::select(
                array('m.id', 'unit_code'),
                array('m.name', 'unit_name'),
                array('m.schedule_type', 'schedule_type'),
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));
        // ユニットコード
        $stmt->where('m.name', '=', $name);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * ユニットマスタ検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return S0020::getSearch(true, $conditions, null, null, $db);
    }

    /**
     * ユニットマスタ検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return S0020::getSearch(false, $conditions, $offset, $limit, $db);
    }

    /**
     * ユニットデータ削除
     */
    public static function delUnit($unit_code, $db = null) {
        return M0025::delete_record($unit_code, $db);
    }

    /**
     * エクセル作成処理
     */
    public static function createTsv($conditions, $db) {
        //出力データ取得
        $header = self::getHeader($db);
        $body = self::getBody($conditions, $db);

        try {
            \DB::start_transaction($db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0017', \Config::get('m_MI0017'), '', $db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }

            \DB::commit_transaction($db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction($db);
            \Log::error($e->getMessage());
        }

        //ファイル名設定
        $title = mb_convert_encoding('ユニットマスタ一覧', 'SJIS', 'UTF-8');
        $fileName = $title.'.tsv';

        //HTMLヘッダー
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);

        //ファイルへの書き込み
        $handle = fopen('php://output', 'w');

        mb_convert_variables('SJIS-win', 'UTF-8', $header);
        fputcsv($handle, $header, "\t");

        foreach ($body as $row) {
            mb_convert_variables('SJIS-win', 'UTF-8', $row);
            fputcsv($handle, $row, "\t");
        }

        fclose($handle);

        exit();
    }

    /**
     * TSV用ヘッダー情報取得
     */
    public static function getHeader($db) {
        $result = array();
        $result += array("unit_code" => "ユニットコード");
        $result += array("unit_name" => "ユニット名");

        return $result;
    }

    /**
     * TSV用データ取得
     */
    public static function getBody($conditions, $db) {

        $unit_list  = array();
        $result     = array();

        // 取得データ
        $stmt = \DB::select(
                array('mu.id', 'unit_code'),
                array('mu.name', 'unit_name'),
                );

        // テーブル
        $stmt->from(array('m_unit', 'mu'));

        // 会社名
        if (trim($conditions['unit_name']) != '') {
            $stmt->where('mu.name', 'LIKE', \DB::expr("'%".$conditions['unit_name']."%'"));
        }
        // 適用開始日
        $stmt->where('mu.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mu.end_date', '>', date("Y-m-d"));

        // 検索実行
        $unit_list  = $stmt->order_by('mu.id', 'ASC')->execute($db)->as_array();

        //担当部署の値再セット
        foreach ($unit_list as $unit) {
            $record = array(
                'unit_code' => $unit['unit_code'],
                'unit_name' => $unit['unit_name'],
            );

            $result[$unit['unit_code']] = $record;
        }

        return $result;
    }

}