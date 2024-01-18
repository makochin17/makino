<?php
namespace Model\Mainte\M0010;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class M0011 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(m.member_code) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('m.member_code', 'member_code'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'full_name'),
                            array('m.user_id', 'user_id'),
                            array('m.user_authority', 'user_authority'),
                            array('mc.customer_code', 'customer_code'),
                            array(\DB::expr("
                                CASE
                                    WHEN mc.customer_type = 'individual' THEN '個人'
                                    WHEN mc.customer_type = 'corporation' THEN '法人'
                                    WHEN mc.customer_type = 'dealer' THEN 'ディーラー'
                                    ELSE ''
                                END
                                "), 'customer_type'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'customer_name'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.name_kana),"'.$encrypt_key.'")'), 'customer_name_kana'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.zip),"'.$encrypt_key.'")'), 'zip'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr1),"'.$encrypt_key.'")'), 'addr1'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr2),"'.$encrypt_key.'")'), 'addr2'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.tel),"'.$encrypt_key.'")'), 'tel'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax),"'.$encrypt_key.'")'), 'fax'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.mobile),"'.$encrypt_key.'")'), 'mobile'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.office_name),"'.$encrypt_key.'")'), 'office_name'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(mc.manager_name),"'.$encrypt_key.'")'), 'manager_name'),
                            array('mc.birth_date', 'birth_date'),
                            array(\DB::expr("
                                CASE
                                    WHEN mc.sex = 'Man' THEN '男性'
                                    WHEN mc.sex = 'Woman' THEN '女性'
                                    ELSE ''
                                END
                                "), 'sex'),
                            array('mc.resign_flg', 'resign_flg'),
                            array('mc.resign_date', 'resign_date'),
                            array('mc.resign_reason', 'resign_reason'),
                            array('mc.start_date', 'start_date'),
                            array('mc.end_date', 'end_date')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('m_member', 'm'))
            ->join(array('m_customer', 'mc'), 'LEFT')
            ->on('mc.customer_code', '=', 'm.customer_code')
            ->on('mc.del_flg', '=', \DB::expr("'NO'"))
        ;

        // ユーザー番号
        if (!empty($conditions['member_code'])) {
            $stmt->where(\DB::expr('CAST(m.member_code AS SIGNED)'), '=', $conditions['member_code']);
        }
        // ユーザー名
        if (!empty($conditions['full_name']) && trim($conditions['full_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['full_name']."%'"));
        }
        // ログインID
        if (!empty($conditions['user_id'])) {
            $stmt->where('m.user_id', '=', $conditions['user_id']);
        }
        // お客様番号
        if (!empty($conditions['customer_code'])) {
            $stmt->where(\DB::expr('CAST(mc.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // ユーザー権限
        if (!empty($conditions['user_authority'])) {
            $stmt->where('m.user_authority', '=', $conditions['user_authority']);
        }

        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('m.member_code', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('m.member_code', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    /**
     * 社員マスタレコード取得
     */
    public static function getMemberLoginUser($user_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.member_code', 'member_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'full_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                array('m.user_id', 'user_id'),
                array('m.user_authority', 'user_authority'),
                array('m.lock_status', 'lock_status'),
                array('m.customer_code', 'customer_code')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));

        // 氏名
        $stmt->where('m.user_id', '=', $user_id);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * お客様情報データの取得
     */
    public static function getSearchCustomer($customer_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                    array('mc.customer_code', 'customer_code'),
                    array(\DB::expr("
                        CASE
                            WHEN mc.customer_type = 'individual' THEN '個人'
                            WHEN mc.customer_type = 'corporation' THEN '法人'
                            WHEN mc.customer_type = 'dealer' THEN 'ディーラー'
                            ELSE ''
                        END
                        "), 'customer_type'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'customer_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.name_kana),"'.$encrypt_key.'")'), 'customer_name_kana'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.zip),"'.$encrypt_key.'")'), 'zip'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr1),"'.$encrypt_key.'")'), 'addr1'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.addr2),"'.$encrypt_key.'")'), 'addr2'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.tel),"'.$encrypt_key.'")'), 'tel'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax),"'.$encrypt_key.'")'), 'fax'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.mobile),"'.$encrypt_key.'")'), 'mobile'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.office_name),"'.$encrypt_key.'")'), 'office_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mc.manager_name),"'.$encrypt_key.'")'), 'manager_name'),
                    array('mc.birth_date', 'birth_date'),
                    array(\DB::expr("
                        CASE
                            WHEN mc.sex = 'Man' THEN '男性'
                            WHEN mc.sex = 'Woman' THEN '女性'
                            ELSE ''
                        END
                        "), 'sex'),
                    array('mc.resign_flg', 'resign_flg'),
                    array('mc.resign_date', 'resign_date'),
                    array('mc.resign_reason', 'resign_reason'),
                    array('mc.start_date', 'start_date'),
                    array('mc.end_date', 'end_date')
                );

        // テーブル
        $stmt->from(array('m_customer', 'mc'));

        //削除フラグ
        $stmt->where('mc.del_flg', '=', 'NO');
        // お客様番号
        $stmt->where('mc.customer_code', '=', $customer_code);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

}