<?php
namespace Model\Mainte\M0020;
use \Model\Mainte\M0020\M0024;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;
use \Log;
use \Config;

class M0025 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * ユニットマスタレコード取得
     */
    public static function getUnit($code, $db) {

        // 項目
        $stmt = \DB::select(
                array('m.id', 'unit_code'),
                array('m.schedule_type', 'schedule_type'),
                array('m.name', 'unit_name'),
                array('m.start_date', 'start_date'),
                array('m.end_date', 'end_date'),
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));
        // ユニットコード
        $stmt->where('m.id', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * ユニット削除
     */
    public static function delete_record($unit_code, $db) {

        //得意先マスタ情報取得
        $result = self::getUnit($unit_code, $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $unit_data = $result[0];

        //ユニットマスタ削除
        $result = self::delUnit($unit_code, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','ユニット',Config::get('m_ME0008'))."[".$unit_code."]");
            return str_replace('XXXXX','ユニット',Config::get('m_ME0008'));
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), 'ユニットマスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * ユニット更新
     */
    public static function update_record($conditions, $db) {

        //ユニットマスタ情報取得
        $result = self::getUnit($conditions['unit_code'], $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $unit_data = $result[0];

        ////////////////////////////////////////////
        //ユニットマスタ更新

        // 取得レコードの「適用開始日」がシステム日付より過去日か
        if (strtotime($unit_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
            // レコード削除（論理）
            $result = self::delUnit($unit_data['unit_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','ユニット',Config::get('m_ME0008'))."[".$unit_data['unit_code']."]");
                return str_replace('XXXXX','ユニット',Config::get('m_ME0008'));
            }

            $data = array(
                'schedule_type' => $conditions['schedule_type'],
                'unit_name'     => $conditions['unit_name'],
                );

            //ユニットマスタ登録
            $result = M0024::addUnit($data, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','ユニット',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','ユニット',Config::get('m_ME0006'));
            }
        } else {
            //　レコード更新
            $result = self::updUnit($conditions, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','ユニット',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','ユニット',Config::get('m_ME0007'));
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), 'ユニットマスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * ユニットマスタ更新
     */
    public static function updUnit($items, $db) {

        // テーブル
        $stmt = \DB::update('m_unit');

        // 項目セット
        $set = array(
            'schedule_type' => $items['schedule_type'],
            'name'          => $items['unit_name'],
            'start_date'    => Date::forge()->format('mysql_date'),
            'end_date'      => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));

        // コード
        $stmt->where('id', '=', $items['unit_code']);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        // 更新実行
        $result = $stmt->execute($db);

        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * ユニットマスタ削除
     */
    public static function delUnit($code, $db) {

        // テーブル
        $stmt = \DB::update('m_unit');

        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));

        // コード
        $stmt->where('id', '=', $code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
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