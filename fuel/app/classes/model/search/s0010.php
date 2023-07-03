<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0010 extends \Model {

    public static $db       = 'ONISHI';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 社員マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mm.member_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mm.member_code', 'member_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'full_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                    array('md.division_name', 'division'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mp.position_name),"'.$encrypt_key.'")'), 'position'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.phone_number),"'.$encrypt_key.'")'), 'phone_number')
                    );
        }

        // テーブル
        $stmt->from(array('m_member', 'mm'))
            ->join(array('m_car', 'mc'), 'left outer')
                ->on('mm.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('mm.division_code', '=', 'md.division_code')
            ->join(array('m_position', 'mp'), 'left outer')
                ->on('mm.position_code', '=', 'mp.position_code');
        
        // 社員コード
        if (trim($conditions['member_code']) != '') {
            $stmt->where('mm.member_code', '=', $conditions['member_code']);
        }
        // 氏名
        if (trim($conditions['full_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['full_name']."%'"));
        }
        // ふりがな
        if (trim($conditions['name_furigana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.name_furigana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['name_furigana']."%'"));
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $stmt->where('mm.division_code', '=', $conditions['division']);
        }
        // 役職コード
        if (trim($conditions['position']) != '' && trim($conditions['position']) != '00') {
            $stmt->where('mm.position_code', '=', $conditions['position']);
        }
        // 車両番号
        if (trim($conditions['car_number']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_number']."%'"));
        }
        
        // 適用開始日
        $stmt->where('mm.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mm.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mm.member_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}