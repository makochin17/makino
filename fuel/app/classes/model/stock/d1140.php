<?php
namespace Model\Stock;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;

class D1140 extends \Model {

    public static $db           = 'ONISHI';

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
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // エクスポートファイル用ヘッダー
    public static function getHeader() {

        return array(
            'stock_number'      	 => '在庫番号',
            'division_code'     	 => '課コード',
            'division_name'     	 => '課名',
            'client_code'       	 => '得意先コード',
            'client_name'       	 => '得意先名',
            'product_name'      	 => '商品名',
            'maker_name'             => 'メーカー名',
            'part_number'            => '品番',
            'model_number'           => '型番',
            'total_volume'           => '数量合計',
            'unit_code'          	 => '単位コード',
            'unit_name'         	 => '単位名',
            'storage_location'       => '保管場所',
            'remarks'           	 => '備考',
        );

    }

    // フォームデータ
    public static function getForms() {

        return array(
            // 在庫番号
            'stock_number'              => '',
            // 課
            'division_code'             => '',
            // 得意先
            'client_code'               => '',
            // 保管場所
            'storage_location'          => '',
            // 商品名
            'product_name'              => '',
            // メーカー名
            'maker_name'                => '',
            // 品番
            'part_number'               => '',
            // 型番
            'model_number'              => '',
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
            // 在庫番号
            'stock_number'              => array('name' => '在庫番号', 'max_lengths' => '10'),
            // 得意先
            'client_code'               => array('name' => '得意先', 'max_lengths' => '5'),
            // 保管場所
            'storage_location'          => array('name' => '保管場所', 'max_lengths' => '15'),
            // 商品名
            'product_name'              => array('name' => '商品名', 'max_lengths' => '30'),
            // メーカー名
            'maker_name'                => array('name' => 'メーカー名', 'max_lengths' => '15'),
            // 品番
            'part_number'               => array('name' => '品番', 'max_lengths' => '15'),
            // 型番
            'model_number'              => array('name' => '型番', 'max_lengths' => '15'),
        );
    }

    public static function getNameById($type, $id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        switch ($type) {
            case 'client':
                return \DB::select(
                    array('client_code', 'client_code'),
                    array('client_name', 'client_name')
                )
                ->from('m_client')
                ->where('client_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
        }

        return false;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 在庫レコード検索 & 在庫レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(ts.stock_number) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('ts.stock_number', 'stock_number'),
                            array('ts.division_code', 'division_code'),
                            array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = ts.division_code)'), 'division_name'),
                            array('ts.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = ts.client_code AND start_date <= ts.update_datetime AND end_date > ts.update_datetime)'), 'client_name'),
                            array('ts.storage_location', 'storage_location'),
                            array('ts.product_name', 'product_name'),
                            array('ts.maker_name', 'maker_name'),
                            array('ts.part_number', 'part_number'),
                            array('ts.model_number', 'model_number'),
                            array('ts.total_volume', 'total_volume'),
                            array('ts.unit_code', 'unit_code'),
                            array('ts.remarks', 'remarks'),
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_stock', 'ts'));
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
        
        // 在庫番号
        if (!empty($conditions['stock_number'])) {
            $stmt->where(\DB::expr('CAST(ts.stock_number AS SIGNED)'), '=', $conditions['stock_number']);
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('ts.client_code', '=', $conditions['client_code']);
        }
        // 保管場所
        if (!empty($conditions['storage_location'])) {
            $stmt->where('ts.storage_location', 'LIKE', \DB::expr("'%".$conditions['storage_location']."%'"));
        }
        // 商品名
        if (!empty($conditions['product_name'])) {
            $stmt->where('ts.product_name', 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // メーカー名
        if (!empty($conditions['maker_name'])) {
            $stmt->where('ts.maker_name', 'LIKE', \DB::expr("'%".$conditions['maker_name']."%'"));
        }
        // 品番
        if (!empty($conditions['part_number'])) {
            $stmt->where('ts.part_number', 'LIKE', \DB::expr("'%".$conditions['part_number']."%'"));
        }
        // 型番
        if (!empty($conditions['model_number'])) {
            $stmt->where('ts.model_number', 'LIKE', \DB::expr("'%".$conditions['model_number']."%'"));
        }
        // 登録者
        if (!empty($conditions['create_user'])) {
            $stmt->where('ts.create_user', '=', $conditions['create_user']);
        }
        $stmt->where('ts.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('ts.stock_number', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('ts.division_code', 'ASC')->order_by('ts.client_code', 'ASC')->order_by('ts.product_name', 'ASC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

}