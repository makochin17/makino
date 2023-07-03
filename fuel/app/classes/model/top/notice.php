<?php
namespace Model\Top;

use \Model\Init;
use \Model\AccessControl;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;

class Notice extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 通知データ取得
     */
    public static function getNoticeData($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key    = SystemConfig::getSystemConfig('encrypt_key',$db);
        $list           = AuthConfig::getAuthConfig();

        return \DB::select(
            'notice_date',
            array(\DB::expr('AES_DECRYPT(UNHEX(notice_title), "'.$encrypt_key.'")'), 'notice_title'),
            array(\DB::expr('AES_DECRYPT(UNHEX(notice_message), "'.$encrypt_key.'")'), 'notice_message')
        )
        ->from('t_notice')
        ->where_open()
        ->where('division_code', 'IS', null)
        ->or_where('division_code', '=', $list['division_code'])
        ->where_close()
        ->where_open()
        ->where('position_code', 'IS', null)
        ->or_where('position_code', '=', $list['position_code'])
        ->where_close()
        ->where('notice_start', '<=', date('Y-m-d'))
        ->where('notice_end', '>', date('Y-m-d'))
        ->order_by('notice_date')
        ->order_by('notice_message')
        ->execute($db)->as_array()
        ;

    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {

        //$auth_data        = Init::get('auth_data');
        //$user_master_id   = $auth_data['id'];
        $user_master_id   = '00000';
        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => Date::forge()->format('mysql'),
                'create_user'       => $user_master_id,
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        }
        return $data;
    }

}