<?php
namespace Model\Common;
use \Model\Common\SystemConfig;

class CommonSql extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 会社情報取得
     */
    public static function getCompanyData($division_code, $db = null) {
        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 項目
        $stmt = \DB::select(
                array(\DB::expr('AES_DECRYPT(UNHEX(mb.postal_code),"'.$encrypt_key.'")'), 'postal_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mb.address),"'.$encrypt_key.'")'), 'address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.private_line_number),"'.$encrypt_key.'")'), 'private_line_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.mobile_phone_number),"'.$encrypt_key.'")'), 'mobile_phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.person_in_charge),"'.$encrypt_key.'")'), 'person_in_charge'),
                );
        
        // テーブル
        $stmt->from(array('m_division', 'md'))
            ->join(array('m_branch_office', 'mb'), 'inner')
                ->on('md.branch_office_code', '=', 'mb.branch_office_code');
        // 課コード
        $stmt->where('md.division_code', '=', $division_code);
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        return $result[0];
    }
}