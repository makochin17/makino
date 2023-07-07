<?php
namespace Model\Search;

class S0022 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 得意先営業所マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mcs.client_sales_office_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mcs.client_sales_office_code', 'client_sales_office_code'),
                    array('mcs.sales_office_name', 'sales_office_name'),
                    array('mcs.client_company_code', 'client_company_code'),
                    array('mcc.company_name', 'company_name'),
                    );
        }

        // テーブル
        $stmt->from(array('m_client_sales_office', 'mcs'))
            ->join(array('m_client_company', 'mcc'), 'left outer')
                ->on('mcs.client_company_code', '=', 'mcc.client_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 得意先営業所コード
        if (trim($conditions['client_sales_office_code']) != '') {
            $stmt->where('mcs.client_sales_office_code', '=', $conditions['client_sales_office_code']);
        }
        // 営業所名
        if (trim($conditions['sales_office_name']) != '') {
            $stmt->where('mcs.sales_office_name', 'LIKE', \DB::expr("'%".$conditions['sales_office_name']."%'"));
        }
        // 得意先営業所コード
        if (trim($conditions['client_company_code']) != '') {
            $stmt->where('mcs.client_company_code', '=', $conditions['client_company_code']);
        }
                
        // 適用開始日
        $stmt->where('mcs.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcs.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mcs.client_sales_office_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }
    
    /**
     * 得意先営業所マスタレコード取得
     */
    public static function getSearchClientSalesOffice($client_sales_office_code, $client_company_code, $db) {
        
        // 件数取得
        $stmt = \DB::select(
                array('mcs.client_sales_office_code', 'client_sales_office_code'),
                array('mcs.sales_office_name', 'sales_office_name'),
                );

        // テーブル
        $stmt->from(array('m_client_sales_office', 'mcs'));
        
        // 得意先営業所コード
        $stmt->where('mcs.client_sales_office_code', '=', $client_sales_office_code);
        // 得意先会社コード
        if (!empty($client_company_code)) {
            $stmt->where('mcs.client_company_code', '=', $client_company_code);
        }
        // 適用開始日
        $stmt->where('mcs.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcs.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();

    }

}