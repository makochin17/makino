<?php
namespace Model\Location;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030;

class L0010 extends \Model {

    public static $db               = 'MAKINO';

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // データ加工
    public static function setList($type, $item) {

        $res = array();
        if (empty($item)) {
            return false;
        }

        switch ($type) {
            case 'unit':
                foreach ($item as $key => $val) {
                    $res[$val['unit_id']] = $val['unit_name'];
                }
                break;
            case 'location':
                foreach ($item as $key => $val) {
                    $res[$val['storage_location_id']] = $val['storage_location_name'];
                }
                break;
            default:
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = null) {

        $res = array();
        switch ($type) {
            case 'location':
            default:
                $res = array(
                    'location_id'                       => '',
                    'location_name'                     => '',
                    'storage_warehouse_id'              => '',
                    'storage_warehouse_name'            => '',
                    'storage_column_id'                 => '',
                    'storage_column_name'               => '',
                    'storage_depth_id'                  => '',
                    'storage_depth_name'                => '',
                    'storage_height_id'                 => '',
                    'storage_height_name'               => '',
                    'barcode_flg'                       => '',
                    'total_cnt'                         => '',
                    'storage_total_cnt'                 => '',
                    'warehouse_cnt'                     => '',
                    'storage_warehouse_cnt'             => '',
                    'column_cnt'                        => '',
                    'storage_column_cnt'                => '',
                    'depth_cnt'                         => '',
                    'storage_depth_cnt'                 => '',
                    'height_cnt'                        => '',
                    'storage_height_cnt'                => '',
                );
                break;
        }

        return $res;
    }

    public static function setForms($type = 'location', $conditions, $input_data) {

        if (empty($conditions)) {
            $conditions = self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if (isset($input_data[$key])) {
                $conditions[$key] = $input_data[$key];
            }
        }

        return $conditions;
    }

    //=========================================================================//
    //===============================   検索処理  ==============================//
    //=========================================================================//
    /**
     * レコード全件数取得
     */
    public static function getTotalCnt($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $stmt = \DB::select(\DB::expr('COUNT(r.id) AS count'));
        // テーブル
        $stmt->from(array('rel_storage_location', 'r'));
        // 条件
        $stmt->where('r.del_flg', '=', 'NO');

        $res = $stmt->execute($db)->as_array();
        return $res[0]['count'];
    }

    /**
     * 倉庫別保管情報取得
     */
    public static function getLocationWarehouse($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array(\DB::expr("COUNT(r.storage_warehouse_id)"), 'warehouse_cnt'),
                array('r.storage_warehouse_id', 'storage_warehouse_id'),
                array('w.name', 'storage_warehouse_name')
                );
        // テーブル
        $stmt->from(array('rel_storage_location', 'r'))
        ->join(array('m_storage_warehouse', 'w'), 'INNER')
            ->on('w.id', '=', 'r.storage_warehouse_id')
            ->on('w.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('r.del_flg', '=', 'NO');
        // 結果
        $res = $stmt->group_by('r.storage_warehouse_id')
            ->order_by('r.storage_warehouse_id', 'ASC')
            ->execute($db)
            ->as_array();

        return $res;
    }

    /**
     * 倉庫・列別保管情報取得
     */
    public static function getLocationColumn($warehouse_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array(\DB::expr("COUNT(r.storage_column_id)"), 'column_cnt'),
                array('r.storage_column_id', 'storage_column_id'),
                array('c.name', 'storage_column_name')
                );
        // テーブル
        $stmt->from(array('rel_storage_location', 'r'))
        ->join(array('m_storage_warehouse', 'w'), 'INNER')
            ->on('w.id', '=', 'r.storage_warehouse_id')
            ->on('w.del_flg', '=', \DB::expr("'NO'"))
            ->on('w.id', '=', \DB::expr("".$warehouse_id.""))
        ->join(array('m_storage_column', 'c'), 'INNER')
            ->on('c.id', '=', 'r.storage_column_id')
            ->on('c.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('r.del_flg', '=', 'NO');
        // 結果
        $res = $stmt->group_by('r.storage_column_id')
            ->order_by('r.storage_column_id', 'ASC')
            ->execute($db)
            ->as_array();

        return $res;
    }

    /**
     * 倉庫・列・奥行別保管情報取得
     */
    public static function getLocationDepth($warehouse_id, $column_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array(\DB::expr("COUNT(r.storage_depth_id)"), 'depth_cnt'),
                array('r.storage_depth_id', 'storage_depth_id'),
                array('d.name', 'storage_depth_name')
                );
        // テーブル
        $stmt->from(array('rel_storage_location', 'r'))
        ->join(array('m_storage_warehouse', 'w'), 'INNER')
            ->on('w.id', '=', 'r.storage_warehouse_id')
            ->on('w.del_flg', '=', \DB::expr("'NO'"))
            ->on('w.id', '=', \DB::expr("".$warehouse_id.""))
        ->join(array('m_storage_column', 'c'), 'INNER')
            ->on('c.id', '=', 'r.storage_column_id')
            ->on('c.del_flg', '=', \DB::expr("'NO'"))
            ->on('c.id', '=', \DB::expr("".$column_id.""))
        ->join(array('m_storage_depth', 'd'), 'INNER')
            ->on('d.id', '=', 'r.storage_depth_id')
            ->on('d.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('r.del_flg', '=', 'NO');
        // 結果
        $res = $stmt->group_by('r.storage_depth_id')
            ->order_by('r.storage_depth_id', 'ASC')
            ->execute($db)
            ->as_array();

        return $res;
    }

    /**
     * 倉庫・列・奥行・高さ別保管情報取得
     */
    public static function getLocationHeight($warehouse_id, $column_id, $depth_id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array(\DB::expr("COUNT(r.storage_height_id)"), 'height_cnt'),
                array('r.storage_height_id', 'storage_height_id'),
                array('h.name', 'storage_height_name')
                );
        // テーブル
        $stmt->from(array('rel_storage_location', 'r'))
        ->join(array('m_storage_warehouse', 'w'), 'INNER')
            ->on('w.id', '=', 'r.storage_warehouse_id')
            ->on('w.del_flg', '=', \DB::expr("'NO'"))
            ->on('w.id', '=', \DB::expr("".$warehouse_id.""))
        ->join(array('m_storage_column', 'c'), 'INNER')
            ->on('c.id', '=', 'r.storage_column_id')
            ->on('c.del_flg', '=', \DB::expr("'NO'"))
            ->on('c.id', '=', \DB::expr("".$column_id.""))
        ->join(array('m_storage_depth', 'd'), 'INNER')
            ->on('d.id', '=', 'r.storage_depth_id')
            ->on('d.del_flg', '=', \DB::expr("'NO'"))
            ->on('d.id', '=', \DB::expr("".$depth_id.""))
        ->join(array('m_storage_height', 'h'), 'INNER')
            ->on('h.id', '=', 'r.storage_height_id')
            ->on('h.del_flg', '=', \DB::expr("'NO'"))
        ;
        // 条件
        $stmt->where('r.del_flg', '=', 'NO');
        // 結果
        $res = $stmt->group_by('r.storage_height_id')
            ->order_by('r.storage_height_id', 'ASC')
            ->execute($db)
            ->as_array();

        return $res;
    }

