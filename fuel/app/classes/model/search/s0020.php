<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0020 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 得意先マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mu.id) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mu.id', 'unit_code'),
                    array('mu.name', 'unit_name'),
                    );
        }

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
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mu.id', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}