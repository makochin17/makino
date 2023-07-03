<?php
namespace Model\Mainte;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Date;
use \Model\Mainte\M0030\M0030;

class M0080 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 課マスタレコード取得
     */
    public static function getDivision($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.division_code', 'division_code'),
                array('m.branch_office_code', 'branch_office_code'),
                array('m.carrier_code', 'carrier_code'),
                array('m.division_name', 'division_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.private_line_number),"'.$encrypt_key.'")'), 'private_line_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mobile_phone_number),"'.$encrypt_key.'")'), 'mobile_phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.person_in_charge),"'.$encrypt_key.'")'), 'person_in_charge'),
                );

        // テーブル
        $stmt->from(array('m_division', 'm'));
        
        // 課コード
        $stmt->where('m.division_code', '=', $code);
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 課マスタ更新
     */
    public static function updDivision($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // テーブル
        $stmt = \DB::update('m_division');
        
        // 項目セット
        $set = array(
            'branch_office_code' => $items['branch_office_code'],
            'carrier_code' => $items['carrier_code'],
            'division_name' => $items['division_name'],
            'private_line_number' => \DB::expr('HEX(AES_ENCRYPT("'.$items['private_line_number'].'","'.$encrypt_key.'"))'),
            'fax_number' => \DB::expr('HEX(AES_ENCRYPT("'.$items['fax_number'].'","'.$encrypt_key.'"))'),
            'mobile_phone_number' => \DB::expr('HEX(AES_ENCRYPT("'.$items['mobile_phone_number'].'","'.$encrypt_key.'"))'),
            'person_in_charge' => \DB::expr('HEX(AES_ENCRYPT("'.$items['person_in_charge'].'","'.$encrypt_key.'"))'),
            );
        $stmt->set($set);
        
        // 課コード
        $stmt->where('division_code', '=', $items['division_code']);
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 庸車先の検索
     */
    public static function getSearchCarrier($code, $db) {
        return M0030::getCarrier($code, $db);
    }
}