<?php
namespace Model\Mainte\M0030;
use \Date;
use \Log;
use \Config;
use \Model\Common\AuthConfig;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030\M0035;
use \Model\Search\S0030;

class M0030 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {

        $user_master_id   = AuthConfig::getAuthConfig('user_id');
        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => Date::forge()->format('mysql'),
                'create_user'       => $user_master_id,
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        }
        return $data;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 保管場所リレーションレコード取得
     */
    public static function getStorageLocation($code, $db) {

        // 項目
        $stmt = \DB::select(
                array('rsl.id', 'storage_location_id'),
                array(\DB::expr("CONCAT(msw.name, '-', msc.name, '-', msd.name, '-', msh.name)"), 'storage_location_name'),
                array('rsl.storage_warehouse_id', 'storage_warehouse_id'),
                array('msw.name', 'storage_warehouse_name'),
                array('rsl.storage_column_id', 'storage_column_id'),
                array('msc.name', 'storage_column_name'),
                array('rsl.storage_depth_id', 'storage_depth_id'),
                array('msd.name', 'storage_depth_name'),
                array('rsl.storage_height_id', 'storage_height_id'),
                array('msh.name', 'storage_height_name'),
                array('rsl.barcode_flg', 'barcode_flg'),
                array('rsl.del_flg', 'del_flg'),
                );

        $stmt->from(array('rel_storage_location', 'rsl'))
            ->join(array('m_storage_warehouse', 'msw'), 'left outer')
                ->on('msw.id', '=', 'rsl.storage_warehouse_id')
                ->on('msw.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msw.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_column', 'msc'), 'left outer')
                ->on('msc.id', '=', 'rsl.storage_column_id')
                ->on('msc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_depth', 'msd'), 'left outer')
                ->on('msd.id', '=', 'rsl.storage_depth_id')
                ->on('msd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msd.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_height', 'msh'), 'left outer')
                ->on('msh.id', '=', 'rsl.storage_height_id')
                ->on('msh.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msh.end_date', '>', '\''.date("Y-m-d").'\'');
        // コード
        $stmt->where('rsl.id', '=', $code);
        // 適用開始日
        $stmt->where('rsl.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('rsl.end_date', '>', date("Y-m-d"));
        // 削除フラグ
        $stmt->where('rsl.del_flg', '=', 'NO');

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 保管場所リレーションレコード取得
     */
    public static function getStorageLocationBySubCode($storage_warehouse_id, $storage_column_id, $storage_depth_id, $storage_height_id, $db) {

        // 項目
        $stmt = \DB::select(
                array('rsl.id', 'storage_location_id'),
                array(\DB::expr("CONCAT(msw.name, '-', msc.name, '-', msd.name, '-', msh.name)"), 'storage_location_name'),
                array('rsl.storage_warehouse_id', 'storage_warehouse_id'),
                array('msw.name', 'storage_warehouse_name'),
                array('rsl.storage_column_id', 'storage_column_id'),
                array('msc.name', 'storage_column_name'),
                array('rsl.storage_depth_id', 'storage_depth_id'),
                array('msd.name', 'storage_depth_name'),
                array('rsl.storage_height_id', 'storage_height_id'),
                array('msh.name', 'storage_height_name'),
                array('rsl.barcode_flg', 'barcode_flg'),
                array('rsl.del_flg', 'del_flg'),
                );

        $stmt->from(array('rel_storage_location', 'rsl'))
            ->join(array('m_storage_warehouse', 'msw'), 'left outer')
                ->on('msw.id', '=', 'rsl.storage_warehouse_id')
                ->on('msw.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msw.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_column', 'msc'), 'left outer')
                ->on('msc.id', '=', 'rsl.storage_column_id')
                ->on('msc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_depth', 'msd'), 'left outer')
                ->on('msd.id', '=', 'rsl.storage_depth_id')
                ->on('msd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msd.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_height', 'msh'), 'left outer')
                ->on('msh.id', '=', 'rsl.storage_height_id')
                ->on('msh.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msh.end_date', '>', '\''.date("Y-m-d").'\'');
        // 保管場所倉庫コード
        if (!empty($storage_warehouse_id)) {
            $stmt->where('rsl.storage_warehouse_id', '=', $storage_warehouse_id);
        }
        // 保管場所列コード
        if (!empty($storage_column_id)) {
            $stmt->where('rsl.storage_column_id', '=', $storage_column_id);
        }
        // 保管場所奥行コード
        if (!empty($storage_depth_id)) {
            $stmt->where('rsl.storage_depth_id', '=', $storage_depth_id);
        }
        // 保管場所高コード
        if (!empty($storage_height_id)) {
            $stmt->where('rsl.storage_height_id', '=', $storage_height_id);
        }

        // 適用開始日
        $stmt->where('rsl.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('rsl.end_date', '>', date("Y-m-d"));
        // 削除フラグ
        $stmt->where('rsl.del_flg', '=', 'NO');

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 保管場所リレーション検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return S0030::getSearch(true, $conditions, null, null, $db);
    }

    /**
     * 保管場所リレーション検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return S0030::getSearch(false, $conditions, $offset, $limit, $db);
    }

    /**
     * 保管場所リレーション削除
     */
    public static function delCarrier($carrier_code, $db = null) {
        return M0035::delete_record($carrier_code, $db);
    }

    /**
     * 保管場所リレーション バーコードフラグ更新
     */
    public static function updStorageLocationBarcode($storage_location_id, $barcode_flg, $db) {

        // テーブル
        $stmt = \DB::update('rel_storage_location');

        // 項目セット
        $set = array(
            'barcode_flg'  => $barcode_flg,
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 保管場所ID
        $stmt->where('id', '=', $storage_location_id);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        // 更新実行
        $result = $stmt->execute($db);

        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * エクセル作成処理
     */
    public static function createTsv($conditions, $db) {
        //出力データ取得
        $header = self::getHeader($db);
        $body   = self::getBody($conditions, $db);

        //ファイル名設定
        $title = mb_convert_encoding('保管場所リレーション一覧', 'SJIS', 'UTF-8');
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
        $result += array("storage_location_id" => "保管場所ID");
        $result += array("storage_location_name" => "保管場所名");
        $result += array("storage_warehouse_id" => "保管場所倉庫ID");
        $result += array("storage_warehouse_name" => "保管場所倉庫名");
        $result += array("storage_column_id" => "保管場所列ID");
        $result += array("storage_column_name" => "保管場所列名");
        $result += array("storage_depth_id" => "保管場所奥行ID");
        $result += array("storage_depth_name" => "保管場所奥行名");
        $result += array("storage_height_id" => "保管場所高ID");
        $result += array("storage_height_name" => "保管場所高名");
        $result += array("barcode_flg" => "バーコードフラグ");

        return $result;
    }

    /**
     * TSV用データ取得
     */
    public static function getBody($conditions, $db) {

        // 取得データ
        $stmt = \DB::select(
                array('rsl.id', 'storage_location_id'),
                array(\DB::expr("CONCAT(msw.name, '-', msc.name, '-', msd.name, '-', msh.name)"), 'storage_location_name'),
                array('rsl.storage_warehouse_id', 'storage_warehouse_id'),
                array('msw.name', 'storage_warehouse_name'),
                array('rsl.storage_column_id', 'storage_column_id'),
                array('msc.name', 'storage_column_name'),
                array('rsl.storage_depth_id', 'storage_depth_id'),
                array('msd.name', 'storage_depth_name'),
                array('rsl.storage_height_id', 'storage_height_id'),
                array('msh.name', 'storage_height_name'),
                array('rsl.barcode_flg', 'barcode_flg'),
                array('rsl.del_flg', 'del_flg'),
                );

        // テーブル
        $stmt->from(array('rel_storage_location', 'rsl'))
            ->join(array('m_storage_warehouse', 'msw'), 'left outer')
                ->on('msw.id', '=', 'rsl.storage_warehouse_id')
                ->on('msw.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msw.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_column', 'msc'), 'left outer')
                ->on('msc.id', '=', 'rsl.storage_column_id')
                ->on('msc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_depth', 'msd'), 'left outer')
                ->on('msd.id', '=', 'rsl.storage_depth_id')
                ->on('msd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msd.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_height', 'msh'), 'left outer')
                ->on('msh.id', '=', 'rsl.storage_height_id')
                ->on('msh.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msh.end_date', '>', '\''.date("Y-m-d").'\'');

        // 保管場所コード
        if (trim($conditions['storage_location_id']) != '') {
            $stmt->where('rsl.id', '=', $conditions['storage_location_id']);
        }
        // 保管場所名称
        if (trim($conditions['storage_location_name']) != '') {
            $stmt->where(\DB::expr("CONCAT(msc.name, '-', msd.name, '-', msh.name)"), 'LIKE', \DB::expr("'%".$conditions['storage_location_name']."%'"));
        }
        // 保管場所倉庫コード
        if (trim($conditions['storage_warehouse_id']) != '') {
            $stmt->where('rsl.storage_warehouse_id', '=', $conditions['storage_warehouse_id']);
        }
        // 保管場所倉庫名
        if (trim($conditions['storage_warehouse_name']) != '') {
            $stmt->where('msw.name', 'LIKE', \DB::expr("'%".$conditions['storage_warehouse_name']."%'"));
        }
        // 保管場所列コード
        if (trim($conditions['storage_column_id']) != '') {
            $stmt->where('rsl.storage_column_id', '=', $conditions['storage_column_id']);
        }
        // 保管場所列名
        if (trim($conditions['storage_column_name']) != '') {
            $stmt->where('msc.name', 'LIKE', \DB::expr("'%".$conditions['storage_column_name']."%'"));
        }
        // 保管場所奥行コード
        if (trim($conditions['storage_depth_id']) != '') {
            $stmt->where('rsl.storage_depth_id', '=', $conditions['storage_depth_id']);
        }
        // 保管場所奥行名
        if (trim($conditions['storage_depth_name']) != '') {
            $stmt->where('msd.name', 'LIKE', \DB::expr("'%".$conditions['storage_depth_name']."%'"));
        }
        // 保管場所高コード
        if (trim($conditions['storage_height_id']) != '') {
            $stmt->where('rsl.storage_height_id', '=', $conditions['storage_height_id']);
        }
        // 保管場所高名
        if (trim($conditions['storage_height_name']) != '') {
            $stmt->where('msh.name', 'LIKE', \DB::expr("'%".$conditions['storage_height_name']."%'"));
        }

        // 適用開始日
        $stmt->where('rsl.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('rsl.end_date', '>', date("Y-m-d"));
        // 検索実行
        $storage_location_list =  $stmt->order_by('rsl.id', 'ASC')->execute($db)->as_array();

        $result = array();

        //担当部署の値再セット
        foreach ($storage_location_list as $location) {

            $result[] = array(
                'storage_location_id'       => $location['storage_location_id'],
                'storage_location_name'     => $location['storage_location_name'],
                'storage_warehouse_id'      => $location['storage_warehouse_id'],
                'storage_warehouse_name'    => $location['storage_warehouse_name'],
                'storage_column_id'         => $location['storage_column_id'],
                'storage_column_name'       => $location['storage_column_name'],
                'storage_depth_id'          => $location['storage_depth_id'],
                'storage_depth_name'        => $location['storage_depth_name'],
                'storage_height_id'         => $location['storage_height_id'],
                'storage_height_name'       => $location['storage_height_name'],
                'barcode_flg'               => $location['barcode_flg'],
            );

        }

        return $result;

    }
}