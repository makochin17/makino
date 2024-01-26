<?php
/**
 * @class use  アクセスコントロール用クラス
 */
namespace Model;

class AccessControl extends \Model {

    public static function hasPermission($permission_level) {

        // 許可リスト確認
        $allow = self::isAllowedPermission($permission_level);

        // 不許可リスト確認
        $deny  = self::isDeniedPermission($permission_level);

        return $allow && $deny;

    }

    public static function getActiveController() {
        $controller = \Request::active()->controller;
        $controller = strtolower($controller);
        $controller = str_replace(array('controller_', '_'), array('', '/'), $controller);
        return $controller;
    }

    public static function getActiveAction() {
        $controller = self::getActiveController();
        $action     = strtolower(\Request::active()->action);
        return $controller.'/'.$action;
    }

    /**
     * メソッドへのアクセス権があるか？
     * ※許可リスト
     */
    public static function isAllowedPermission($permission_level) {

        $res = false;

        /**
         * アクセス許可リストに含まれているか？
         */
        if (!$permission_allowed = \Config::get('permissionallowed')) {

            $permission_allowed  = \Config::load('permissionallowed', 'permissionallowed');

        }

        if (isset($permission_allowed[$permission_level])) {

            if (in_array(self::getActiveController(), $permission_allowed[$permission_level])) {

                $res = true;

            } else if (in_array(self::getActiveAction(), $permission_allowed[$permission_level])) {

                $res = true;

            }

        } else {

            // 設定自体がなければ true
            $res = true;

        }

        return $res;

    }

    /**
     * メソッドへのアクセス権があるか？
     * ※不許可リスト
     */
    public static function isDeniedPermission($permission_level) {

        $res = true;

        /**
         * アクセス許可リストに含まれているか？
         */
        if (!$permission_allowed = \Config::get('permissiondenied')) {

            $permission_allowed  = \Config::load('permissiondenied', 'permissiondenied');

        }

        if (isset($permission_allowed[$permission_level])) {

            if (in_array(self::getActiveController(), $permission_allowed[$permission_level])) {

                $res = false;

            } else if (in_array(self::getActiveAction(), $permission_allowed[$permission_level])) {

                $res = false;

            }

        }

        return $res;

    }

    /**
     * ページへのアクセス権があるか？
     */
    public static function isPagePermission($permission_level) {

        $res = false;

        switch ($permission_level) {
            case 'ADMIN':
            case 'LEADER':
                $res = true;
                break;
            case 'USER':
            case 'GUEST':
            case 'BAD_USER':
            case 'OTHER':
            default:
                break;
        }

        return $res;

    }

}