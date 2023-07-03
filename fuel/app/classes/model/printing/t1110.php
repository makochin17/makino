<?php
namespace Model\Printing;
use \Model\Printing\T1111;
use \Model\Printing\T1112;

class T1110 extends \Model {
    
    public static $db           = 'ONISHI';
    
    /**
     * 出力条件取得
     */
    public static function getConditions() {
        $conditions 	= array_fill_keys(array(
        	'division',
            'client_radio',
        	'client_code',
            'target_date',
            'target_date_day',
            'report_radio',
            'area_code',
            'bill_report',
        ), '');
        
        //出力条件取得
        if ($cond = \Session::get('t1110_list', array())) {
            foreach ($cond as $key => $val) {
                $conditions[$key] = $val;
            }
        }
        
        $result = array('division' => $conditions['division'],
                        'client_code' => $conditions['client_code'],
                        'target_date' => $conditions['target_date'],
                        'target_date_day' => $conditions['target_date_day'],
                        'report_radio' => $conditions['report_radio'],
                        'area_code' => $conditions['area_code'],
                        'bill_report' => $conditions['bill_report']);
        
        return $result;
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel() {
        $conditions = self::getConditions();
        
        //帳票出力処理分岐
        if ($conditions['report_radio'] == "1") {
            //請求明細書(集計)
            $message = T1111::outputReport($conditions);
        } elseif ($conditions['report_radio'] == "2") {
            //請求明細書(明細)
            $message = T1112::outputReport($conditions, $conditions['bill_report']);
        } else {
            $message = \Config::get('m_CE0001');
        }
        
        return $message;
    }

}