    //=========================================================================//
    //==============================   対象取得   ==============================//
    //=========================================================================//
    /**
     * 詳細保管情報取得
     */
    public static function getLocationDetail($type, $warehouse_id, $column_id = null, $depth_id = null, $height_id = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $sql = \DB::select('id')->from('rel_storage_location')
            ->where('del_flg', 'NO')
            ->where('storage_warehouse_id', $warehouse_id);
        if (!empty($column_id)) {
            $sql->where('storage_column_id', $column_id);
        }
        if (!empty($depth_id)) {
            $sql->where('storage_depth_id', $depth_id);
        }
        if (!empty($height_id)) {
            $sql->where('storage_height_id', $height_id);
        }
        $relation_id = $sql->compile($db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(
                        array(\DB::expr("COUNT(t.id)"), 'stock_cnt')
                        );
                break;
            default:
                $stmt = \DB::select(
                        array(\DB::expr("AES_DECRYPT(UNHEX(t.car_name),'".$encrypt_key."')"), 'car_name'),
                        array('t.car_code', 'car_code'),
                        array('t.customer_code', 'customer_code'),
                        array(\DB::expr("AES_DECRYPT(UNHEX(t.customer_name),'".$encrypt_key."')"), 'customer_name'),
                        array('t.location_id', 'location_id')
                        );
                break;
        }
        // テーブル
        $stmt->from(array('t_logistics', 't'));
        // 条件
        $stmt->where('t.del_flg', '=', 'NO');
        // 条件
        $stmt->where('t.location_id', 'IN', \DB::expr("(".$relation_id.")"));
        // 条件
        $stmt->where('t.receipt_flg', '=', 'YES');
        // 条件
        $stmt->where('t.delivery_flg', '=', 'NO');
        // 結果
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->current();
                return $res['stock_cnt'];
                break;
            default:
                $res = $stmt->execute($db)->current();
                break;
        }

        return $res;
    }

}