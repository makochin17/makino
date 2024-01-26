<?php
namespace Model\Search;

class S0040 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 車種マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(m.car_model_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('m.car_model_code', 'car_model_code'),
                    array('m.car_model_name', 'car_model_name'),
                    array('m.tonnage', 'tonnage'),
                    array('m.aggregation_tonnage', 'aggregation_tonnage'),
                    array('m.freight_tonnage', 'freight_tonnage'),
                    array('m.sort', 'sort')
                    );
        }

        // 条件
        $stmt->from(array('m_car_model', 'm'));
        // 車種コード
        if (trim($conditions['car_model_code']) != '') {
            $stmt->where('m.car_model_code', '=', $conditions['car_model_code']);
        }
        // 車種名
        if (trim($conditions['car_model_name']) != '') {
            $stmt->where('m.car_model_name', 'LIKE', \DB::expr("'%".$conditions['car_model_name']."%'"));
        }
        // トン数
        if (trim($conditions['tonnage']) != '') {
            $stmt->where('m.tonnage', '=', $conditions['tonnage']);
        }
        // 集約トン数
        if (trim($conditions['aggregation_tonnage']) != '') {
            $stmt->where('m.aggregation_tonnage', '=', $conditions['aggregation_tonnage']);
        }
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('m.sort', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}