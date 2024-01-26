<?php
namespace Model\Customer;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class C0011 extends \Model {

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

    // ヘッダーデータ
    public static function getHeaders($type = 'csv') {

        $res = array();
        switch ($type) {
            case 'customer':
            case 'csv':
            default:
                $res = array(
                    'customer_type'         => 'お客様区分',
                    'customer_code'         => 'お客様番号',
                    'customer_name'         => 'お客様名',
                    'customer_name_kana'    => 'お客様名かな',
                    'zip'                   => '郵便番号',
                    'addr1'                 => '住所１',
                    'addr2'                 => '住所２',
                    'tel'                   => '電話番号',
                    'fax'                   => 'FAX番号',
                    'mobile'                => '携帯番号',
                    'mail_address'          => 'メールアドレス',
                    'office_name'           => '勤務先名',
                    'manager_name'          => '担当者名',
                    'birth_date'            => '生年月日',
                    'sex'                   => '性別',
                    'resign_flg'            => '退会フラグ',
                    'resign_date'           => '退会日',
                    'resign_reason'         => '退会理由',
                );
                break;
        }

        return $res;
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
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録(配車データ)
        $insert_id = self::addCustomer($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_CUS008')."[".print_r($conditions,true)."]");
            return \Config::get('m_CUS008');
        }

        // 操作ログ出力
        // $result = OpeLog::addOpeLog('DI0009', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0009'), '配車登録', $db);
        // if (!$result) {
        //    \Log::error(\Config::get('m_CE0007'));
        //    return \Config::get('m_CE0007');
        // }
        return null;
    }

    /**
     * お客様登録
     */
    public static function addCustomer($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'customer_type'         => $data['customer_type'],
            'customer_code'         => $data['customer_code'],
            'name'                  => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))'),
            'name_kana'             => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name_kana'].'","'.$encrypt_key.'"))'),
            'zip'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['zip'].'","'.$encrypt_key.'"))'),
            'addr1'                 => \DB::expr('HEX(AES_ENCRYPT("'.$data['addr1'].'","'.$encrypt_key.'"))'),
            'addr2'                 => (!empty($data['addr2'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['addr2'].'","'.$encrypt_key.'"))'):null,
            'tel'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['tel'].'","'.$encrypt_key.'"))'),
            'fax'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['fax'].'","'.$encrypt_key.'"))'),
            'mobile'                => \DB::expr('HEX(AES_ENCRYPT("'.$data['mobile'].'","'.$encrypt_key.'"))'),
            'mail_address'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['mail_address'].'","'.$encrypt_key.'"))'),
            'office_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['office_name'].'","'.$encrypt_key.'"))'),
            'manager_name'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['manager_name'].'","'.$encrypt_key.'"))'),
            'birth_date'            => (!empty($data['birth_date'])) ? date('Y-m-d', strtotime($data['birth_date'])):null,
            'sex'                   => $data['sex'],
            'resign_flg'            => (!empty($data['resign_flg'])) ? $data['resign_flg']:'NO',
            'resign_date'           => (!empty($data['resign_date'])) ? date('Y-m-d', strtotime($data['resign_date'])):null,
            'resign_reason'         => (!empty($data['resign_reason'])) ? $data['resign_reason']:null,
            'start_date'            => \Date::forge()->format('mysql_date'),
            'end_date'              => \Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('m_customer')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        $bill_number = "";

        // レコード更新
        if ($result = self::getCustomer($conditions['customer_code'], $db)) {
            if (!self::updCustomer($conditions, $db)) {
                \Log::error(\Config::get('m_CUS009')."[customer_code:".$conditions['customer_code']."]");
                return \Config::get('m_CUS009');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('CUS005', AuthConfig::getAuthConfig('user_name').\Config::get('m_CUS005'), 'お客様更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * お客様情報更新
     */
    public static function updCustomer($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'customer_type'         => $data['customer_type'],
            'customer_code'         => $data['customer_code'],
            'name'                  => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name'].'","'.$encrypt_key.'"))'),
            'name_kana'             => \DB::expr('HEX(AES_ENCRYPT("'.$data['customer_name_kana'].'","'.$encrypt_key.'"))'),
            'zip'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['zip'].'","'.$encrypt_key.'"))'),
            'addr1'                 => \DB::expr('HEX(AES_ENCRYPT("'.$data['addr1'].'","'.$encrypt_key.'"))'),
            'addr2'                 => (!empty($data['addr2'])) ? \DB::expr('HEX(AES_ENCRYPT("'.$data['addr2'].'","'.$encrypt_key.'"))'):null,
            'tel'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['tel'].'","'.$encrypt_key.'"))'),
            'fax'                   => \DB::expr('HEX(AES_ENCRYPT("'.$data['fax'].'","'.$encrypt_key.'"))'),
            'mobile'                => \DB::expr('HEX(AES_ENCRYPT("'.$data['mobile'].'","'.$encrypt_key.'"))'),
            'mail_address'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['mail_address'].'","'.$encrypt_key.'"))'),
            'office_name'           => \DB::expr('HEX(AES_ENCRYPT("'.$data['office_name'].'","'.$encrypt_key.'"))'),
            'manager_name'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['manager_name'].'","'.$encrypt_key.'"))'),
            'birth_date'            => (!empty($data['birth_date'])) ? date('Y-m-d', strtotime($data['birth_date'])):null,
            'sex'                   => $data['sex'],
            'resign_flg'            => (!empty($data['resign_flg'])) ? $data['resign_flg']:'NO',
            'resign_date'           => (!empty($data['resign_date'])) ? date('Y-m-d', strtotime($data['resign_date'])):null,
            'resign_reason'         => (!empty($data['resign_reason'])) ? $data['resign_reason']:null,
        );

        // テーブル
        $stmt = \DB::update('m_customer')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('customer_code', '=', $data['customer_code']);
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

    //=========================================================================//
    //==============================   対象削除   ==============================//
    //=========================================================================//
    public static function delete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        if ($result = self::getCustomer($conditions['customer_code'], $db)) {
            if (!self::delCustomer($conditions['customer_code'], $db)) {
                \Log::error(\Config::get('m_CUS010')."[customer_code:".$conditions['customer_code']."]");
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
     * 配車データ削除
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
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
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