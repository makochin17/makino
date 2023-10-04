<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0030 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 庸車先マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(rsl.id) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                        array('rsl.id', 'storage_location_id'),
                        array(\DB::expr("CONCAT(msc.name, '-', msd.name, '-', msh.name)"), 'storage_location_name'),
                        array('rsl.storage_column_id', 'storage_column_id'),
                        array('msc.name', 'storage_column_name'),
                        array('rsl.storage_depth_id', 'storage_depth_id'),
                        array('msd.name', 'storage_depth_name'),
                        array('rsl.storage_height_id', 'storage_height_id'),
                        array('msh.name', 'storage_height_name'),
                        array('rsl.del_flg', 'del_flg'),
                    );
        }

        // テーブル
        $stmt->from(array('rel_storage_location', 'rsl'))
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
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('rsl.id', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}