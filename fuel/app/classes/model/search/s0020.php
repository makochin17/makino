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
     * ユニットマスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(m.id) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('m.id', 'car_id'),
                    array('m.old_car_id', 'old_car_id'),
                    array('m.car_code', 'car_code'),
                    array('m.car_name', 'car_name'),
                    array('m.customer_code', 'customer_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(c.name),"'.$encrypt_key.'")'), 'customer_name'),
                    array('m.owner_name', 'owner_name'),
                    array('m.consumer_name', 'consumer_name'),
                    );
        }

        // テーブル
        $stmt->from(array('m_car', 'm'))
            ->join(array('m_customer', 'c'), 'INNER')
                ->on('c.customer_code', '=', 'm.customer_code')
                ->on('c.del_flg', '=', \DB::expr("'NO'"))
        ;

        // 車両番号
        if (!empty($conditions['car_code'])) {
            $stmt->where('m.car_code', '=', $conditions['car_code']);
        }
        // 車種
        if (!empty($conditions['car_name'])) {
            $stmt->where('m.car_name', 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
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
            return $stmt->order_by('m.id', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}