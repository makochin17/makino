<?php
namespace Model\Stock;
use \Model\Common\GenerateList;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;

class D1131 extends \Model {

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

    /**
     * 保管料区分「定額」のレコード重複チェック
     */
    public static function checkStorageFee($storage_fee_number = null, $closing_date, $client_code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($closing_date) || empty($client_code)) {
            return false;
        }

        $ret = \DB::select()
        ->from('t_storage_fee')
        ->where('delete_flag', 0)
        ->where('storage_fee_code', 1);
        if (!empty($storage_fee_number)) {
            $ret = $ret->where('storage_fee_number', '!=', $storage_fee_number);
        }
        $res = $ret->where('closing_date', $closing_date)
        ->where('client_code', (int)$client_code)
        ->execute($db)
        ->as_array()
        ;

        return $res;
    }

    /**
     * 指定項目NULLチェック
     */
    public static function chkStorageFeeDataNull($data) {

        // if (empty($data['closing_date']) && empty($data['client_code']) && empty($data['storage_location']) && empty($data['product_name']) && empty($data['maker_name']) && empty($data['storage_fee']) && empty(floatval($data['unit_price'])) && empty(floatval($data['volume'])) && empty($data['remarks'])) {
        if (empty($data['closing_date']) && empty($data['client_code']) && empty($data['storage_location']) && empty($data['product_name']) && empty($data['maker_name']) && empty($data['remarks'])) {
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
                    'remarks'               => '備考',
                );
                break;
        }

        return $res;
    }

    // フォームデータ
    public static function getForms($type = 'storagefee') {

        $res = array();
        switch ($type) {
            case 'storagefee':
            default:
                $tmp = array(
                    'processing_division'   => '1',
                    'storage_fee_number'    => '',
                    'division_code'         => '',
                    'list'                  => array(),
                );
                $sub_tmp = array(
                    // 保管料番号
                    'storage_fee_number'    => '',
                    // 課
                    'division_code'         => '',
                    // 得意先
                    'client_code'           => '',
                    'client_name'           => '',
                    // 売上状態
                    'sales_status'          => '',
                    // 締日
                    'closing_date'          => '',
                    // 保管場所
                    'storage_location'      => '',
                    // 商品名
                    'product_name'          => '',
                    // メーカー名
                    'maker_name'            => '',
                    // 保管料区分コード
                    'storage_fee_code'      => '',
                    // 保管料
                    'storage_fee'           => '',
                    // 単価
                    'unit_price'            => '',
                    // 数量
                    'volume'                => '',
                    // 単位
                    'unit_code'             => '',
                    // 端数処理コード
                    'rounding_code'         => '',
                    // 備考
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

    public static function setForms($type = 'storagefee', $conditions, $input_data) {

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

    // 入力チェック項目
    public static function getValidateItems() {

        return array(
            // 保管料番号
            'storage_fee_number'    => array('name' => '保管料番号', 'max_lengths' => '10'),
            // 得意先
            'client_code'           => array('name' => '得意先', 'max_lengths' => '5'),
            // 保管場所
            'storage_location'      => array('name' => '保管場所', 'max_lengths' => '15'),
            // 保管料
            'storage_fee'           => array('name' => '保管料', 'max_lengths' => '10'),
            // 単価
            'unit_price'            => array('name' => '単価', 'max_lengths' => '10'),
            // 数量
            'volume'                => array('name' => '数量', 'max_lengths' => '10'),
            // 商品名
            'product_name'          => array('name' => '商品名', 'max_lengths' => '30'),
            // メーカー名
            'maker_name'            => array('name' => 'メーカー名', 'max_lengths' => '15'),
            // 備考
            'remarks'               => array('name' => '備考', 'max_lengths' => '15'),
            // 日付
            'closing_date'          => array('name' => '締日', 'max_lengths' => ''),
        );
    }

    //=========================================================================//
    //==============================   対象登録   ==============================//
    //=========================================================================//
    public static function create_record($conditions, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード登録
        $insert_id = self::addStorageFee($conditions, $db);
        if (!$insert_id) {
            \Log::error(\Config::get('m_DE0021')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0021');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0041', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0041'), '保管料登録', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 在庫データ登録
     */
    public static function addStorageFee($data, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($data)) {
            return false;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key');

        $set = array(
            'division_code'         => $data['division_code'],
            'sales_status'          => (empty($data['sales_status'])) ? 1:$data['sales_status'],
            'closing_date'          => $data['closing_date'],
            'client_code'           => $data['client_code'],
            'storage_fee_code'      => $data['storage_fee_code'],
            'unit_price'            => (!empty($data['unit_price'])) ? str_replace(',', '', $data['unit_price']):null,
            'volume'                => (!empty($data['volume'])) ? str_replace(',', '', $data['volume']):null,
            'storage_fee'           => str_replace(',', '', $data['storage_fee']),
            'unit_code'             => $data['unit_code'],
            'rounding_code'         => $data['rounding_code'],
            'storage_location'      => (!empty($data['storage_location'])) ? $data['storage_location']:null,
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
        );
        $set = array_merge($set, self::getEtcData(true));

        // 登録実行
        list($insert_id, $rows_affected) = \DB::insert('t_storage_fee')->set($set)->execute($db);

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
        $result = self::updStorageFee($conditions, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0022')."[".print_r($conditions,true)."]");
            return \Config::get('m_DE0022');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0042', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0042'), '保管料更新', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }
        return null;
    }

    /**
     * 在庫データ更新
     */
    public static function updStorageFee($data, $db = null) {

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
            'sales_status'          => (empty($data['sales_status'])) ? 1:$data['sales_status'],
            'closing_date'          => $data['closing_date'],
            'client_code'           => $data['client_code'],
            'storage_fee_code'      => $data['storage_fee_code'],
            'unit_price'            => (!empty($data['unit_price'])) ? str_replace(',', '', $data['unit_price']):null,
            'volume'                => (!empty($data['volume'])) ? str_replace(',', '', $data['volume']):null,
            'storage_fee'           => str_replace(',', '', $data['storage_fee']),
            'unit_code'             => $data['unit_code'],
            'rounding_code'         => $data['rounding_code'],
            'storage_location'      => (!empty($data['storage_location'])) ? $data['storage_location']:null,
            'product_name'          => $data['product_name'],
            'maker_name'            => (!empty($data['maker_name'])) ? $data['maker_name']:null,
            'remarks'               => (!empty($data['remarks'])) ? $data['remarks']:null,
        );

        // テーブル
        $stmt = \DB::update('t_storage_fee')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('storage_fee_number', '=', $data['storage_fee_number']);
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

        // レコード削除
        $result = self::delStorageFee($conditions['storage_fee_number'], $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0023')."[storage_fee_number:".$conditions['storage_fee_number']."]");
            return \Config::get('m_DE0023');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0043', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0043'), '保管料削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }
    /**
     * 在庫データ削除
     */
    public static function delStorageFee($storage_fee_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($storage_fee_number)) {
            return false;
        }

        // 項目セット
        $set = array(
            'delete_flag' => 1
        );

        // テーブル
        $stmt = \DB::update('t_storage_fee')->set(array_merge($set, self::getEtcData(false)));

        // 在庫番号
        $stmt->where('storage_fee_number', '=', $storage_fee_number);
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
     * レコード取得
     */
    public static function getStorageFee($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(
                array('t.storage_fee_number', 'storage_fee_number'),
                array('t.sales_status', 'sales_status'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = t.division_code)'), 'division_name'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.closing_date', 'closing_date'),
                array('t.storage_fee_code', 'storage_fee_code'),
                array('t.storage_fee', 'storage_fee'),
                array('t.unit_price', 'unit_price'),
                array('t.volume', 'volume'),
                array('t.unit_code', 'unit_code'),
                array('t.rounding_code', 'rounding_code'),
                array('t.storage_location', 'storage_location'),
                array('t.product_name', 'product_name'),
                array('t.maker_name', 'maker_name'),
                array('t.remarks', 'remarks'),
                );

        // テーブル
        $stmt->from(array('t_storage_fee', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');

        // 在庫番号
        $stmt->where('t.storage_fee_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
    }
}