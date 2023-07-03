<?php
namespace Model\Printing;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

use \Model\Printing\T1121;
use \Model\Printing\T1122;
use \Model\Printing\T1123;
use \Model\Printing\T1124;

class T1120 extends \Model {

    public static $db           = 'ONISHI';
    
    /**
     * 納品書区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDeliverySlipList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.delivery_slip_code', 'delivery_slip_code'),
                array('m.delivery_slip_name', 'client_name'),
                );

        // テーブル
        $stmt->from(array('m_delivery_slip', 'm'));
        // ソート
        $stmt->order_by('m.delivery_slip_name', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $delivery_slip_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $delivery_slip_list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $delivery_slip_list[$item['delivery_slip_code']] = $item['client_name'];
        }
        
        return $delivery_slip_list;
    }
    
    //納品書区分コードから得意先コード取得
    public static function getClientCode($delivery_slip_code) {

        // 項目
        $stmt = \DB::select(array('m.client_code', 'client_code'));
        // テーブル
        $stmt->from(array('m_delivery_slip', 'm'));
        // 条件
        $stmt->where('m.delivery_slip_code', '=', $delivery_slip_code);
        
        // 検索実行
        $result = $stmt->execute(self::$db)->current();
        return $result['client_code'];
    }
    
    //納品書区分コードから出力処理ID取得
    public static function getProgramId($delivery_slip_code) {

        // 項目
        $stmt = \DB::select(array('m.program_id', 'program_id'));
        // テーブル
        $stmt->from(array('m_delivery_slip', 'm'));
        // 条件
        $stmt->where('m.delivery_slip_code', '=', $delivery_slip_code);
        
        // 検索実行
        $result = $stmt->execute(self::$db)->current();
        return $result['program_id'];
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

    // ユーザー権限
    public static function permission() {
        return array('0' => '-') + \Config::load('userpermission');
    }

    // フォームデータ
    public static function getForms() {

        return array(
            // 配車番号
            'dispatch_number'           => '',
            // 課
            'division_code'             => '',
            // 納品日
            'from_delivery_date'        => '',
            'to_delivery_date'          => '',
            // 納品先
            'delivery_place'            => '',
            // 得意先（納品書区分コード）
            'delivery_slip_code'        => '',
            // 傭車先
            'carrier_code'              => '',
            // 車種
            'car_model_code'            => '',
            // 車両番号
            'car_code'                  => '',
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
            // 配車番号
            'dispatch_number'           => array('name' => '配車番号', 'max_lengths' => '10'),
            // 納品先
            'delivery_place'            => array('name' => '納品先', 'max_lengths' => '15'),
            // 傭車先
            'carrier_code'              => array('name' => '傭車先', 'max_lengths' => '5'),
            // 車両番号
            'car_code'                  => array('name' => '車両番号', 'max_lengths' => '4'),
            // 納品日
            'from_delivery_date'        => array('name' => '納品日From', 'max_lengths' => ''),
            'to_delivery_date'          => array('name' => '納品日To', 'max_lengths' => ''),
        );
    }

    public static function getNameById($type, $id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        switch ($type) {
            case 'carrier':
                return \DB::select(
                    array('carrier_code', 'carrier_code'),
                    array('carrier_name', 'carrier_name')
                )
                ->from('m_carrier')
                ->where('carrier_code', $id)
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>', date('Y-m-d'))
                ->execute($db)->current();
                break;
            case 'car':
                return \DB::select(
                    array('car_code', 'car_code'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(car_number),"'.$encrypt_key.'")'), 'car_number'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(car_name),"'.$encrypt_key.'")'), 'car_name')
                )
                ->from('m_car')
                ->where('car_code', $id)
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
     * 配車レコード検索 & 配車レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        
        // 納品書区分コードから得意先コード取得
        $client_code = self::getClientCode($conditions['delivery_slip_code']);

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'count':
                $stmt = \DB::select(
                    array('t.division_code', 'division_code'),
                    array('t.delivery_date', 'delivery_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                    array('t.client_code', 'client_code'),
                    array('t.carrier_code', 'carrier_code'),
                    array('t.car_model_code', 'car_model_code'),
                    array('t.car_code', 'car_code'),
                );
                break;
            case 'export':
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.carrier_code', 'DESC')->order_by('t.car_code', 'DESC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                    array('t.division_code', 'division_code'),
                    array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = t.division_code)'), 'division_name'),
                    array('t.delivery_date', 'delivery_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                    array('t.client_code', 'client_code'),
                    array('t.carrier_code', 'carrier_code'),
                    array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                    array('t.car_model_code', 'car_model_code'),
                    array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                    array('t.car_code', 'car_code'),
                );
                break;
        }
        

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');
        }
        // 傭車先
        if (!empty($conditions['carrier_name'])) {
            $stmt->join(array('m_carrier', 'mca'), 'INNER')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime');
        }
        // 課コード
        if (!empty($conditions['division_code']) && trim($conditions['division_code']) != '000') {
            $stmt->join(array('m_division', 'md'), 'INNER')
                ->on('t.division_code', '=', 'md.division_code')
                ->on('t.division_code', '=', \DB::expr("'".$conditions['division_code']."'"));
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->join(array('m_car_model', 'mcm'), 'INNER')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
                ->on('t.car_model_code', '=', \DB::expr("'".$conditions['car_model_code']."'"));
        }
        // 車番
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->join(array('m_car', 'mc'), 'INNER')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
                ->on('mc.car_code', '=', \DB::expr("'".$conditions['car_code']."'"));
        }
        
        // 配車番号
        if (!empty($conditions['dispatch_number'])) {
            $stmt->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 納品日
        $stmt->where('t.delivery_date', '!=', null);
        if (!empty($conditions['from_delivery_date']) && trim($conditions['to_delivery_date']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['from_delivery_date'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['to_delivery_date'])))->format('mysql_date');
            $stmt->where('t.delivery_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['from_delivery_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['from_delivery_date'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '>=', $date);
            }
            if (!empty($conditions['to_delivery_date'])) {
                $date = \Date::forge(strtotime(trim($conditions['to_delivery_date'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '<=', $date);
            }
        }
        // 納品先
        if (!empty($conditions['delivery_place']) && trim($conditions['delivery_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['delivery_place']."%'"));
        }
        // 得意先
        $stmt->where('t.client_code', '=', $client_code);
        // 庸車先
        if (!empty($conditions['carrier_code'])) {
            $stmt->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->where('t.car_model_code', '=', $conditions['car_model_code']);
        }
        // 車両コード
        if (!empty($conditions['car_code'])) {
            $stmt->where('t.car_code', '=', $conditions['car_code']);
        }
        $stmt->where('t.delete_flag', '=', '0');
        
        //グルーピング
        switch ($type) {
            case 'export':
                break;
            case 'search':
            case 'count':
            default:
                $stmt->group_by('t.division_code', 't.delivery_date', 't.delivery_place', 't.client_code', 't.carrier_code', 't.car_model_code', 't.car_code');
                break;
        }
        

        // 検索実行
        switch ($type) {
            case 'count':
                return $stmt->execute($db)->as_array();
                break;
            case 'export':
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.carrier_code', 'DESC')->order_by('t.car_code', 'DESC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.carrier_code', 'DESC')->order_by('t.car_code', 'DESC')
                    ->distinct(true)
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }
    
    /**
     * エクセル作成処理
     */
    public static function createExcel($delivery_slip_code, $select_dispatch_info) {
        //配車情報を分割
        $select_info_list = explode(",", $select_dispatch_info);
        $program_id = self::getProgramId($delivery_slip_code);
        
        //インプット情報リスト作成
        $input_list = array();
        foreach($select_info_list as $select_info) {
            $column = explode("@@", $select_info);
            $record = array(
                "client_code"       => $column[0],
                "division_code"     => $column[1],
                "division_name"     => $column[2],
                "delivery_date"     => $column[3],
                "delivery_place"    => $column[4],
                "carrier_code"      => $column[5],
                "car_model_code"    => $column[6],
                "car_code"          => $column[7]
            );
            $input_list[] = $record;
        }
        
        //帳票出力処理分岐
        switch ($program_id){
            case 'T1121':
                T1121::outputReport($input_list);
                break;
            case 'T1122':
                T1122::outputReport($input_list);
                break;
            case 'T1123':
                T1123::outputReport($input_list);
                break;
            case 'T1124':
                T1124::outputReport($input_list);
                break;
            default:
                return \Config::get('m_CE0001');
        }
    }
}