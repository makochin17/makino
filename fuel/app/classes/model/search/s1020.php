<?php
namespace Model\Search;
use \Model\Common\SystemConfig;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;
use \Model\Dispatch\D0040\D0040;

class S1020 extends \Model {

    public static $db       = 'ONISHI';

    // フォームデータ
    public static function getForms() {

        return array(
            // 入出庫番号
            'stock_change_number'       => '',
            // 在庫番号
            'stock_number'              => '',
            // 課
            'division_code'             => '',
            // 売上状態
            'sales_status'              => '',
            // 得意先
            'client_code'               => '',
            // 商品名
            'product_name'              => '',
            // 区分
            'stock_change_code'         => '',
            // 日付
            'from_destination_date'     => '',
            'to_destination_date'       => '',
            // 運行先
            'destination'               => '',
            // 登録者
            'create_user'               => '',
        );
    }

    public static function setForms($conditions, $input_data) {

        if (empty($conditions)) {
            return self::getForms();
        }

        foreach ($conditions as $key => $cols) {
            $conditions[$key] = $input_data[$key];
        }

        return $conditions;
    }
    
    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 入出庫番号
            'stock_change_number'       => array('name' => '入出庫番号', 'max_lengths' => '10'),
            // 在庫番号
            'stock_number'              => array('name' => '在庫番号', 'max_lengths' => '10'),
            // 得意先
            'client_code'               => array('name' => '得意先', 'max_lengths' => '5'),
            // 商品名
            'product_name'              => array('name' => '商品名', 'max_lengths' => '30'),
            // 日付
            'from_destination_date'     => array('name' => '日付From', 'max_lengths' => ''),
            'to_destination_date'       => array('name' => '日付To', 'max_lengths' => ''),
            // 運行先
            'destination'               => array('name' => '運行先', 'max_lengths' => '30'),
        );
    }
    
    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 入出庫レコード検索 & 入出庫レコード検索件数取得
     */
    public static function getStockChange($type = 'search', $conditions, $offset, $limit, $db = null) {

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
                            array('ts.division_code', 'division_code'),
                            array('tsc.sales_status', 'sales_status'),
                            array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = ts.division_code)'), 'division_name'),
                            array('ts.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = ts.client_code AND start_date <= ts.update_datetime AND end_date > ts.update_datetime)'), 'client_name'),
                            array('ts.product_name', 'product_name'),
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
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('ts.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 'ts.update_datetime')
                ->on('mcl.end_date', '>', 'ts.update_datetime');
        }
        // 課コード
        if (!empty($conditions['division_code']) && trim($conditions['division_code']) != '000') {
            $stmt->join(array('m_division', 'md'), 'INNER')
                ->on('ts.division_code', '=', 'md.division_code')
                ->on('ts.division_code', '=', \DB::expr("'".$conditions['division_code']."'"));
        }
        
        // 入出庫番号
        if (!empty($conditions['stock_change_number'])) {
            $stmt->where(\DB::expr('CAST(tsc.stock_change_number AS SIGNED)'), '=', $conditions['stock_change_number']);
        }
        // 在庫番号
        if (!empty($conditions['stock_number'])) {
            $stmt->where(\DB::expr('CAST(tsc.stock_number AS SIGNED)'), '=', $conditions['stock_number']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('tsc.sales_status', '=', $conditions['sales_status']);
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('ts.client_code', '=', $conditions['client_code']);
        }
        // 商品
        if (!empty($conditions['product_name'])) {
            $stmt->where('ts.product_name', 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // 区分
        if (!empty($conditions['stock_change_code']) && trim($conditions['stock_change_code']) != '0') {
            $stmt->where('tsc.stock_change_code', '=', $conditions['stock_change_code']);
        }
        // 日付
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
        $stmt->where('tsc.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('tsc.destination_date', 'DESC')->order_by('ts.division_code', 'ASC')->order_by('ts.client_code', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('tsc.destination_date', 'DESC')->order_by('ts.division_code', 'ASC')->order_by('ts.client_code', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }
    
    /**
     * 入出庫データ検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return self::getStockChange('count', $conditions, null, null, $db);
    }

    /**
     * 入出庫データ検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return self::getStockChange('search', $conditions, $offset, $limit, $db);
    }
    
    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }
    
}