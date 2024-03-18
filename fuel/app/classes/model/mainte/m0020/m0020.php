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
                array('m.disp_flg', 'disp_flg'),
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
                array('m.disp_flg', 'disp_flg'),
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
     * ユニットマスタ検索&件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(m.id) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('m.id', 'unit_code'),
                            array('m.name', 'unit_name'),
                            array('m.schedule_type', 'schedule_type'),
                            array('m.disp_flg', 'disp_flg'),
                            array('m.start_date', 'start_date'),
                            array('m.end_date', 'end_date')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('m_unit', 'm'))
        ;

        // 予約タイプ
        if (!empty($conditions['schedule_type'])) {
            $stmt->where('m.schedule_type', '=', $conditions['schedule_type']);
        }
        // ユニット名称
        if (!empty($conditions['unit_name'])) {
            $stmt->where('m.name', 'LIKE', \DB::expr("'%".$conditions['unit_name']."%'"));
        }

        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('m.schedule_type', 'ASC')->order_by('m.id', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('m.schedule_type', 'ASC')->order_by('m.id', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
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