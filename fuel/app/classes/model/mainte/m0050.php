<?php
namespace Model\Mainte;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;

class M0050 extends \Model {

    public static $db       = 'MAKINO';

    /**
     * エクセル作成処理
     */
    public static function createTsv($db) {
        //出力データ取得
        $header = self::getHeader($db);
        $body = self::getBody($db);
        
        try {
            \DB::start_transaction($db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0021', \Config::get('m_MI0021'), '', $db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction($db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction($db);
            \Log::error($e->getMessage());
        }
        
        //ファイル名設定
        $title = mb_convert_encoding('車両マスタ一覧', 'SJIS', 'UTF-8');
        $fileName = $title.'.tsv';

        //HTMLヘッダー
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);

        //ファイルへの書き込み
        $handle = fopen('php://output', 'w');
        
        mb_convert_variables('SJIS-win', 'UTF-8', $header);
        fputcsv($handle, $header, "\t");
        
        foreach ($body as $row) {
            mb_convert_variables('SJIS-win', 'UTF-8', $row);
            fputcsv($handle, $row, "\t");
        }
        
        fclose($handle);

        exit();
    }
    
    /**
     * TSV用ヘッダー情報取得
     */
    public static function getHeader($db) {
        $result = array();
        $result += array("car_code" => "車両コード");
        $result += array("car_number" => "車両番号");
        $result += array("car_name" => "車両名");
        $result += array("car_model_code" => "車種コード");
        $result += array("car_model_name" => "車種");
        
        return $result;
    }
    
    /**
     * TSV用データ取得
     */
    public static function getBody($db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 取得データ
        $stmt = \DB::select(
                array(\DB::expr('CONCAT("=\"",LPAD(m.car_code, 4, "0"),"\"")'), 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_name),"'.$encrypt_key.'")'), 'car_name'),
                array('m.car_model_code', 'car_model_code'),
                array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = m.car_model_code AND start_date <= m.update_datetime AND end_date > m.update_datetime)'), 'car_model_name'),
                );

        // テーブル
        $stmt->from(array('m_car', 'm'));
                
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        $result = $stmt->order_by('m.car_code', 'ASC')->execute($db)->as_array();
        
        return $result;
    }
    
    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 車両マスタレコード取得
     */
    public static function getCar($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('m.car_code', 'car_code'),
                array('m.car_model_code', 'car_model_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_name),"'.$encrypt_key.'")'), 'car_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_car', 'm'));
        
        // 車両コード
        $stmt->where('m.car_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 車両マスタ登録
     */
    public static function addCar($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 項目セット
        $set = array(
            'car_code' => $items['car_code'],
            'car_model_code' => $items['car_model_code'],
            'car_name' => \DB::expr('HEX(AES_ENCRYPT("'.$items['car_name'].'","'.$encrypt_key.'"))'),
            'car_number' => \DB::expr('HEX(AES_ENCRYPT("'.$items['car_number'].'","'.$encrypt_key.'"))'),
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'car_model_code = VALUES(car_model_code),'
                . 'car_name = VALUES(car_name),'
                . 'car_number = VALUES(car_number),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        
        // 登録実行
        $stmt = \DB::insert('m_car')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 車両マスタ更新
     */
    public static function updCar($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // テーブル
        $stmt = \DB::update('m_car');
        
        // 項目セット
        $set = array(
            'car_model_code' => $items['car_model_code'],
            'car_name' => \DB::expr('HEX(AES_ENCRYPT("'.$items['car_name'].'","'.$encrypt_key.'"))'),
            'car_number' => \DB::expr('HEX(AES_ENCRYPT("'.$items['car_number'].'","'.$encrypt_key.'"))'),
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車両コード
        $stmt->where('car_code', '=', $items['car_code']);
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
     * 車両マスタ削除（論理削除）
     */
    public static function delCar($code, $db) {

        // テーブル
        $stmt = \DB::update('m_car');
        
        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車両コード
        $stmt->where('car_code', '=', $code);
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