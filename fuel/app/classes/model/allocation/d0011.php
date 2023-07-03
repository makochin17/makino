<?php
namespace Model\Allocation;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;
use \Model\Dispatch\D0040\D0040;

class D0011 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車データ（チャーター便）検索件数取得
     */
    public static function getSearchCount($conditions, $db, $search_mode) {
        return D0040::getSearchCount($conditions, $db, $search_mode);
    }

    /**
     * 配車データ（チャーター便）検索
     */
    public static function getSearch($conditions, $offset, $limit, $db, $search_mode) {
        return D0040::getSearch($conditions, $offset, $limit, $db, $search_mode);
    }
    
    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }
    
    /**
     * 庸車先の検索
     */
    public static function getSearchCarrier($code, $db) {
        return M0030::getCarrier($code, $db);
    }
    
    /**
     * 商品の検索
     */
    public static function getSearchProduct($code, $db) {
        return M0060::getProduct($code, $db);
    }
    
    /**
     * 車両の検索
     */
    public static function getSearchCar($code, $db) {
        return M0050::getCar($code, $db);
    }
    
    /**
     * 社員の検索
     */
    public static function getSearchMember($code, $db) {
        return M0010::getMember($code, $db);
    }
    
    /**
     * 配車データの取得（存在チェック用）
     */
    public static function getDispatchCharter($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));

        // テーブル
        $stmt->from(array('t_dispatch_charter', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 分載データの取得（存在チェック用）
     */
    public static function getCarryingCharter($dispatch_number = null, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 項目
        $stmt = \DB::select(
                array('t.carrying_number', 'carrying_number'),
                array('t.dispatch_number', 'dispatch_number')
                );

        // テーブル
        $stmt->from(array('t_carrying_charter', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 配車、分載データ削除
     */
    public static function deleteRecord($dispatch_number, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        $result = self::delDispatChcharter($dispatch_number, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0006')."[dispatch_number:".$dispatch_number."]");
            return \Config::get('m_DE0006');
        }

        if (!empty(self::getCarryingCharter($dispatch_number, $db))) {
            // 分載データ削除
            $result = self::delCarryingChcharter($dispatch_number, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0006')."[dispatch_number:".$dispatch_number."]");
                return \Config::get('m_DE0006');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0011', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0011'), '配車削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    
    /**
     * 配車データ削除
     */
    public static function delDispatChcharter($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 分載データ削除
     */
    public static function delCarryingChcharter($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }
        
        // 項目セット
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_carrying_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 配車データ更新（売上ステータス）
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
            
            $dispatch_number = $record['dispatch_number'];
            $sales_status = $record['sales_status'];
            
            // レコード存在チェック
            if (!$result = self::getDispatchCharter($dispatch_number, $db)) {
                return \Config::get('m_DW0001');
            }

            // レコード更新
            $result = self::updSalesStatus($dispatch_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0005')."[dispatch_number:".$dispatch_number."]");
                return \Config::get('m_DE0005');
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0010', \Config::get('m_DI0010'), '配車更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        
        return null;
    }
    
    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($dispatch_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }
        
        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
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