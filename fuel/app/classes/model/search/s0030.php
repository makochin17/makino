<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0030 extends \Model {

    public static $db       = 'ONISHI';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 庸車先マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mc.carrier_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mc.carrier_code', 'carrier_code'),
                    array('mc.company_section', 'company_section'),
                    array('mcc.company_name', 'company_name'),
                    array('mcs.sales_office_name', 'sales_office_name'),
                    array('mcd.department_name', 'department_name'),
                    array('mc.closing_date', 'closing_date'),
                    array('mc.closing_date_1', 'closing_date_1'),
                    array('mc.closing_date_2', 'closing_date_2'),
                    array('mc.closing_date_3', 'closing_date_3'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'official_name'),
                    );
        }

        // テーブル
        $stmt->from(array('m_carrier', 'mc'))
            ->join(array('m_carrier_company', 'mcc'), 'left outer')
                ->on('mc.carrier_company_code', '=', 'mcc.carrier_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_carrier_sales_office', 'mcs'), 'left outer')
                ->on('mc.carrier_sales_office_code', '=', 'mcs.carrier_sales_office_code')
                ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_carrier_department', 'mcd'), 'left outer')
                ->on('mc.carrier_department_code', '=', 'mcd.carrier_department_code')
                ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $stmt->where('mc.carrier_code', '=', $conditions['carrier_code']);
        }
        // 会社区分
        if (trim($conditions['company_section']) != '' && trim($conditions['company_section']) != '0') {
            $stmt->where('mc.company_section', '=', $conditions['company_section']);
        }
        // 会社名
        if (trim($conditions['company_name']) != '') {
            $stmt->where('mcc.company_name', 'LIKE', \DB::expr("'%".$conditions['company_name']."%'"));
        }
        // 営業所名
        if (trim($conditions['sales_office_name']) != '') {
            $stmt->where('mcs.sales_office_name', 'LIKE', \DB::expr("'%".$conditions['sales_office_name']."%'"));
        }
        // 部署名
        if (trim($conditions['department_name']) != '') {
            $stmt->where('mcd.department_name', 'LIKE', \DB::expr("'%".$conditions['department_name']."%'"));
        }
        // 締日
        if (trim($conditions['closing_date']) != '' && trim($conditions['closing_date']) != '0') {
            $stmt->and_where_open();
            $stmt->where('mc.closing_date', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_1', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_2', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_3', '=', $conditions['closing_date']);
            $stmt->and_where_close();
        }
        // 正式名称
        if (trim($conditions['official_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['official_name']."%'"));
        }
        // 正式名称（カナ）
        if (trim($conditions['official_name_kana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['official_name_kana']."%'"));
        }
                
        // 適用開始日
        $stmt->where('mc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mc.carrier_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}