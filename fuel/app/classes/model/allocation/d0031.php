<?php
namespace Model\Allocation;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Dispatch\D0060\D0060;

class D0031 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車データ（チャーター便）検索件数取得
     */
    public static function getSearchCount($conditions, $db, $search_mode) {
        return D0060::getSearchCount($conditions, $db, $search_mode);
    }

    /**
     * 配車データ（チャーター便）検索
     */
    public static function getSearch($conditions, $offset, $limit, $db, $search_mode) {
        return D0060::getSearch($conditions, $offset, $limit, $db, $search_mode);
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
     * 月極その他データの取得（存在チェック用）
     */
    public static function getSalesCorrection($sales_correction_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.sales_correction_number', 'sales_correction_number'));

        // テーブル
        $stmt->from(array('t_sales_correction', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.sales_correction_number', '=', $sales_correction_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }
        
    /**
     * 月極その他データ削除
     */
    public static function deleteRecord($sales_correction_number, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        $result = self::delSalesCorrection($sales_correction_number, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0009')."[sales_correction_number:".$sales_correction_number."]");
            return \Config::get('m_DE0009');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0014', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0014'), '月極その他データ削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    
    /**
     * 月極その他データ削除（SQL）
     */
    public static function delSalesCorrection($sales_correction_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($sales_correction_number)) {
            return false;
        }

        // 項目セット
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_sales_correction')->set(array_merge($set, self::getEtcData(false)));

        // 売上補正コード
        $stmt->where('sales_correction_number', '=', $sales_correction_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 月極その他データ更新（売上ステータス）
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
            
            $sales_correction_number = $record['sales_correction_number'];
            $sales_status = $record['sales_status'];
            
            // レコード存在チェック
            if (!$result = self::getSalesCorrection($sales_correction_number, $db)) {
                return \Config::get('m_DW0011');
            }

            // レコード更新
            $result = self::updSalesStatus($sales_correction_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0008')."[sales_correction_number:".$sales_correction_number."]");
                return \Config::get('m_DE0008');
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0013', \Config::get('m_DI0013'), '月極その他データ更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        
        return null;
    }
    
    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($sales_correction_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($sales_correction_number)) {
            return false;
        }
        
        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_sales_correction')->set(array_merge($set, self::getEtcData(false)));

        // 売上補正コード
        $stmt->where('sales_correction_number', '=', $sales_correction_number);
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