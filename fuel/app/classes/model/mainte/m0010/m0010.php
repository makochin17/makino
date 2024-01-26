<?php
namespace Model\Mainte\M0010;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;

class M0010 extends \Model {

    public static $db           = 'MAKINO';
    public static $password     = null;

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // ユーザー権限
    public static function getCsvHeaders($type = 'csv') {

        $res = array();
        switch ($type) {
            case 'csv':
            default:
                $res = array(
                    'member_code'           => '社員コード',
                    'full_name'             => '氏名',
                    'name_furigana'         => 'ふりがな',
                    'mail_address'          => 'メールアドレス',
                    'user_id'               => 'ユーザー名',
                    'user_authority'        => 'ユーザー権限',
                );
                break;
        }

        return $res;
    }
    //=========================================================================//
    //=========================   一括登録・更新   ==============================//
    //=========================================================================//

    /**
     * 入力された列を確認して定義にない列を返す
     */
    public static function checkHeader($row) {
        $header  = self::getCsvHeaders('csv');
        $errors  = array();
        $columns = array_keys($row);
        foreach ($header as $head) {
            if (!in_array($head, $columns)) {
                $errors[] = '列名：['.$head.'] がありません';
            }
        }
        return $errors;
    }
    public static function getConvertData($data) {
        $new_data = array();
        $header   = self::getCsvHeaders('csv');
        foreach ($header as $column => $head) {
            if (isset($data[$head])) {
                switch ($column) {
                    case 'member_code':
                        $tmp = sprintf('%05d', $data[$head]);
                        break;
                    // case 'phone_number':
                    //     $tmp = sprintf('%0'.(strlen($data[$head]) + 1).'d', $data[$head]);
                    //     break;
                    default:
                        $tmp = $data[$head];
                        break;
                }
                $new_data[$column] = $tmp;
            }
        }
        return $new_data;
    }
    /**
     * 会員データインポートする
     */
    public static function import($rows, $db) {

        if (is_null($db)) {
            $db = self::$db;
        }

        \Config::load('message');
        $row_no = 0;
        foreach ($rows as $row) {
            $row_no++;
            /**
             * カラムの確認（最初のみ）
             */
            if ($row_no == 1) {
                $errs = self::checkHeader($row);
                if (!empty($errs)) {
                    $errors[0]= $errs;
                    return $errors;
                }
            }
            $new_row = self::getConvertData($row);

            /**
             * バリデーション
             */
            $error_msg = self::validData($new_row, $db);

            if (!empty($error_msg)) {
                return $error_msg;
            }

            \DB::start_transaction($db);
            try {

                switch (trim($new_row['processing_division'])) {
                    case 'C':       // 登録
                        $error_msg = self::create_record($new_row, $db);
                        break;
                    case 'U':       // 更新
                        $error_msg = self::update_record($new_row, $db);
                        break;
                    case 'D':       // 削除
                        $error_msg = self::delete_record($new_row, $db);
                        break;
                }
                if (!empty($error_msg)) {
                    throw new \Exception();
                }
                \DB::commit_transaction($db);
            } catch (\Exception $e) {
                \DB::rollback_transaction($db);
                if (!empty($error_msg)) {
                    return $error_msg;
                } else {
                    return \Config::get('m_CE0001');
                }
            }
        }
        return null;
    }

