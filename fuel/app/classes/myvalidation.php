<?php
/**
 * バリデーション独自ルール
 */

class MyValidation {

    public static $db           = 'MAKINO';

    /**
     * Special empty method because 0 and '0' are non-empty values
     *
     * @param   mixed
     * @return  bool
     */
    public static function _validation_required_brank($val)
    {
        // return ($val === false or $val === null or $val === '' or $val === '-' or $val === '0' or $val === array());
        if ($val == '') {
            return false;
        }
        return true;
    }

    /**
     * Special empty method because 0 and '0' are non-empty values
     *
     * @param   mixed
     * @return  bool
     */
    public static function _validation_required_select($val)
    {
        // return ($val === false or $val === null or $val === '' or $val === '-' or $val === '0' or $val === array());
        if (empty($val)) {
            return false;
        }
        return true;
    }

    /**
     * Maximum string length
     *
     * original: fuel/core/classes/validation.php
     * function _validation_max_length
     *
     * @param   string
     * @param   int
     * @return  bool
     */
    public static function _validation_trim_max_lengths($val, $length)
    {
        $val   = str_replace(array("\r\n", "\r"), array("\n", "\n"), $val);
        $tmp   = explode("\n", $val);
        $array = array();
        if (!empty($tmp)) {
            foreach ($tmp as $v) {
                if (trim($v) != '') {
                    $array[] = trim($v);
                }
            }
        }
        if (empty($array))
        {
            return true;
        }
        $flg = true;
        foreach ($array as $val) {
            if (\Str::length(trim($val)) > $length) {
                $flg = false;
                break;
            }
        }
        return $flg;
    }

    /**
     * Maximum string length
     *
     * original: fuel/core/classes/validation.php
     * function _validation_max_length
     *
     * @param   string
     * @param   int
     * @return  bool
     */
    public static function _validation_trim_max_lengths_int($val, $length)
    {
        $val   = str_replace(',', '', $val);
        $val   = str_replace(array("\r\n", "\r"), array("\n", "\n"), intval($val));
        $tmp   = explode("\n", $val);
        $array = array();
        if (!empty($tmp)) {
            foreach ($tmp as $v) {
                if (trim($v) != '') {
                    $array[] = trim($v);
                }
            }
        }
        if (empty($array))
        {
            return true;
        }
        $flg = true;
        foreach ($array as $val) {
            if (\Str::length(trim($val)) > $length) {
                $flg = false;
                break;
            }
        }
        return $flg;
    }

    public static function _validation_is_space($val)
    {

        if (preg_match('/( |　)+/', $val)) {
            return false;
        }
        return true;
    }

    public static function _validation_is_trim($val)
    {

        if (preg_match('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', $val)) {
            return false;
        }
        return true;
    }

    /**
     * Validate input string with many options
     *
     * original: fuel/core/classes/validation.php
     * function _validation_valid_string
     *
     * @param   string
     * @param   string|array  either a named filter or combination of flags
     * @return  bool
     */
    public static function _validation_valid_strings($val, $flags = array('alpha', 'utf8'))
    {
        $val   = str_replace(array("\r\n", "\r"), array("\n", "\n"), $val);
        $tmp   = explode("\n", $val);
        $array = array();
        if (!empty($tmp)) {
            foreach ($tmp as $v) {
                if (trim($v) != '') {
                    $array[] = trim($v);
                }
            }
        }
        if (empty($array))
        {
            return true;
        }
        if ( ! is_array($flags))
        {
            if ($flags == 'alpha')
            {
                $flags = array('alpha', 'utf8');
            }
            elseif ($flags == 'alpha_numeric')
            {
                $flags = array('alpha', 'utf8', 'numeric');
            }
            elseif ($flags == 'url_safe')
            {
                $flags = array('alpha', 'numeric', 'dashes');
            }
            elseif ($flags == 'integer' or $flags == 'numeric')
            {
                $flags = array('numeric');
            }
            elseif ($flags == 'float')
            {
                $flags = array('numeric', 'dots');
            }
            elseif ($flags == 'quotes')
            {
                $flags = array('singlequotes', 'doublequotes');
            }
            elseif ($flags == 'all')
            {
                $flags = array('alpha', 'utf8', 'numeric', 'spaces', 'newlines', 'tabs', 'punctuation', 'singlequotes', 'doublequotes', 'dashes');
            }
            else
            {
                return false;
            }
        }

        $pattern = ! in_array('uppercase', $flags) && in_array('alpha', $flags) ? 'a-z' : '';
        $pattern .= ! in_array('lowercase', $flags) && in_array('alpha', $flags) ? 'A-Z' : '';
        $pattern .= in_array('numeric', $flags) ? '0-9' : '';
        $pattern .= in_array('spaces', $flags) ? ' ' : '';
        $pattern .= in_array('newlines', $flags) ? "\n" : '';
        $pattern .= in_array('tabs', $flags) ? "\t" : '';
        $pattern .= in_array('dots', $flags) && ! in_array('punctuation', $flags) ? '\.' : '';
        $pattern .= in_array('commas', $flags) && ! in_array('punctuation', $flags) ? ',' : '';
        $pattern .= in_array('punctuation', $flags) ? "\.,\!\?:;\&" : '';
        $pattern .= in_array('dashes', $flags) ? '_\-' : '';
        $pattern .= in_array('singlequotes', $flags) ? "'" : '';
        $pattern .= in_array('doublequotes', $flags) ? "\"" : '';
        $pattern = empty($pattern) ? '/^(.*)$/' : ('/^(['.$pattern.'])+$/');
        $pattern .= in_array('utf8', $flags) ? 'u' : '';

        $flg = true;
        foreach ($array as $val) {
            if (!(preg_match($pattern, $val) > 0)) {
                $flg = false;
                break;
            }
        }
        return $flg;
    }

