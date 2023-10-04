<?php
namespace Model\System;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Date;
use \Log;
use \Config;

class C0080 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 会社情報取得
     */
    public static function getCompany($id, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'id'),
                array('m.company_name', 'company_name'),
                array('m.system_name', 'system_name'),
                array('m.start_time', 'start_time'),
                array('m.end_time', 'end_time'),
                array('m.summer_tire_warning', 'summer_tire_warning'),
                array('m.summer_tire_caution', 'summer_tire_caution'),
                array('m.winter_tire_warning', 'winter_tire_warning'),
                array('m.winter_tire_caution', 'winter_tire_caution'),
                );

        // テーブル
        $stmt->from(array('m_company', 'm'));
        // 会社情報ID
        if (!empty($id)) {
            $stmt->where('m.id', '=', $id);
        }
        // 検索実行
        return $stmt->execute($db)->current();

    }

    /**
     * 登録
     */
    public static function insert_record($conditions, $db) {

        $data = array(
            'company_name'          => $conditions['company_name'],
            'system_name'           => $conditions['system_name'],
            'start_time'            => $conditions['start_time'],
            'end_time'              => $conditions['end_time'],
            'summer_tire_warning'   => $conditions['summer_tire_warning'],
            'summer_tire_caution'   => $conditions['summer_tire_caution'],
            'winter_tire_warning'   => $conditions['winter_tire_warning'],
            'winter_tire_caution'   => $conditions['winter_tire_caution'],
            );

        //得意先マスタ登録
        $result = self::addCompany($data, $db);
        if (!$result) {
            Log::error(Config::get('m_CO0007')."[".print_r($conditions,true)."]");
            return Config::get('m_CO0007');
        }

        return null;
    }

    /**
     * 登録
     */
    public static function addCompany($items, $db) {

        // 項目セット
        $set = array(
            'company_name'          => $items['company_name'],
            'system_name'           => $items['system_name'],
            'start_time'            => (!empty($items['start_time'])) ? $items['start_time']:'00:00',
            'end_time'              => (!empty($items['end_time'])) ? $items['end_time']:'00:00',
            'summer_tire_warning'   => (!empty($items['summer_tire_warning'])) ? $items['summer_tire_warning']:'0.00',
            'summer_tire_caution'   => (!empty($items['summer_tire_caution'])) ? $items['summer_tire_caution']:'0.00',
            'winter_tire_warning'   => (!empty($items['winter_tire_warning'])) ? $items['winter_tire_warning']:'0.00',
            'winter_tire_caution'   => (!empty($items['winter_tire_caution'])) ? $items['winter_tire_caution']:'0.00',
            );

        // 登録実行
        $stmt = \DB::insert('m_company')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 更新
     */
    public static function update_record($id, $conditions, $db) {

        $data = array(
            'company_name'          => $conditions['company_name'],
            'system_name'           => $conditions['system_name'],
            'start_time'            => $conditions['start_time'],
            'end_time'              => $conditions['end_time'],
            'summer_tire_warning'   => $conditions['summer_tire_warning'],
            'summer_tire_caution'   => $conditions['summer_tire_caution'],
            'winter_tire_warning'   => $conditions['winter_tire_warning'],
            'winter_tire_caution'   => $conditions['winter_tire_caution'],
            );

        //得意先マスタ登録
        $result = self::updCompany($id, $data, $db);
        if (!$result) {
            Log::error(Config::get('m_CO0008')."[".print_r($conditions,true)."]");
            return Config::get('m_CO0008');
        }

        return null;
    }

    /**
     * 更新
     */
    public static function updCompany($id, $items, $db) {

        // 項目セット
        $set = array(
            'company_name'          => $items['company_name'],
            'system_name'           => $items['system_name'],
            'start_time'            => (!empty($items['start_time'])) ? $items['start_time']:'00:00',
            'end_time'              => (!empty($items['end_time'])) ? $items['end_time']:'00:00',
            'summer_tire_warning'   => (!empty($items['summer_tire_warning'])) ? $items['summer_tire_warning']:'0.00',
            'summer_tire_caution'   => (!empty($items['summer_tire_caution'])) ? $items['summer_tire_caution']:'0.00',
            'winter_tire_warning'   => (!empty($items['winter_tire_warning'])) ? $items['winter_tire_warning']:'0.00',
            'winter_tire_caution'   => (!empty($items['winter_tire_caution'])) ? $items['winter_tire_caution']:'0.00',
            );

        // 登録実行
        $stmt = \DB::update('m_company')->set($set)->where('id', '=', $id);
        // 更新実行
        $result = $stmt->execute($db);
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {

        $user_master_id   = AuthConfig::getAuthConfig('user_id');
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