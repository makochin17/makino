<?php
namespace Model\System;
use \Model\Common\SystemConfig;
use \Date;

class M0070 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 通知データレコード取得
     */
    public static function getNotice($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.notice_number', 'notice_number'),
                array('t.division_code', 'division'),
                array('t.position_code', 'position'),
                array('t.notice_date', 'notice_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.notice_title),"'.$encrypt_key.'")'), 'notice_title'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.notice_message),"'.$encrypt_key.'")'), 'notice_message'),
                array('t.notice_start', 'notice_start'),
                array('t.notice_end', 'notice_end'),
                );

        // テーブル
        $stmt->from(array('t_notice', 't'));
        
        // 商品コード
        $stmt->where('t.notice_number', '=', $code);
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 通知データ登録
     */
    public static function addNotice($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 項目セット
        $set = array(
            'division_code' => (!empty($items['division'])) ? $items['division'] : null,
            'position_code' => (!empty($items['position'])) ? $items['position'] : null,
            'notice_date' => $items['notice_date'],
            'notice_title' => \DB::expr('HEX(AES_ENCRYPT("'.$items['notice_title'].'","'.$encrypt_key.'"))'),
            'notice_message' => \DB::expr('HEX(AES_ENCRYPT("'.$items['notice_message'].'","'.$encrypt_key.'"))'),
            'notice_start' => $items['notice_start'],
            'notice_end' => $items['notice_end'],
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('t_notice')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 通知データ更新
     */
    public static function updNotice($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // テーブル
        $stmt = \DB::update('t_notice');
        
        // 項目セット
        $set = array(
            'division_code' => (!empty($items['division'])) ? $items['division'] : null,
            'position_code' => (!empty($items['position'])) ? $items['position'] : null,
            'notice_date' => $items['notice_date'],
            'notice_title' => \DB::expr('HEX(AES_ENCRYPT("'.$items['notice_title'].'","'.$encrypt_key.'"))'),
            'notice_message' => \DB::expr('HEX(AES_ENCRYPT("'.$items['notice_message'].'","'.$encrypt_key.'"))'),
            'notice_start' => $items['notice_start'],
            'notice_end' => $items['notice_end'],
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 商品コード
        $stmt->where('notice_number', '=', $items['notice_number']);
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 通知データ削除（物理削除）
     */
    public static function delNotice($code, $db) {

        // テーブル
        $stmt = \DB::delete('t_notice');
        
        // 商品コード
        $stmt->where('notice_number', '=', $code);
        
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