    /**
     * ログインIDからユーザーマスタ重複チェック
     */
    public static function _validation_user_duplicate($val) {

        if (is_null($val)) {
            return false;
        }
        $res = \DB::select()
        ->from('m_user')
        ->where('del_flg', 'NO')
        ->where('login_id', $val)
        ->execute(self::$db)
        ->current();

        return empty($res) ? true : false;
    }

    /**
     * MCC/MNC/PLMNコードの桁数と整合性チェック
     */
    public static function _validation_valid_num_len($val, $len) {
        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }
        if (preg_match('/^[0-9]+$/', $val) && strlen($val) == $len) {
            return true;
        }
        return false;
    }

    /**
     * IDまたは名称存在確認（マスタ系）
     */
    public static function _validation_master_duplicate($val, $type, $table) {

        if (is_null($val) || empty($type) || empty($table)) {
            return false;
        }
        $res = \DB::select()
        ->from($table)
        ->where('del_flg', 'NO')
        ->where($type, $val)
        ->execute(self::$db)
        ->as_array();

        return empty($res) ? true : false;
    }

    /**
     * IDまたは名称存在確認（マスタ系）
     */
    public static function _validation_master_other_duplicate($val, $type, $id, $table) {

        if (is_null($val) || empty($type) || empty($table)) {
            return false;
        }
        $res = \DB::select()
        ->from($table)
        ->where('del_flg', 'NO')
        ->where('id', '!=', $id)
        ->where($type, $val)
        ->execute(self::$db)
        ->as_array();

        return empty($res) ? true : false;
    }

    /**
     * 退職フラグの確認 user_master.resign_flg
     */
    public static function _validation_valid_resign_flg($resign_flg) {
        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($resign_flg == '') {
            return true;
        }
        $resign = array('YES', 'NO');
        return in_array($resign_flg, $resign) ? true : false;
    }

    /**
     * ログインパスワードの重複チェック
     */
    public static function _validation_valid_duplicate_pw($new, $old) {
        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($new == '') {
            return true;
        }

        return ($new != $old) ? true : false;
    }

    /**
     * ログインパスワードの8文字以上半角英数字大文字含チェック
     */
    public static function _validation_new_passwd_match($val) {
        if (empty($val)) {
            return true;
        }
        // 半角英数字8文字以上50文字以下チェック
        if (!preg_match("/\A(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)[a-zA-Z\d]{8,50}+\z/", $val)) {
            \Validation::active()->set_message('new_passwd_match', '大文字を含む半角英数字8文字以上50文字以下で設定してください');
            return false;
        }

        return true;
    }

    /**
     * ログインパスワードの8文字以上半角英数字含チェック
     */
    public static function _validation_new_passwd_low_match($val) {
        if (empty($val)) {
            return true;
        }
        // 半角英数字8文字以上50文字以下チェック
        if (!preg_match("/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i", $val)) {
            \Validation::active()->set_message('new_passwd_low_match', '半角英数字8文字以上50文字以下で設定してください');
            return false;
        }

        return true;
    }

    /**
     * 数字のみか？
     */
    public static function _validation_is_numeric($val) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }

        // 数字のみか？
        if (preg_match('/^[0-9]+$/', $val)) {
            return true;
        }

        return false;

    }

    /**
     * 数字のみか？（小数点を含む判定）
     * $mode true:カンマを取り除く false:カンマを取り除かない
     */
    public static function _validation_is_numeric_decimal($val, $deci, $mode=false) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }
        
        // カンマを取り除く
        if ($mode) {
            $val = str_replace(',', '', $val);
        }

        // 小数点を含む数字のみか？
        if (preg_match( '/^[0-9]+(.[0-9]{1,' . $deci . '})?$/', $val)) {
            return true;
        } elseif(preg_match( '/^[0-9]+$/', $val)) {
            // 小数点がない場合も数字のみなら通す
            return true;
        }

        return false;

    }

    /**
     * 数字のみか？（小数点を含まない判定）
     * $mode true:カンマを取り除く false:カンマを取り除かない
     */
    public static function _validation_is_numeric_conma($val) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }

        // カンマを取り除く
        $val = str_replace(',', '', $val);

        // 小数点を含む数字のみか？
        if (preg_match( '/^[0-9]+$/', $val)) {
            return true;
        }

        return false;

    }

    /**
     * 日付チェック
     */
    public static function _validation_valid_date_format($date) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($date == '') {
            return true;
        }

        $date = str_replace('/', '-', $date);
        if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $m)) {
            // yyyy-mm-dd形式でない場合
            return false;
        }
        return checkdate($m[2], $m[3], $m[1]);

   }

    /**
     * 日時形式チェック
     */
    public static function _validation_valid_datetime_format($date) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($date == '') {
            return true;
        }

        $date = str_replace('/', '-', $date);
        if (!preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})\s+([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/', $date, $m)) {
            // yyyy-mm-dd形式でない場合
            return false;
        }

        // 時刻チェック
        if ((int)$m[4] < 0 || (int)$m[4] > 23) {
            return false;
        }
        if ((int)$m[5] < 0 || (int)$m[5] > 59) {
            return false;
        }
        if ((int)$m[6] < 0 || (int)$m[6] > 59) {
            return false;
        }

        // 日付チェック
        return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);

   }

    /**
     * メールチェック
     */
    public static function _validation_valid_mail($mail) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($mail == '') {
            return true;
        }

        if (preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $mail, $m)) {
            return true;
        }

        return false;

   }

    /**
     * 会員コードチェック
     */
    public static function _validation_valid_member_code($member_code) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($member_code == '') {
            return true;
        }

        if (preg_match('/[0-9a-zA-Z]{4,20}/', $member_code)) {
            return true;
        }

        return false;

   }
   
   /**
     * 半角カタカナチェック
     */
    public static function _validation_is_half_katakana($val) {
        
        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($val == '') {
            return true;
        }

        if (preg_match('/\A[ｦ-ﾟ]+\z/u', $val)) {
            return true;
        }

        return false;

   }

    /**
     * 納品日＆引取日チェック
     */
    public static function _validation_delivery_and_pickup_required_date($date1, $date2) {

        // date1とdate2のいずれか、もしくは両方が入力されていること
        if (empty($date1) && empty($date2)) {
            return false;
        }
    }

    /**
     * 金額整合性チェック
     */
    public static function _validation_amount_check($data1, $data2) {

        if (empty($data1) || empty($data2)) {
            return true;
        }

        $data1 = intval(str_replace(',', '', $data1));
        $data2 = intval(str_replace(',', '', $data2));

        if ($data1 != $data2) {
            return false;
        }

        return true;
    }

    /**
     * 存在確認
     */
    public static function _validation_table_duplicate($val, $type, $table) {

        if (is_null($val) || empty($type) || empty($table)) {
            return false;
        }
        $res = \DB::select()
        ->from($table)
        ->where('delete_flag', 0)
        ->where($type, $val)
        ->execute(self::$db)
        ->as_array();

        return empty($res) ? true : false;
    }

}
