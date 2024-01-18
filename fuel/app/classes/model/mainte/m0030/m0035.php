<?php
namespace Model\Mainte\M0030;
use \Model\Mainte\M0030\M0034;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;
use \Log;
use \Config;

class M0035 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 保管場所リレーションレコード取得
     */
    public static function getStorageLocation($code, $db) {

        // 項目
        $stmt = \DB::select(
                array('rsl.id', 'storage_location_id'),
                array(\DB::expr("CONCAT(msw.name, '-', msc.name, '-', msd.name, '-', msh.name)"), 'storage_location_name'),
                array('rsl.storage_warehouse_id', 'storage_warehouse_id'),
                array('msw.name', 'storage_warehouse_name'),
                array('rsl.storage_column_id', 'storage_column_id'),
                array('msc.name', 'storage_column_name'),
                array('rsl.storage_depth_id', 'storage_depth_id'),
                array('msd.name', 'storage_depth_name'),
                array('rsl.storage_height_id', 'storage_height_id'),
                array('msh.name', 'storage_height_name'),
                array('rsl.start_date', 'start_date'),
                array('rsl.del_flg', 'del_flg'),
                );

        $stmt->from(array('rel_storage_location', 'rsl'))
            ->join(array('m_storage_warehouse', 'msw'), 'left outer')
                ->on('msw.id', '=', 'rsl.storage_warehouse_id')
                ->on('msw.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msw.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_column', 'msc'), 'left outer')
                ->on('msc.id', '=', 'rsl.storage_column_id')
                ->on('msc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_depth', 'msd'), 'left outer')
                ->on('msd.id', '=', 'rsl.storage_depth_id')
                ->on('msd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msd.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_storage_height', 'msh'), 'left outer')
                ->on('msh.id', '=', 'rsl.storage_height_id')
                ->on('msh.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('msh.end_date', '>', '\''.date("Y-m-d").'\'');
        // コード
        $stmt->where('rsl.id', '=', $code);
        // 適用開始日
        $stmt->where('rsl.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('rsl.end_date', '>', date("Y-m-d"));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 保管場所削除
     */
    public static function delete_record($storage_location_id, $db) {

        //保管場所マスタ情報取得
        $result = self::getStorageLocation($storage_location_id, $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $storage_location_data = $result[0];

        //保管場所マスタ削除
        $result = self::delStorageLocation($storage_location_id, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','保管場所',Config::get('m_ME0008'))."[".$storage_location_id."]");
            return str_replace('XXXXX','保管場所',Config::get('m_ME0008'));
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '保管場所リレーション', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 保管場所更新
     */
    public static function update_record($conditions, $db) {

        //保管場所マスタ情報取得
        $result = self::getStorageLocation($conditions['storage_location_id'], $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $data = $result[0];

        ////////////////////////////////////////////
        //保管場所マスタ更新
        // レコードの重複チェック(各種コードで重複チェック)
        if ($result = M0030::getStorageLocationBySubCode($conditions['storage_warehouse_id'], $conditions['storage_column_id'], $conditions['storage_depth_id'], $conditions['storage_height_id'], $db)) {
            return Config::get('m_MW0004');
        }

        // 取得レコードの「適用開始日」がシステム日付より過去日か
        if (strtotime($data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {

           $data = array(
                'storage_warehouse_id'  => $conditions['storage_warehouse_id'],
                'storage_column_id'     => $conditions['storage_column_id'],
                'storage_depth_id'      => $conditions['storage_depth_id'],
                'storage_height_id'     => $conditions['storage_height_id'],
                );

            //保管場所マスタ登録
            $result = M0034::addStorageLocation($data, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','保管場所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','保管場所',Config::get('m_ME0006'));
            }
        } else {
            //　レコード更新
            $result = self::updStorageLocation($conditions, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','保管場所',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','保管場所',Config::get('m_ME0007'));
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '保管場所マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 保管場所マスタ更新
     */
    public static function updStorageLocation($items, $db) {

        // テーブル
        $stmt = \DB::update('rel_storage_location');

        // 項目セット
        $set = array(
            'storage_warehouse_id'  => $items['storage_warehouse_id'],
            'storage_column_id'     => $items['storage_column_id'],
            'storage_depth_id'		=> $items['storage_depth_id'],
            'storage_height_id'		=> $items['storage_height_id'],
            'start_date'            => Date::forge()->format('mysql_date'),
            'end_date'              => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 保管場所ID
        $stmt->where('id', '=', $items['storage_location_id']);
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
     * 保管場所マスタ削除
     */
    public static function delStorageLocation($code, $db) {

        // テーブル
        $stmt = \DB::update('rel_storage_location');

        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 保管場所コード
        $stmt->where('id', '=', $code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        // 削除フラグ
        $stmt->where('del_flg', '=', 'NO');
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