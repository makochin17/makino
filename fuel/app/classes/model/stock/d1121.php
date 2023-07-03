<?php
namespace Model\Stock;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;
use \Model\Stock\D1120;

class D1121 extends \Model {

    public static $db           = 'ONISHI';

    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 日付
            'destination_date'  => array('name' => '日付', 'max_lengths' => ''),
            // 運行先
            'destination'       => array('name' => '運行先', 'max_lengths' => '30'),
            // 数量
            'volume'            => array('name' => '数量', 'max_lengths' => '10'),
            // 料金
            'fee'               => array('name' => '料金', 'max_lengths' => '10'),
            // 備考
            'remarks'           => array('name' => '備考', 'max_lengths' => '15'),
        );
    }
    
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

    /**
     * 指定項目NULLチェック
     */
    public static function chkStockChangeDataNull($data) {

        if (empty($data['destination_date']) && empty($data['destination']) && empty(floatval(str_replace(',', '', $data['volume']))) && empty($data['fee']) && empty($data['remarks'])) {
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
    
    /**
     * 在庫データの検索
     */
    public static function getSearchStock($code, $db) {
        return D1120::getSearchStock($code, $db);
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
                    'sales_status'      => '売上確定',
                    'destination_date'  => '日付',
                    'stock_change_code' => '区分',
                    'destination'       => '運行先',
                    'volume'            => '数量',
                    'unit_code'         => '単位',
                    'fee'               => '料金',
                    'remarks'           => '備考',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'stock_change') {

        $res = array();
        switch ($type) {
            case 'stock_change':
            default:
                $tmp = array(
                    'processing_division'   => '1',
                    'stock_number'          => '',
                    'stock_change_number'   => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    'stock_change_number'   => '',
                    'sales_status'          => '1',
                    'destination_date'      => '',
                    'stock_change_code'     => '',
                    'destination'           => '',
                    'volume'                => '',
                    'unit_code'             => '',
                    'fee'                   => '',
                    'remarks'               => '',
                );
                // 入出庫データ
                for ($i=0;$i < 5;$i++) {
                    $list[] = $sub_tmp;
                }
                $tmp['list']        = $list;
                $res                = $tmp;
                break;
        }

        return $res;
    }

    public static function setForms($type = 'stock_change', $conditions, $input_data) {

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
            } else {
                if (!empty($input_data[$key]))$conditions[$key] = $input_data[$key];
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

        //在庫データ存在チェック
        if (!$result = D1120::getSearchStock($conditions['stock_number'], $db)) {
            return \Config::get('m_DW0033');
        }
        
        // レコード登録(入出庫データ)
        $insert_id = self::addStockChange($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_DE0018')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0018');
        }
        
        //在庫数量更新
        $result = self::updStock('add', $conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0016')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0016');
        }

        return null;
    }

    /**
     * 入出庫データ登録
     */
    public static function addStockChange($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'stock_number'         => $data['stock_number'],
            'sales_status'         => (!empty($data['sales_status'])) ? $data['sales_status']:'1',
            'stock_change_code'    => $data['stock_change_code'],
            'destination_date'     => date('Y-m-d', strtotime($data['destination_date'])),
            'destination'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'),
            'volume'               => str_replace(',', '', $data['volume']),
            'fee'                  => $data['fee'],
            'remarks'              => (!empty($data['remarks'])) ? $data['remarks']:null,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_stock_change')->set($set)->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;
    }

    //=========================================================================//
    //==============================   対象更新   ==============================//
    //=========================================================================//
    public static function update_record($conditions, $old_data, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // レコード更新
        $result = self::updStockChange($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0019')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0019');
        }
        
        //在庫数量更新
        $conditions['old_stock_change_code'] = $old_data['stock_change_code'];
        $conditions['old_volume'] = $old_data['volume'];
        $result = self::updStock('upd', $conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0016')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0016');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0032', \Config::get('m_DI0032'), '入出庫更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 入出庫データ更新
     */
    public static function updStockChange($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array(
            'sales_status'         => (!empty($data['sales_status'])) ? $data['sales_status']:'1',
            'stock_change_code'    => $data['stock_change_code'],
            'destination_date'     => date('Y-m-d', strtotime($data['destination_date'])),
            'destination'          => \DB::expr('HEX(AES_ENCRYPT("'.$data['destination'].'","'.$encrypt_key.'"))'),
            'volume'               => str_replace(',', '', $data['volume']),
            'fee'                  => $data['fee'],
            'remarks'              => (!empty($data['remarks'])) ? $data['remarks']:null,
        );

        // テーブル
        $stmt = \DB::update('t_stock_change')->set(array_merge($set, self::getEtcData(false)));

        // 入出庫番号
        $stmt->where('stock_change_number', '=', $data['stock_change_number']);
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
    public static function delete_record($conditions, $old_data, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // レコード削除
        $result = self::delStockChange($conditions['stock_change_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0020')."[stock_change_number:".$conditions['stock_change_number']."]");
            return \Config::get('m_DE0020');
        }
        
        //在庫数量更新
        $conditions['old_stock_change_code'] = $old_data['stock_change_code'];
        $conditions['old_volume'] = $old_data['volume'];
        $result = self::updStock('del', $conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0016')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0016');
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0033', \Config::get('m_DI0033'), '入出庫削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 入出庫データ削除
     */
    public static function delStockChange($stock_change_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($stock_change_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_stock_change')->set(array_merge($set, self::getEtcData(false)));

        // 入出庫番号
        $stmt->where('stock_change_number', '=', $stock_change_number);
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
    public static function getStockChange($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目
        $stmt = \DB::select(
                array('t.stock_change_number', 'stock_change_number'),
                array('t.stock_number', 'stock_number'),
                array('t.sales_status', 'sales_status'),
                array('t.stock_change_code', 'stock_change_code'),
                array('t.destination_date', 'destination_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.destination),"'.$encrypt_key.'")'), 'destination'),
                array('t.volume', 'volume'),
                array('t.fee', 'fee'),
                array('t.remarks', 'remarks'),
                );

        // テーブル
        $stmt->from(array('t_stock_change', 't'));

        // 入出庫番号
        $stmt->where('t.stock_change_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
    }
    
    /**
     * 在庫データ更新（数量のみ）
     * $mode 'add'：登録時の処理 'upd'：更新時の処理 'del'：削除時の処理
     */
    public static function updStock($mode, $data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }
        
        $code = $data['stock_change_code'];
        $volume = str_replace(',', '', $data['volume']);
        $add_volume = 0.00;
        switch ($mode){
            case 'add':
                if ($code == '1' || $code == '3' || $code == '5') {
                    $add_volume += $volume;
                } elseif ($code == '2' || $code == '4' || $code == '6') {
                    $add_volume -= $volume;
                }
                break;
            case 'upd':
                //更新前の数量計算
                $old_code = $data['old_stock_change_code'];
                $old_volume = $data['old_volume'];
                if ($old_code == '1' || $old_code == '3' || $old_code == '5') {
                    $add_volume -= $old_volume;
                } elseif ($old_code == '2' || $old_code == '4' || $old_code == '6') {
                    $add_volume += $old_volume;
                }
                
                //更新後の数量計算
                if ($code == '1' || $code == '3' || $code == '5') {
                    $add_volume += $volume;
                } elseif ($code == '2' || $code == '4' || $code == '6') {
                    $add_volume -= $volume;
                }
                break;
            case 'del':
                $old_code = $data['old_stock_change_code'];
                $old_volume = $data['old_volume'];
                if ($old_code == '1' || $old_code == '3' || $old_code == '5') {
                    $add_volume -= $old_volume;
                } elseif ($old_code == '2' || $old_code == '4' || $old_code == '6') {
                    $add_volume += $old_volume;
                }
                break;
            default:
                return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        // 項目セット
        $set = array('total_volume' => \DB::expr('total_volume + '.$add_volume));

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
}