    public static function validData($data, $db) {

        switch (trim($data['processing_division'])) {
            case 'C':       // 登録
            case 'U':       // 更新
                if (count($data) != 11) {
                    return str_replace('XXXXX','項目数誤り',\Config::get('m_MW0001'));
                }
                // ユーザーコード
                if (trim($data['member_code']) == '') {
                    return str_replace('XXXXX','項目の入力漏れ【社員コード】',\Config::get('m_MW0001'));
                } elseif (!preg_match('/[0-9]/', trim($data['member_code']))) {
                    return str_replace('XXXXX','データ型誤り【社員コード】',\Config::get('m_MW0001'));
                } elseif (trim($data['member_code']) != '' && strlen(trim($data['member_code'])) != 5) {
                    return str_replace('XXXXX','桁数誤り【社員コード】',\Config::get('m_MW0001'));
                }
                // 氏名
                if (trim($data['full_name']) == '') {
                    return str_replace('XXXXX','項目の入力漏れ【氏名】',\Config::get('m_MW0001'));
                } elseif (trim($data['full_name']) != '' && mb_strlen(trim($data['full_name'])) > 10) {
                    return str_replace('XXXXX','桁数誤り【氏名】',\Config::get('m_MW0001'));
                }
                // ふりがな
                if (trim($data['name_furigana']) == '') {
                    return str_replace('XXXXX','項目の入力漏れ【ふりがな】',\Config::get('m_MW0001'));
                } elseif (trim($data['name_furigana']) != '' && mb_strlen(trim($data['name_furigana'])) > 15) {
                    return str_replace('XXXXX','桁数誤り【ふりがな】',\Config::get('m_MW0001'));
                }
                // メールアドレス
                if (trim($data['mail_address']) == '') {
                    return str_replace('XXXXX','項目の入力漏れ【メールアドレス】',\Config::get('m_MW0001'));
                } elseif (trim($data['mail_address']) != '' && strlen(trim($data['mail_address'])) > 15) {
                    return str_replace('XXXXX','桁数誤り【メールアドレス】',\Config::get('m_MW0001'));
                }
                // ユーザー名
                if (trim($data['user_id']) != '' && !preg_match('/[a-zA-Z0-9]/', trim($data['user_id']))) {
                    return str_replace('XXXXX','データ型誤り【ユーザー名】',\Config::get('m_MW0001'));
                } elseif (trim($data['user_id']) != '' && strlen(trim($data['user_id'])) > 10) {
                    return str_replace('XXXXX','桁数誤り【ユーザー名】',\Config::get('m_MW0001'));
                }
                // ユーザー権限
                if (trim($data['user_authority']) != '' && !preg_match('/[0-9]/', trim($data['user_authority']))) {
                    return str_replace('XXXXX','データ型誤り【ユーザー権限】',\Config::get('m_MW0001'));
                } elseif (trim($data['user_authority']) != '' && strlen(trim($data['user_authority'])) != 1) {
                    return str_replace('XXXXX','桁数誤り【ユーザー権限】',\Config::get('m_MW0001'));
                }
                break;
            case 'D':       // 削除
                if (count($data) != 11) {
                    return str_replace('XXXXX','項目数誤り',\Config::get('m_MW0001'));
                }
                // 社員コード
                if (trim($data['member_code']) == '') {
                    return str_replace('XXXXX','項目の入力漏れ【社員コード】',\Config::get('m_MW0001'));
                } elseif (!preg_match('/[0-9]/', trim($data['member_code']))) {
                    return str_replace('XXXXX','データ型誤り【社員コード】',\Config::get('m_MW0001'));
                } elseif (trim($data['member_code']) != '' && strlen(trim($data['member_code'])) != 5) {
                    return str_replace('XXXXX','桁数誤り【社員コード】',\Config::get('m_MW0001'));
                }
                break;
            default:
                return str_replace('XXXXX','処理区分誤り',\Config::get('m_MW0001'));
                break;
        }
        return null;
    }

    /**
     * エクセル作成処理
     */
    public static function createTsv($db) {
        //出力データ取得
        $header = self::getHeader($db);
        $body = self::getBody($db);

        try {
            \DB::start_transaction($db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0019', \Config::get('m_MI0019'), '', $db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }

            \DB::commit_transaction($db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction($db);
            \Log::error($e->getMessage());
        }

        //ファイル名設定
        $title = mb_convert_encoding('ユーザーマスタ一覧', 'SJIS', 'UTF-8');
        $fileName = $title.'.tsv';

        //HTMLヘッダー
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);

        //ファイルへの書き込み
        $handle = fopen('php://output', 'w');

        mb_convert_variables('SJIS-win', 'UTF-8', $header);
        fputcsv($handle, $header, "\t");

        foreach ($body as $row) {
            mb_convert_variables('SJIS-win', 'UTF-8', $row);
            fputcsv($handle, $row, "\t");
        }

        fclose($handle);

        exit();
    }

    /**
     * TSV用ヘッダー情報取得
     */
    public static function getHeader($db) {

        $result = array();
        $result += array("member_code" => "社員コード");
        $result += array("name" => "氏名");
        $result += array("name_furigana" => "ふりがな");
        $result += array("mail_address" => "メールアドレス");

        return $result;
    }

    /**
     * TSV用データ取得
     */
    public static function getBody($db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 取得データ
        $stmt = \DB::select(
                array('m.member_code', 'member_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name_furigana),"'.$encrypt_key.'")'), 'name_furigana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.mail_address),"'.$encrypt_key.'")'), 'mail_address'),
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        $result = $stmt->order_by('m.member_code', 'ASC')->execute($db)->as_array();
        return $result;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 社員マスタレコード取得
     */
    public static function getMember($code, $db = null) {

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
                array('m.customer_code', 'customer_code'),
                array('m.start_date', 'start_date')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));

