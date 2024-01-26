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

            $stmt = \DB::select(\DB::expr('COUNT(mm.customer_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mm.customer_code', 'customer_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'customer_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.name_kana),"'.$encrypt_key.'")'), 'customer_name_kana'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.tel),"'.$encrypt_key.'")'), 'tel'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.mobile),"'.$encrypt_key.'")'), 'mobile'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mm.office_name),"'.$encrypt_key.'")'), 'office_name')
                    );
        }

        // テーブル
        $stmt->from(array('m_customer', 'mm'))
            // ->join(array('m_car', 'mc'), 'left outer')
            //     ->on('mm.car_code', '=', 'mc.car_code')
            //     ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
            //     ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
        ;

        // 社員コード
        if (trim($conditions['customer_code']) != '') {
            $stmt->where('mm.customer_code', '=', $conditions['customer_code']);
        }
        // 氏名
        if (trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // ふりがな
        if (trim($conditions['customer_name_kana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.name_kana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name_kana']."%'"));
        }
        // メールアドレス
        if (trim($conditions['mail_address']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mm.mail_address),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['mail_address']."%'"));
        }
        // 勤務先
        if (trim($conditions['office_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.office_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['office_name']."%'"));
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
            return $stmt->order_by('mm.customer_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}