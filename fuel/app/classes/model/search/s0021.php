<?php
namespace Model\Search;

class S0021 extends \Model {

    public static $db       = 'ONISHI';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 得意先会社マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mcc.client_company_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mcc.client_company_code', 'client_company_code'),
                    array('mcc.company_name', 'company_name'),
                    );
        }

        // テーブル
        $stmt->from(array('m_client_company', 'mcc'));
        
        // 得意先会社コード
        if (trim($conditions['client_company_code']) != '') {
            $stmt->where('mcc.client_company_code', '=', $conditions['client_company_code']);
        }
        // 会社名
        if (trim($conditions['company_name']) != '') {
            $stmt->where('mcc.company_name', 'LIKE', \DB::expr("'%".$conditions['company_name']."%'"));
        }
                
        // 適用開始日
        $stmt->where('mcc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mcc.client_company_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }
    
    /**
     * 得意先会社マスタレコード取得
     */
    public static function getSearchClientCompany($code, $db) {
        
        // 件数取得
        $stmt = \DB::select(
                array('mcc.client_company_code', 'client_company_code'),
                array('mcc.company_name', 'company_name'),
                );

        // テーブル
        $stmt->from(array('m_client_company', 'mcc'));
        
        // 得意先会社コード
        $stmt->where('mcc.client_company_code', '=', $code);
        // 適用開始日
        $stmt->where('mcc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();

    }

}