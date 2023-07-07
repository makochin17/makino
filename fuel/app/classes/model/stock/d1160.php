<?php
namespace Model\Stock;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0020;

class D1160 extends \Model {

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
            'storage_fee_number'    => '保管料番号',
            'division_code'         => '課コード',
            'division_name'         => '課名',
            'client_code'           => '得意先コード',
            'client_name'           => '得意先名',
            'closing_date'          => '締日',
            'storage_fee_code'      => '保管料区分コード',
            'storage_fee_name'      => '保管料区分名',
            'storage_fee'           => '保管料',
            'unit_price'            => '単価',
            'volume'                => '数量',
            'unit_code'             => '単位コード',
            'unit_name'             => '単位名',
            'rounding_code'         => '端数処理コード',
            'rounding_name'         => '端数処理名',
            'storage_location'      => '保管場所',
            'product_name'          => '商品名',
            'maker_name'            => 'メーカー名',
            'sales_status'          => '売上状態',
            'remarks'               => '備考',
        );

    }

    // フォームデータ
    public static function getForms() {

        return array(
            // 保管料番号
            'storage_fee_number'    => '',
            // 課
            'division_code'         => '',
            // 売上状態
            'sales_status'          => '',
            // 締日
            'from_closing_date'     => '',
            'to_closing_date'       => '',
            // 得意先
            'client_code'           => '',
            // 保管場所
            'storage_location'      => '',
            // 商品名
            'product_name'          => '',
            // メーカー名
            'maker_name'            => '',
            // 保管料区分コード
            'storage_fee_code'      => '',
            // 登録者
            'create_user'           => '',
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
            // 保管料番号
            'storage_fee_number'    => array('name' => '保管料番号', 'max_lengths' => '10'),
            // 得意先
            'client_code'           => array('name' => '得意先', 'max_lengths' => '5'),
            // 保管場所
            'storage_location'      => array('name' => '保管場所', 'max_lengths' => '15'),
            // 商品名
            'product_name'          => array('name' => '商品名', 'max_lengths' => '30'),
            // メーカー名
            'maker_name'            => array('name' => 'メーカー名', 'max_lengths' => '15'),
            // 日付
            'from_closing_date'     => array('name' => '締日From', 'max_lengths' => ''),
            'to_closing_date'       => array('name' => '締日To', 'max_lengths' => ''),
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
     * 保管料レコード検索 & 保管料レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(\DB::expr('COUNT(tsf.storage_fee_number) AS count'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('tsf.storage_fee_number', 'storage_fee_number'),
                            array('tsf.sales_status', 'sales_status'),
                            array('tsf.division_code', 'division_code'),
                            array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = tsf.division_code)'), 'division_name'),
                            array('tsf.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = tsf.client_code AND start_date <= tsf.update_datetime AND end_date > tsf.update_datetime)'), 'client_name'),
                            array('tsf.closing_date', 'closing_date'),
                            array('tsf.storage_fee_code', 'storage_fee_code'),
                            array('tsf.storage_fee', 'storage_fee'),
                            array('tsf.unit_price', 'unit_price'),
                            array('tsf.volume', 'volume'),
                            array('tsf.unit_code', 'unit_code'),
                            array('tsf.rounding_code', 'rounding_code'),
                            array('tsf.storage_location', 'storage_location'),
                            array('tsf.product_name', 'product_name'),
                            array('tsf.maker_name', 'maker_name'),
                            array('tsf.remarks', 'remarks'),
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_storage_fee', 'tsf'));
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('tsf.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 'tsf.update_datetime')
                ->on('mcl.end_date', '>', 'tsf.update_datetime');
        }
        // 課コード
        if (!empty($conditions['division_code']) && trim($conditions['division_code']) != '000') {
            $stmt->join(array('m_division', 'md'), 'INNER')
                ->on('tsf.division_code', '=', 'md.division_code')
                ->on('tsf.division_code', '=', \DB::expr("'".$conditions['division_code']."'"));
        }

        // 保管料番号
        if (!empty($conditions['storage_fee_number'])) {
            $stmt->where(\DB::expr('CAST(tsf.storage_fee_number AS SIGNED)'), '=', $conditions['storage_fee_number']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('tsf.sales_status', '=', $conditions['sales_status']);
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('tsf.client_code', '=', $conditions['client_code']);
        }
        // 保管場所
        if (!empty($conditions['storage_location'])) {
            $stmt->where('tsf.storage_location', 'LIKE', \DB::expr("'%".$conditions['storage_location']."%'"));
        }
        // 商品名
        if (!empty($conditions['product_name'])) {
            $stmt->where('tsf.product_name', 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // メーカー名
        if (!empty($conditions['maker_name'])) {
            $stmt->where('tsf.maker_name', 'LIKE', \DB::expr("'%".$conditions['maker_name']."%'"));
        }
        // 区分
        if (!empty($conditions['storage_fee_code']) && trim($conditions['storage_fee_code']) != '0') {
            $stmt->where('tsf.storage_fee_code', '=', $conditions['storage_fee_code']);
        }
        // 締日
        if (!empty($conditions['from_closing_date']) && trim($conditions['to_closing_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_closing_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_closing_date'])))->format('mysql_date');
            $stmt->where('tsf.closing_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_closing_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_closing_date'])))->format('mysql_date');
                $stmt->where('tsf.closing_date', '>=', $date);
            }
            if (!empty($conditions['to_closing_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_closing_date'])))->format('mysql_date');
                $stmt->where('tsf.closing_date', '<=', $date);
            }
        }
        // 登録者
        if (!empty($conditions['create_user'])) {
            $stmt->where('tsf.create_user', '=', $conditions['create_user']);
        }
        $stmt->where('tsf.delete_flag', '=', '0');

        // 検索実行
        switch ($type) {
            case 'count':
                $res = $stmt->execute($db)->as_array();
                return $res[0]['count'];
                break;
            case 'export':
                return $stmt->order_by('tsf.storage_fee_number', 'ASC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('tsf.closing_date', 'DESC')->order_by('tsf.client_code', 'DESC')->order_by('tsf.storage_fee_number', 'DESC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

}