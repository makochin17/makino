<?php
namespace Model\Mainte;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Date;

class M0060 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 商品マスタレコード取得
     */
    public static function getProduct($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.product_code', 'product_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.product_name),"'.$encrypt_key.'")'), 'product_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.category),"'.$encrypt_key.'")'), 'category'),
                array('m.sort', 'sort'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_product', 'm'));
        
        // 商品コード
        $stmt->where('m.product_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 商品マスタ登録
     */
    public static function addProduct($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        //ソート順が設定されていない場合は最大値+1を設定
        $sort = $items['sort'];
        if ($sort == ''){
            $sort = self::getSortNext($db);
        }
        
        // 項目セット
        $set = array(
            'product_code' => $items['product_code'],
            'product_name' => \DB::expr('HEX(AES_ENCRYPT("'.$items['product_name'].'","'.$encrypt_key.'"))'),
            'category' => \DB::expr('HEX(AES_ENCRYPT("'.$items['category'].'","'.$encrypt_key.'"))'),
            'sort' => $sort,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'product_name = VALUES(product_name),'
                . 'category = VALUES(category),'
                . 'sort = VALUES(sort),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        
        // 登録実行
        $stmt = \DB::insert('m_product')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 商品マスタ更新
     */
    public static function updProduct($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // テーブル
        $stmt = \DB::update('m_product');
        
        // 項目セット
        $set = array(
            'product_code' => $items['product_code'],
            'product_name' => \DB::expr('HEX(AES_ENCRYPT("'.$items['product_name'].'","'.$encrypt_key.'"))'),
            'category' => \DB::expr('HEX(AES_ENCRYPT("'.$items['category'].'","'.$encrypt_key.'"))'),
            'sort' => $items['sort'],
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 商品コード
        $stmt->where('product_code', '=', $items['product_code']);
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
     * 商品マスタ削除（論理削除）
     */
    public static function delProduct($code, $db) {

        // テーブル
        $stmt = \DB::update('m_product');
        
        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 商品コード
        $stmt->where('product_code', '=', $code);
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
     * ソート順の最大値+1を取得
     */
    public static function getSortNext($db) {
        // 項目
        $stmt = \DB::select(
                array(\DB::expr('MAX(m.sort)'), 'sort')
                );

        // テーブル
        $stmt->from(array('m_product', 'm'));
        
        // 適用開始日
        $stmt->where('m.start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('m.end_date', '>', Date::forge()->format('mysql_date'));
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        return (int)$result[0]['sort'] + 1;
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