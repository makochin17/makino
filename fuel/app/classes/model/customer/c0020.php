<?php
namespace Model\Customer;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class C0020 extends \Model {

    public static $db           = 'MAKINO';

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

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
            case 'customer':
            default:
                $res = array(
                    'mode'                  => '',
                    'customer_type'         => '',
                    'customer_code'         => '',
                    'customer_name'         => '',
                    'customer_name_kana'    => '',
                    'zip'                   => '',
                    'addr1'                 => '',
                    'addr2'                 => '',
                    'tel'                   => '',
                    'fax'                   => '',
                    'mobile'                => '',
                    'mail_address'          => '',
                    'office_name'           => '',
                    'manager_name'          => '',
                    'birth_date'            => '',
                    'sex'                   => '',
                    'resign_flg'            => '',
                    'resign_date'           => '',
                    'resign_reason'         => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'customer', $conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if (isset($input_data[$key])) {
                $conditions[$key] = $input_data[$key];
            }
        }

        return $conditions;
    }

    //=========================================================================//
    //=============================   お客様データ  =============================//
    //=========================================================================//
    /**
     * レコード取得
     */
    public static function getCustomer($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.customer_code', 'customer_code'),
                array(\DB::expr("
                        CASE
                            WHEN m.customer_type = 'individual' THEN '個人'
                            WHEN m.customer_type = 'corporation' THEN '法人'
                            WHEN m.customer_type = 'dealer' THEN 'ディーラー'
                            ELSE ''
                        END
                        "), 'customer_type'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'customer_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_kana),"'.$encrypt_key.'")'), 'customer_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.zip),"'.$encrypt_key.'")'), 'zip'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.addr1),"'.$encrypt_key.'")'), 'addr1'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.addr2),"'.$encrypt_key.'")'), 'addr2'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.tel),"'.$encrypt_key.'")'), 'tel'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.fax),"'.$encrypt_key.'")'), 'fax'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mobile),"'.$encrypt_key.'")'), 'mobile'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.office_name),"'.$encrypt_key.'")'), 'office_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.manager_name),"'.$encrypt_key.'")'), 'manager_name'),
                array('m.birth_date', 'birth_date'),
                array('m.sex', 'sex'),
                array('m.resign_flg', 'resign_flg'),
                array('m.resign_date', 'resign_date'),
                array('m.resign_reason', 'resign_reason')
                );

        // テーブル
        $stmt->from(array('m_customer', 'm'));

        // お客様コード
        $stmt->where('m.customer_code', '=', $code);
        // 削除フラグ
        $stmt->where('m.del_flg', '=', 'NO');
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->current();
    }

}