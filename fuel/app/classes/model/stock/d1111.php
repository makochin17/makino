<?php
namespace Model\Stock;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;

class D1111 extends \Model {

    public static $db           = 'MAKINO';

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
    
    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 得意先No
            'client_code'       => array('name' => '得意先No', 'max_lengths' => '5'),
            // 保管場所
            'storage_location'  => array('name' => '保管場所', 'max_lengths' => '15'),
            // 数量
            'total_volume'      => array('name' => '数量', 'max_lengths' => '10'),
            // 商品名
            'product_name'      => array('name' => '商品名', 'max_lengths' => '30'),
            // メーカー
            'maker_name'        => array('name' => 'メーカー', 'max_lengths' => '15'),
            // 品番
            'part_number'       => array('name' => '品番', 'max_lengths' => '15'),
            // 型番
            'model_number'      => array('name' => '型番', 'max_lengths' => '15'),
            // 備考
            'remarks'           => array('name' => '備考', 'max_lengths' => '15'),
        );
    }

    /**
     * 指定項目NULLチェック
     */
    public static function chkStockDataNull($data) {

        if (empty($data['client_code']) && empty($data['storage_location']) && empty($data['product_name']) && empty($data['maker_name']) && empty(floatval($data['total_volume'])) && empty($data['part_number']) && empty($data['model_number']) && empty($data['remarks'])) {
            return false;
        }
        return true;
    }

    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // ヘッダーデータ
    public static function getHeaders($type = 'csv') {

        $res = array();
        switch ($type) {
            case 'dispatch':
            case 'csv':
            default:
                $res = array(
                    'division_code'         => '課コード',
                    'client_code'           => '得意先コード',
                    'storage_location'      => '保管場所',
                    'product_name'          => '商品名',
                    'maker_name'            => 'メーカー名',
                    'total_volume'          => '数量',
                    'unit_code'             => '単位コード',
                    'part_number'           => '品番',
                    'model_number'          => '型番',
                    'remarks'               => '備考',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'stock') {

        $res = array();
        switch ($type) {
            case 'stock':
            default:
                $tmp = array(
                    'processing_division'   => '1',
                    'stock_number'          => '',
                    'division_code'         => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    'stock_number'          => '',
                    'client_code'           => '',
                    'client_name'           => '',
                    'storage_location'      => '',
                    'product_name'          => '',
                    'maker_name'            => '',
                    'total_volume'          => '',
                    'unit_code'             => '',
                    'part_number'           => '',
                    'model_number'          => '',
                    'remarks'               => '',
                );
                // 在庫データ
                for ($i=0;$i < 5;$i++) {
                    $list[] = $sub_tmp;
                }
                $tmp['list']        = $list;
                $res                = $tmp;
                break;
        }

        return $res;
    }

    public static function setForms($type = 'stock', $conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms($type);
        }

        foreach ($conditions as $key => $cols) {
            if ($key == 'list') {
                foreach ($cols as $listcnt => $data) {
                    foreach ($data as $listkey => $listval) {
                        if (isset($input_data[$key][$listcnt][$listkey])) {
                            $conditions[$key][$listcnt][$listkey] = $input_data[$key][$listcnt][$listkey];
                        }
                    }
                }
            } elseif ($key == 'division_code') {
                if (isset($input_data[$key])) {
                    $conditions[$key]   = $input_data[$key];
                } else {
                    $userinfo           = AuthConfig::getAuthConfig('all');
                    $conditions[$key]   = $userinfo['division_code'];
                }
            } else {
                $conditions[$key] = $input_data[$key];
            }
        }

        return $conditions;
    }

    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録(在庫データ)
        $insert_id = self::addStock($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_DE0015')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0015');
        }

        return null;
    }

    /**
     * 在庫データ登録
     */
    public static function addStock($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'division_code'         => $data['division_code'],
            'client_code'           => $data['client_code'],
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'unit_code'             => $data['unit_code'],
            'total_volume'          => str_replace(',', '', $data['total_volume']),
            'start_volume'          => str_replace(',', '', $data['total_volume']),
            'storage_location'      => (!empty($data['storage_location'])) ? $data['storage_location']:null,
            'part_number'           => (!empty($data['part_number'])) ? $data['part_number']:null,
            'model_number'          => (!empty($data['model_number'])) ? $data['model_number']:null,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_stock')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード更新
        $result = self::updStock($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0016')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0016');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0028', \Config::get('m_DI0028'), '在庫更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 在庫データ更新
     */
    public static function updStock($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'division_code'         => $data['division_code'],
            'client_code'           => $data['client_code'],
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'storage_location'      => (!empty($data['storage_location'])) ? $data['storage_location']:null,
            'part_number'           => (!empty($data['part_number'])) ? $data['part_number']:null,
            'model_number'          => (!empty($data['model_number'])) ? $data['model_number']:null,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
        );

        // テーブル
        $stmt = \DB::update('t_stock')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('stock_number', '=', $data['stock_number']);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    //=========================================================================//
    //==============================   対象削除   ==============================//
    //=========================================================================//
    public static function delete_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // 在庫レコード削除
        $result = self::delStock($conditions['stock_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0017')."[stock_number:".$conditions['stock_number']."]");
            return \Config::get('m_DE0017');
        }
        
        // 入出庫レコード削除
        $result = self::delStockChange($conditions['stock_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0020')."[stock_number:".$conditions['stock_number']."]");
            return \Config::get('m_DE0020');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0029', \Config::get('m_DI0029'), '在庫削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 在庫データ削除
     */
    public static function delStock($stock_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($stock_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_stock')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('stock_number', '=', $stock_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }
    /**
     * 入出庫データ削除
     */
    public static function delStockChange($stock_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($stock_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_stock_change')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('stock_number', '=', $stock_number);
        // 削除フラグ
        $stmt->where('delete_flag', '=', 0);
        // 更新実行
        $result = $stmt->execute($db);
        if($result >= 0) {
            return true;
        }
        return false;
    }
    /**
     * レコード取得
     */
    public static function getStock($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('t.stock_number', 'stock_number'),
                array('t.division_code', 'division_code'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.product_name', 'product_name'),
                array('t.maker_name', 'maker_name'),
                array('t.unit_code', 'unit_code'),
                array('t.total_volume', 'total_volume'),
                array('t.storage_location', 'storage_location'),
                array('t.part_number', 'part_number'),
                array('t.model_number', 'model_number'),
                array('t.remarks', 'remarks'),
                );

        // テーブル
        $stmt->from(array('t_stock', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');

        // 在庫番号
        $stmt->where('t.stock_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
    }
}