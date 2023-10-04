<?php
namespace Model\Customer;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class C0010 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * お客様レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(mc.customer_code) AS count'));
                break;
            case 'search':
            default:
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
                break;
        }

        // テーブル
        $stmt->from(array('m_customer', 'mc'));

        // お客様番号
        if (!empty($conditions['customer_code'])) {
            $stmt->where(\DB::expr('CAST(mc.customer_code AS SIGNED)'), '=', $conditions['customer_code']);
        }
        // お客様名
        if (!empty($conditions['customer_name']) && trim($conditions['customer_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name']."%'"));
        }
        // お客様名かな
        if (!empty($conditions['customer_name_kana']) && trim($conditions['customer_name_kana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.name_kana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['customer_name_kana']."%'"));
        }
        // お客様区分
        if (!empty($conditions['customer_type'])) {
            $stmt->where('mc.customer_type', '=', $conditions['customer_type']);
        }

        $stmt->where('mc.del_flg', '=', 'NO');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('mc.customer_code', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('mc.customer_code', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    /**
     * お客様データの取得（退会フラグ選択）
     */
    public static function getCustomerByCode($customer_code, $resign_flg = 'NO', $db = null) {

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
        //退会フラグ
        $stmt->where('mc.resign_flg', '=', $resign_flg);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * データ削除
     */
    public static function deleteRecord($customer_code, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // 売上ステータス取得
        if (self::getCustomer($customer_code, $db)) {
            // レコード削除
            $result = self::delCustomer($customer_code, $db);
            if (!$result) {
                \Log::error(\Config::get('m_CUS010')."[customer_code:".$customer_code."]");
                return \Config::get('m_CUS010');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('CUS007', AuthConfig::getAuthConfig('user_name').\Config::get('m_CUS007'), 'お客様情報削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * お客様データ取得
     */
    public static function getCustomer($customer_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('customer_code', 'customer_code')
                );
        // テーブル
        $stmt->from('m_customer');
        // 配車コード
        $stmt->where('customer_code', '=', $customer_code);

        // 検索実行
        return $stmt->execute($db)->current();
    }

    /**
     * お客様データ削除
     */
    public static function delCustomer($customer_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($customer_code)) {
            return false;
        }

        // 項目セット
        $set = array(
            'end_date' => \Date::forge()->format('mysql_date'),
            'del_flg' => 'YES',
        );

        // テーブル
        $stmt = \DB::update('m_customer')->set(array_merge($set, self::getEtcData(false)));

        // お客様コード
        $stmt->where('customer_code', '=', $customer_code);
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('start_date', '<=', \Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', \Date::forge()->format('mysql_date'));

        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
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