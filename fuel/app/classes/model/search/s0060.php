<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0060 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 商品マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(m.product_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('m.product_code', 'product_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(m.product_name),"'.$encrypt_key.'")'), 'product_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(m.category),"'.$encrypt_key.'")'), 'category'),
                    array('m.sort', 'sort'),
                    );
        }

        // テーブル
        $stmt->from(array('m_product', 'm'));
        
        // 商品コード
        if (trim($conditions['product_code']) != '') {
            $stmt->where('m.product_code', '=', $conditions['product_code']);
        }
        // 商品名
        if (trim($conditions['product_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.product_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // 分類
        if (trim($conditions['category']) != '' && trim($conditions['category']) != '0') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.category),"'.$encrypt_key.'")'), '=', $conditions['category']);
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