        // 社員コード
        $stmt->where('m.member_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * デフォルトパスワード取得
     */
    public static function getPasswordDefault($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return substr(bin2hex(random_bytes(8)), 0, 8);
        // return SystemConfig::getSystemConfig('password_default',$db);
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
     * 車両マスタレコード取得
     */
    public static function getCar($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('m.car_code', 'car_code'),
                array('m.car_model_code', 'car_model_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_name),"'.$encrypt_key.'")'), 'car_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('m.start_date', 'start_date')
                );

        // テーブル
        $stmt->from(array('m_car', 'm'));
        // 車種コード
        $stmt->where('m.car_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', \Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('m.end_date', '>', \Date::forge()->format('mysql_date'));
        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        // レコード存在チェック
        if ($result = self::getMember($conditions['member_code'], $db)) {
            return \Config::get('m_MW0004');
        }

        if (!empty($conditions['user_id'])) {
            // システム設定取得
            self::$password = self::getPasswordDefault($db);
            // ログインユーザ名重複チェック
            if ($result = self::getMemberLoginUser($conditions['user_id'], $db)) {
                return \Config::get('m_MW0012');
            }
            // Authログインユーザ登録
            if (!AuthConfig::CreateLoginUser($conditions['user_id'], self::$password, $conditions)) {
                return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0001'));
            }
        }
        // レコード登録
        if (!self::addMember($conditions, $db)) {
            Log::error(\Config::get('m_ME0003')."[".print_r($conditions,true)."]");
            return \Config::get('m_ME0003');
        }
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0005', $conditions['user_id'].\Config::get('m_MI0005'), '社員マスタ', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 登録
     */
    public static function addMember($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'member_code'           => $data['member_code'],
            'name'                  => \DB::expr('HEX(AES_ENCRYPT("'.$data['full_name'].'","'.$encrypt_key.'"))'),
            'name_furigana'         => \DB::expr('HEX(AES_ENCRYPT("'.$data['name_furigana'].'","'.$encrypt_key.'"))'),
            'mail_address'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['mail_address'].'","'.$encrypt_key.'"))'),
            'user_id'               => (!empty($data['user_id'])) ? $data['user_id']:NULL,
            'user_authority'        => (!empty($data['user_authority'])) ? $data['user_authority']:NULL,
            'password_limit'        => date('Y-m-d', strtotime('+7 day')),
            'password_error_count'  => 0,
            'lock_status'           => 0,
            'customer_code'         => (!empty($data['customer_code'])) ? $data['customer_code']:NULL,
            'start_date'            => \Date::forge()->format('mysql_date'),
            'end_date'              => \Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')

        );

        $duplicate_key = " ON DUPLICATE KEY UPDATE `member_code` = VALUES(`member_code`)"
                . ", `name` = VALUES(`name`)"
                . ", `name_furigana` = VALUES(`name_furigana`)"
                . ", `mail_address` = VALUES(`mail_address`)"
                . ", `user_id` = VALUES(`user_id`)"
                . ", `user_authority` = VALUES(`user_authority`)"
                . ", `password_limit` = VALUES(`password_limit`)"
                . ", `password_error_count` = VALUES(`password_error_count`)"
                . ", `lock_status` = VALUES(`lock_status`)"
                . ", `customer_code` = VALUES(`customer_code`)"
                . ", `end_date` = VALUES(`end_date`)"
                . ", `update_user` = VALUES(`update_user`)"
                . ", `update_datetime` = VALUES(`update_datetime`)";

        $set    = array_merge($set, self::getEtcData(true));
        $query  = \DB::insert('m_member')->set($set);
        $result = \DB::query($query->compile().$duplicate_key)->execute($db);

        if($result[1] > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード存在チェック
        if (!$result = self::getMember($conditions['member_code'], $db)) {
            return \Config::get('m_MW0005');
        }
        $start_date = $result[0]['start_date'];
        $old_user_id = $result[0]['user_id'];

        // ログインユーザ名重複チェック
        if (!empty($conditions['user_id']) && $old_user_id != $conditions['user_id']) {
            if ($result = self::getMemberLoginUser($conditions['user_id'], $db)) {
                return \Config::get('m_MW0012');
            }
        }

        // レコード更新
        // 取得レコードの「適用開始日」がシステム日付より過去日か
        if (strtotime($start_date) < strtotime(\Date::forge()->format('mysql_date'))) {

            // レコード削除（論理）
            if (!self::delMember($conditions['member_code'], $db)) {
                Log::error(\Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                return \Config::get('m_ME0004');
            }

            // レコード登録
            if (!self::addMember($conditions, $db)) {
                Log::error(\Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                return \Config::get('m_ME0004');
            }
        } else {
            //　レコード更新
            if (!self::updMember($conditions, $db)) {
                Log::error(\Config::get('m_ME0004')."[".print_r($conditions,true)."]");
                return \Config::get('m_ME0004');
            }
        }
        // Authログインユーザ更新
        if (!empty($conditions['user_id'])) {
            if (!empty($old_user_id) && $old_user_id != $conditions['user_id']) {

                // Authログインユーザ削除
                if (!AuthConfig::DeleteLoginUser($old_user_id)) {
                    return str_replace('XXXXX',$old_user_id,\Config::get('m_ME0002'));
                }

                // システム設定取得
                self::$password = self::getPasswordDefault($db);
                // Authログインユーザ登録
                if (!AuthConfig::CreateLoginUser($conditions['user_id'], self::$password, $conditions)) {
                    return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0001'));
                }
            } elseif(empty($old_user_id) && !AuthConfig::CheckLoginUser($conditions['user_id'], $db)) {

                // システム設定取得
                self::$password = self::getPasswordDefault($db);
                // Authログインユーザ登録
                if (!AuthConfig::CreateLoginUser($conditions['user_id'], self::$password, $conditions)) {
                    return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0001'));
                }
            } else {
                if (!AuthConfig::UpdateLoginUser($conditions, $conditions['user_id'])) {
                    return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0001'));
                }
            }
        } else {
            if (!empty($old_user_id)) {
                // Authログインユーザ削除
                if (!AuthConfig::DeleteLoginUser($old_user_id)) {
                    return str_replace('XXXXX',$old_user_id,\Config::get('m_ME0002'));
                }
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', \Config::get('m_MI0006'), '社員マスタ', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 社員マスタ更新
     */
    public static function updMember($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // テーブル
        $stmt = \DB::update('m_member');
        // 項目セット
        $set = array(
            'name'                  => \DB::expr('HEX(AES_ENCRYPT("'.$data['full_name'].'","'.$encrypt_key.'"))'),
            'name_furigana'         => \DB::expr('HEX(AES_ENCRYPT("'.$data['name_furigana'].'","'.$encrypt_key.'"))'),
            'mail_address'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['mail_address'].'","'.$encrypt_key.'"))'),
            'user_id'               => $data['user_id'],
            'user_authority'        => $data['user_authority'],
            'customer_code'         => (!empty($data['customer_code'])) ? $data['customer_code']:NULL,
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));

        // 車種コード
        $stmt->where('member_code', '=', $data['member_code']);
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

        // レコード存在チェック
        if (!$result = self::getMember($conditions['member_code'], $db)) {
            return \Config::get('m_MW0005');
        }

        if (!empty($conditions['user_id'])) {
            // Authログインユーザ削除
            if (!AuthConfig::DeleteLoginUser($conditions['user_id'])) {
                return str_replace('XXXXX',$conditions['user_id'],\Config::get('m_ME0002'));
            }

        }
        // レコード削除（論理）
        if (!self::delMember($conditions['member_code'], $db)) {
            Log::error(\Config::get('m_ME0005')."[member_code:".$conditions['member_code']."]");
            return \Config::get('m_ME0005');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', \Config::get('m_MI0007'), 'ユーザーマスタ', $db);
        if (!$result) {
            Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 社員マスタ削除（論理削除）
     */
    public static function delMember($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($code)) {
            return false;
        }

        // テーブル
        $stmt = \DB::update('m_member');

        // 項目セット
        $set = array(
            'end_date' => \Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 社員コード
        $stmt->where('member_code', '=', $code);
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
     * ユーザロックアウト解除
     */
    public static function unlockMember($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($code)) {
            return false;
        }

        // テーブル
        $stmt = \DB::update('m_member');

        // 項目セット
        $set = array(
            'password_error_count'  => 0,
            'lock_status'           => 0
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 社員コード
        $stmt->where('member_code', '=', $code);
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
     * パスワード初期化
     */
    public static function initializePassword($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($code)) {
            return false;
        }

        // テーブル
        $stmt = \DB::update('m_member');

        // 項目セット
        $set = array(
            'password_limit'        => date('Y-m-d', strtotime('+7 day')),
            'password_error_count'  => 0,
            'lock_status'           => 0
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 社員コード
        $stmt->where('member_code', '=', $code);
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

        if (!$user_name = AuthConfig::getAuthConfig('user_id')) {
            $user_name = AuthConfig::getAuthConfig('user_name');
        }

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => $user_name,
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => $user_name
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => $user_name
            );
            break;
        }
        return $data;
    }

}