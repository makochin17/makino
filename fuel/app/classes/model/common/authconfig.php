<?php
namespace Model\Common;

class AuthConfig extends \Model {

    public static $db       = 'MAKINO';

    /**
     * システム設定値取得
     * $item_name 取得する項目名
     */
    public static function getAuthConfig($item_name = 'all') {

        $item = null;
        switch ($item_name) {
            case 'id':
                $item = \Auth::get_user_id();
                break;
            case 'name':
                $item = \Auth::get_screen_name();
                if (empty($item)) {
                    $item = \Auth::get_profile_fields('full_name', '');
                }
                break;
            case 'user_name':
                $item = \Auth::get_screen_name();
                break;
            case 'member_code':
                $item = \Auth::get_profile_fields('member_code', '');
                break;
            case 'user_id':
                $item = \Auth::get_profile_fields('user_id', '');
                break;
            case 'user_authority':
                $item = \Auth::get_profile_fields('user_authority', '');
                break;
            case 'lock_status':
                $item = \Auth::get_profile_fields('lock_status', '');
                break;
            case 'customer_code':
                $item = \Auth::get_profile_fields('customer_code', '');
                break;
            case 'all':
            default:
                $item = array();
                $item = array(
                    'login_user_id'     => \Auth::get_user_id(),
                    'login_user_name'   => \Auth::get_screen_name(),
                    'member_code'       => \Auth::get_profile_fields('member_code', ''),
                    'full_name'         => \Auth::get_profile_fields('full_name', ''),
                    'name_furigana'     => \Auth::get_profile_fields('name_furigana', ''),
                    'mail_address'      => \Auth::get_profile_fields('mail_address', ''),
                    'user_id'           => \Auth::get_profile_fields('user_id', ''),
                    'customer_code'     => \Auth::get_profile_fields('customer_code', ''),
                    'user_authority'    => \Auth::get_profile_fields('user_authority', ''),
                    'lock_status'       => \Auth::get_profile_fields('lock_status', ''),
                );
                break;
        }

        return $item;
    }

    /**
     * ログインユーザー登録
     */
    public static function CreateLoginUser($login_name, $login_password, $conditions) {

        if (empty($login_name) || empty($login_password)) {
            return false;
        }

        $profile_fields = array(
            'member_code'       => $conditions['member_code'],
            'full_name'         => $conditions['full_name'],
            'name_furigana'     => $conditions['name_furigana'],
            'mail_address'      => $conditions['mail_address'],
            'user_id'           => $conditions['user_id'],
            'customer_code'     => $conditions['customer_code'],
            'user_authority'    => $conditions['user_authority'],
            'lock_status'       => $conditions['lock_status']
        );

        // Authインスタンス
        $auth   = \Auth::instance();
        // 初期PWはシステム設定テーブルのパスワードにする
        return $auth->create_user($login_name, $login_password, $login_name.$conditions['member_code'].'@system.jp', $conditions['user_authority'], $profile_fields);

    }

    /**
     * ログインユーザーパスワード変更
     */
    public static function ChangePasswordLoginUser($old_password, $new_password, $user_id) {

        if (empty($old_password) || empty($new_password) || empty($user_id)) {
            return false;
        }

        // Authインスタンス
        $auth   = \Auth::instance();

        return $auth->change_password($old_password, $new_password, $user_id);

    }

    /**
     * ログインユーザー更新
     */
    public static function UpdateLoginUser($conditions, $user_id) {

        if (empty($conditions) || empty($user_id)) {
            return false;
        }

        if (array_key_exists('username', $conditions)) {
            $values['username']     = $conditions['user_id'];
        } elseif (array_key_exists('group', $conditions)) {
            $values['group']        = $conditions['user_authority'];
        } elseif (array_key_exists('email', $conditions)) {
            $values['email']        = $conditions['email'];
        } else {
            $values = array(
                'member_code'       => $conditions['member_code'],
                'full_name'         => $conditions['full_name'],
                'name_furigana'     => $conditions['name_furigana'],
                'mail_address'      => $conditions['mail_address'],
                'user_id'           => $conditions['user_id'],
                'customer_code'     => $conditions['customer_code'],
                'user_authority'    => $conditions['user_authority'],
                'lock_status'       => $conditions['lock_status']
            );
        }

        // Authインスタンス
        $auth   = \Auth::instance();
        // 初期PWはシステム設定テーブルのパスワードにする
        return $auth->update_user($values, $user_id);

    }

    /**
     * ログインユーザー削除
     */
    public static function DeleteLoginUser($user_id) {

        if (empty($user_id)) {
            return false;
        }

        // Authインスタンス
        $auth   = \Auth::instance();
        // 初期PWはシステム設定テーブルのパスワードにする
        return $auth->delete_user($user_id);

    }

    /**
     * ログインユーザーチェック
     */
    public static function CheckLoginUser($user_id, $db = null) {

        if (empty($user_id)) {
            return false;
        }
        if (is_null($db)) {
            $db = self::$db;
        }

        return \DB::select()
        ->from('login_users')
        ->where('username', $user_id)
        ->execute($db)->current()
        ;

    }

}