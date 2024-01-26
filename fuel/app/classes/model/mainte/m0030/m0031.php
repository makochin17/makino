<?php
namespace Model\Mainte\M0030;
use \Model\Mainte\M0030\M0030;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;
use \Log;
use \Config;

class M0031 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * 保管場所列マスタレコード検索
     */
    public static function getSearch($is_count, $type, $conditions, $offset, $limit, $db) {

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(msc.id) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('msc.id', 'storage_column_id'),
                    array('msc.name', 'storage_column_name'),
                    array('msc.del_flg', 'del_flg'),
                    );
        }

        // テーブル
        $stmt->from(array('m_storage_column', 'msc'))
        ;

        switch ($type) {
            case 'all':
                break;
            default:
                // コード
                if (trim($conditions['storage_column_id']) != '') {
                    $stmt->where('msc.id', '=', $conditions['storage_column_id']);
                }
                // 名称
                if (trim($conditions['storage_column_name']) != '') {
                    $stmt->where('msc.name', 'LIKE', \DB::expr("'%".$conditions['storage_column_name']."%'"));
                }
                break;
        }
        // 適用開始日
        $stmt->where('msc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('msc.end_date', '>', date("Y-m-d"));

        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('msc.id', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

    /**
     * 保管場所列削除
     */
    public static function delete_record($storage_column_id, $db) {

        //保管場所列マスタ情報取得
        if ($result = self::getStorageColumn($storage_column_id, $db)){
            //保管場所列マスタ無効
            $result = self::delStorageColumn($storage_column_id, 'YES', $db);
        } else {
            //保管場所列マスタ有効
            $result = self::delStorageColumn($storage_column_id, 'NO', $db);
        }

        if (!$result) {
            Log::error(str_replace('XXXXX','保管場所列',Config::get('m_ME0008'))."[".$storage_column_id."]");
            return str_replace('XXXXX','保管場所列',Config::get('m_ME0008'));
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '保管場所列マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 保管場所列マスタレコード取得
     */
    public static function getStorageColumn($code, $db) {
        // 項目
        $stmt = \DB::select();

        // テーブル
        $stmt->from(array('m_storage_column', 'm'));
        // 保管場所列コード
        $stmt->where('m.id', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        // フラグ
        $stmt->where('m.del_flg', '=', 'NO');

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 保管場所列マスタ削除
     */
    public static function delStorageColumn($code, $del_flg, $db) {

        // テーブル
        $stmt = \DB::update('m_storage_column');

        // 項目セット
        $set = array(
            'del_flg' => $del_flg
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        // 庸車先コード
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
     * 保管場所列マスタ登録
     */
    public static function create_record($conditions, $db) {

        //保管場所列マスタ存在チェック
        if ($result = self::getStorageColumnByName($conditions['storage_column_name'], $db)) {
            return Config::get('m_MW0004');
        }

        //保管場所列マスタ登録
        $result = self::addStorageColumn($conditions, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','保管場所列',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
            return str_replace('XXXXX','保管場所列',Config::get('m_ME0006'));
        }

        return null;
    }

    /**
     * 保管場所列マスタ登録
     */
    public static function addStorageColumn($items, $db) {

        // 項目セット
        $set = array(
            'name'          => $items['storage_column_name'],
            'start_date'    => Date::forge()->format('mysql_date'),
            'end_date'      => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));

        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'name = VALUES(name),'
                . 'start_date = VALUES(start_date),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';

        // 登録実行
        $stmt = \DB::insert('m_storage_column')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
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

    //保管場所列マスタ取得(by名称)
    public static function getStorageColumnByName($name, $db) {
        //項目
        return \DB::select()
        // テーブル
        ->from(array('m_storage_column', 'm'))
        // 得意先コード
        ->where('m.name', '=', $name)
        // 検索実行
        ->execute($db)->current();

    }
}