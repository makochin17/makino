<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0010 extends \Model {

    public static $db       = 'MAKINO';
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
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.mail_address),"'.$encrypt_key.'")'), 'mail_address')
                    );
        }

        // テーブル
        $stmt->from(array('m_member', 'mm'))
            // ->join(array('m_car', 'mc'), 'left outer')
            //     ->on('mm.car_code', '=', 'mc.car_code')
            //     ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
            //     ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
        ;

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
        // メールアドレス
        if (trim($conditions['mail_address']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.mail_address),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['mail_address']."%'"));
        }
        // // 車両番号
        // if (trim($conditions['car_number']) != '') {
        //     $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_number']."%'"));
        // }

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