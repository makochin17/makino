<?php
namespace Model\Search;

class S0032 extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 庸車先営業所マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mcs.carrier_sales_office_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mcs.carrier_sales_office_code', 'carrier_sales_office_code'),
                    array('mcs.sales_office_name', 'sales_office_name'),
                    array('mcs.carrier_company_code', 'carrier_company_code'),
                    array('mcc.company_name', 'company_name'),
                    );
        }

        // テーブル
        $stmt->from(array('m_carrier_sales_office', 'mcs'))
            ->join(array('m_carrier_company', 'mcc'), 'left outer')
                ->on('mcs.carrier_company_code', '=', 'mcc.carrier_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 庸車先営業所コード
        if (trim($conditions['carrier_sales_office_code']) != '') {
            $stmt->where('mcs.carrier_sales_office_code', '=', $conditions['carrier_sales_office_code']);
        }
        // 営業所名
        if (trim($conditions['sales_office_name']) != '') {
            $stmt->where('mcs.sales_office_name', 'LIKE', \DB::expr("'%".$conditions['sales_office_name']."%'"));
        }
        // 庸車先営業所コード
        if (trim($conditions['carrier_company_code']) != '') {
            $stmt->where('mcs.carrier_company_code', '=', $conditions['carrier_company_code']);
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
            return $stmt->order_by('mcs.carrier_sales_office_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }
    
    /**
     * 庸車先営業所マスタレコード取得
     */
    public static function getSearchCarrierSalesOffice($carrier_sales_office_code, $carrier_company_code, $db) {
        
        // 件数取得
        $stmt = \DB::select(
                array('mcs.carrier_sales_office_code', 'carrier_sales_office_code'),
                array('mcs.sales_office_name', 'sales_office_name'),
                );

        // テーブル
        $stmt->from(array('m_carrier_sales_office', 'mcs'));
        
        // 庸車先営業所コード
        $stmt->where('mcs.carrier_sales_office_code', '=', $carrier_sales_office_code);
        // 庸車先会社コード
        if (!empty($carrier_company_code)) {
            $stmt->where('mcs.carrier_company_code', '=', $carrier_company_code);
        }
        // 適用開始日
        $stmt->where('mcs.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcs.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();

    }

}