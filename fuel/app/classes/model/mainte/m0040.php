<?php
namespace Model\Mainte;
use \Model\Init;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;

class M0040 extends \Model {

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
            $result = OpeLog::addOpeLog('MI0020', \Config::get('m_MI0020'), '', $db);
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
        $title = mb_convert_encoding('車種マスタ一覧', 'SJIS', 'UTF-8');
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
        $result += array("car_model_code" => "車種コード");
        $result += array("car_model_name" => "車種名");
        $result += array("tonnage" => "トン数");
        $result += array("aggregation_tonnage" => "集約トン数");
        $result += array("freight_tonnage" => "積載トン数");
        
        return $result;
    }
    
    /**
     * TSV用データ取得
     */
    public static function getBody($db) {
        
        // 取得データ
        $stmt = \DB::select(
                array('m.car_model_code', 'car_model_code'),
                array('m.car_model_name', 'car_model_name'),
                array('m.tonnage', 'tonnage'),
                array('m.aggregation_tonnage', 'aggregation_tonnage'),
                array('m.freight_tonnage', 'freight_tonnage'),
                );

        // テーブル
        $stmt->from(array('m_car_model', 'm'));
                
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        $result = $stmt->order_by('m.car_model_code', 'ASC')->execute($db)->as_array();
        
        return $result;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 車種マスタレコード取得
     */
    public static function getCarModel($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.car_model_code', 'car_model_code'),
                array('m.car_model_name', 'car_model_name'),
                array('m.tonnage', 'tonnage'),
                array('m.aggregation_tonnage', 'aggregation_tonnage'),
                array('m.freight_tonnage', 'freight_tonnage'),
                array('m.sort', 'sort'),
                array('m.start_date', 'start_date')
                );

        // テーブル
        $stmt->from(array('m_car_model', 'm'));
        
        // 車種コード
        $stmt->where('m.car_model_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('m.end_date', '>', Date::forge()->format('mysql_date'));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 車種マスタ登録
     */
    public static function addCarModel($items, $db) {
        
        //ソート順が設定されていない場合は最大値+1を設定
        $sort = $items['sort'];
        if ($sort == ''){
            $sort = self::getSortNext($db);
        }
        
        // 項目セット
        $set = array(
            'car_model_code' => $items['car_model_code'],
            'car_model_name' => $items['car_model_name'],
            'tonnage' => $items['tonnage'],
            'aggregation_tonnage' => $items['aggregation_tonnage'],
            'freight_tonnage' => $items['freight_tonnage'],
            'sort' => $sort,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'car_model_name = VALUES(car_model_name),'
                . 'tonnage = VALUES(tonnage),'
                . 'aggregation_tonnage = VALUES(aggregation_tonnage),'
                . 'freight_tonnage = VALUES(freight_tonnage),'
                . 'sort = VALUES(sort),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        
        // 登録実行
        $stmt = \DB::insert('m_car_model')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 車種マスタ更新
     */
    public static function updCarModel($items, $db) {

        // テーブル
        $stmt = \DB::update('m_car_model');
        
        // 項目セット
        $set = array(
            'car_model_name' => $items['car_model_name'],
            'tonnage' => $items['tonnage'],
            'aggregation_tonnage' => $items['aggregation_tonnage'],
            'freight_tonnage' => $items['freight_tonnage'],
            'sort' => $items['sort'],
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車種コード
        $stmt->where('car_model_code', '=', $items['car_model_code']);
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
     * 車種マスタ削除（論理削除）
     */
    public static function delCarModel($code, $db) {

        // テーブル
        $stmt = \DB::update('m_car_model');
        
        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車種コード
        $stmt->where('car_model_code', '=', $code);
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
        $stmt->from(array('m_car_model', 'm'));
        
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