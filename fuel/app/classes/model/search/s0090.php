<?php
namespace Model\Search;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;

class S0090 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * ユーザーレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(m.member_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                array('m.member_code', 'member_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'member_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'member_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                array('m.user_id', 'user_id'),
                array('m.user_authority', 'user_authority'),
                array('m.lock_status', 'lock_status'),
                array('m.customer_code', 'customer_code'),
                array('m.start_date', 'start_date')
            );
        }

        // テーブル
        $stmt->from(array('m_member', 'm'))
            // ->join(array('m_car', 'mc'), 'left outer')
            //     ->on('mm.car_code', '=', 'mc.car_code')
            //     ->on('mc.start_date', '<=', '\''.date("Y-m-d").'\'')
            //     ->on('mc.end_date', '>', '\''.date("Y-m-d").'\'')
        ;

        // 社員コード
        if (trim($conditions['member_code']) != '') {
            $stmt->where('m.member_code', '=', $conditions['member_code']);
        }
        // 氏名
        if (trim($conditions['member_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['member_name']."%'"));
        }
        // ふりがな
        if (trim($conditions['member_name_kana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['member_name_kana']."%'"));
        }
        // メールアドレス
        if (trim($conditions['mail_address']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['mail_address']."%'"));
        }
        // 勤務先
        if (!empty($conditions['user_authority'])) {
            $stmt->where('m.user_authority', $conditions['user_authority']);
        }

        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');

        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('m.member_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }
}