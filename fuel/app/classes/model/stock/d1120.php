<?php
namespace Model\Stock;
use \Model\Common\AuthConfig;
use \Model\Common\SystemConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Stock\D1121;

class D1120 extends \Model {

    public static $db       = 'MAKINO';
    
    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 入出庫番号
            'stock_change_number'       => array('name' => '入出庫番号', 'max_lengths' => '10'),
            // 運行日
            'from_destination_date'     => array('name' => '運行日From', 'max_lengths' => ''),
            'to_destination_date'       => array('name' => '運行日To', 'max_lengths' => ''),
            // 運行先
            'destination'               => array('name' => '運行先', 'max_lengths' => '30'),
        );
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 入出庫レコード検索 & 入出庫レコード検索件数取得
     * $mode　1:通常検索　2：本日分検索
     */
    public static function getSearch($type, $conditions, $offset, $limit, $db, $mode = 1) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(tsc.stock_change_number) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('tsc.stock_change_number', 'stock_change_number'),
                            array('tsc.stock_number', 'stock_number'),
                            array('tsc.sales_status', 'sales_status'),
                            array('tsc.stock_change_code', 'stock_change_code'),
                            array('tsc.destination_date', 'destination_date'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(tsc.destination),"'.$encrypt_key.'")'), 'destination'),
                            array('tsc.volume', 'volume'),
                            array('ts.unit_code', 'unit_code'),
                            array('tsc.fee', 'fee'),
                            array('tsc.remarks', 'remarks'),
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_stock_change', 'tsc'));
        // 在庫データ
        $stmt->join(array('t_stock', 'ts'), 'INNER')
            ->on('tsc.stock_number', '=', 'ts.stock_number');
        
        // 在庫番号
        $stmt->where(\DB::expr('CAST(tsc.stock_number AS SIGNED)'), '=', $conditions['stock_number']);
        // 入出庫番号
        if (!empty($conditions['stock_change_number'])) {
            $stmt->where(\DB::expr('CAST(tsc.stock_change_number AS SIGNED)'), '=', $conditions['stock_change_number']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('tsc.sales_status', '=', $conditions['sales_status']);
        }
        // 区分
        if (!empty($conditions['stock_change_code']) && trim($conditions['stock_change_code']) != '0') {
            $stmt->where('tsc.stock_change_code', '=', $conditions['stock_change_code']);
        }
        // 運行日
        if (!empty($conditions['from_destination_date']) && trim($conditions['to_destination_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_destination_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_destination_date'])))->format('mysql_date');
            $stmt->where('tsc.destination_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_destination_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_destination_date'])))->format('mysql_date');
                $stmt->where('tsc.destination_date', '>=', $date);
            }
            if (!empty($conditions['to_destination_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_destination_date'])))->format('mysql_date');
                $stmt->where('tsc.destination_date', '<=', $date);
            }
        }
        // 運行先
        if (!empty($conditions['destination']) && trim($conditions['destination']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(tsc.destination),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['destination']."%'"));
        }
        // 登録者
        if (!empty($conditions['create_user'])) {
            $stmt->where('tsc.create_user', '=', $conditions['create_user']);
        }
        // 作成日時
        if ($mode == 2) {
            $stmt->where('tsc.create_datetime', 'between', array(date("Y/m/d").' 00:00:00', date("Y/m/d").' 23:59:59'));
        }
        $stmt->where('tsc.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'search':
            default:
                return $stmt->order_by('tsc.destination_date', 'DESC')->order_by('tsc.stock_change_code', 'DESC')->order_by('tsc.stock_change_number', 'DESC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }
    
    /**
     * 在庫データの検索
     */
    public static function getSearchStock($code, $db) {
        
        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 項目
        $stmt = \DB::select(
                array('ts.stock_number', 'stock_number'),
                array('ts.division_code', 'division_code'),
                array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = ts.division_code)'), 'division_name'),
                array('ts.client_code', 'client_code'),
                array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = ts.client_code AND start_date <= ts.update_datetime AND end_date > ts.update_datetime)'), 'client_name'),
                array('ts.product_name', 'product_name'),
                array('ts.total_volume', 'total_volume'),
                array('ts.unit_code', 'unit_code'),
            );
        // テーブル
        $stmt->from(array('t_stock', 'ts'));
        // 条件
        $stmt->where(\DB::expr('CAST(ts.stock_number AS SIGNED)'), '=', $code);
        $stmt->where('ts.delete_flag', '=', '0');
        
        return $stmt->execute($db)->current();
    }
    
    /**
     * 入出庫データの取得（存在チェック用）
     */
    public static function getStockChange($stock_change_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('t.stock_change_number', 'stock_change_number'),
                array('t.stock_number', 'stock_number'),
                array('t.stock_change_code', 'stock_change_code'),
                array('t.volume', 'volume')
                );

        // テーブル
        $stmt->from(array('t_stock_change', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 在庫番号
        $stmt->where('t.stock_change_number', '=', $stock_change_number);

        // 検索実行
        return $stmt->execute($db)->current();
    }
    
    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }
            
    /**
     * 入出庫データ削除
     */
    public static function deleteRecord($stock_change_number, $old_data, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // レコード削除
        $conditions = array(
            'stock_change_number'   => $stock_change_number,
            'stock_number'          => $old_data['stock_number'],
            'stock_change_code'     => $old_data['stock_change_code'],
            'volume'                => $old_data['volume']
                );
        return D1121::delete_record($conditions, $old_data, $db);;
    }
        
    /**
     * 入出庫データ更新（売上ステータス）
     */
    public static function updateRecord($upd_list, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }
        
        if (empty($upd_list)) {
            return \Config::get('m_CW0010');
        }
        
        //売上ステータス更新ループ
        foreach ($upd_list as $record) {
            
            $stock_change_number = $record['stock_change_number'];
            $sales_status = $record['sales_status'];
            
            // レコード存在チェック
            if (!$result = self::getStockChange($stock_change_number, $db)) {
                return \Config::get('m_DW0032');
            }

            // レコード更新
            $result = self::updSalesStatus($stock_change_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0019')."[stock_change_number:".$stock_change_number."]");
                return \Config::get('m_DE0019');
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0032', \Config::get('m_DI0032'), '入出庫データ更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        
        return null;
    }
    
    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($stock_change_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($stock_change_number)) {
            return false;
        }
        
        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_stock_change')->set(array_merge($set, self::getEtcData(false)));

        // 入出庫番号
        $stmt->where('stock_change_number', '=', $stock_change_number);
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
}