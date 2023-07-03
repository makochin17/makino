<?php
namespace Model\Mainte\M0030;
use \Model\Mainte\M0030\M0030;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Date;
use \Log;
use \Config;

class M0034 extends \Model {

    public static $db       = 'ONISHI';
    
    /**
     * 庸車先登録
     */
    public static function create_record($conditions, $db) {
        
        //庸車先存在チェック
        if ($conditions['carrier_radio'] == '2') {
            $result = M0030::getCarrier($conditions['carrier_code'], $db);
            if (is_countable($result)){
                if (count($result) == 1) {
                    return Config::get('m_MW0004');
                }
            }
        }
        
        //会社マスタ登録
        $carrier_company_code = '';
        if ($conditions['company_radio'] == 1) {
            //新規登録の場合
            $carrier_company_code = self::addCarrierCompany($conditions['carrier_company_name'], $db);
            if (is_null($carrier_company_code)) {
                Log::error(str_replace('XXXXX','庸車先会社',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先会社',Config::get('m_ME0006'));
            }
        } else {
            //既存の場合
            $carrier_company_code = $conditions['carrier_company_code'];
        }
        
        //営業所マスタ登録
        $carrier_sales_office_code = '';
        if ($conditions['sales_office_radio'] == 1) {
            //新規登録の場合
            $carrier_sales_office_code = self::addCarrierSalesOffice($carrier_company_code, $conditions['carrier_sales_office_name'], $db);
            if (is_null($carrier_sales_office_code)) {
                Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0006'));
            }
        } elseif ($conditions['sales_office_radio'] == 2) {
            //既存の場合
            $carrier_sales_office_code = $conditions['carrier_sales_office_code'];
        }
        
        //部署マスタ登録
        $carrier_department_code = '';
        if ($conditions['department_radio'] == 1) {
            //新規登録の場合
            $carrier_department_code = self::addCarrierDepartment($carrier_sales_office_code, $conditions['carrier_department_name'], $db);
            if (is_null($carrier_department_code)) {
                Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先部署',Config::get('m_ME0006'));
            }
        }
        
        //庸車先マスタ登録情報作成
        $carrier_code = $conditions['carrier_code'];
        if (empty($carrier_code))$carrier_code = \DB::expr('null');
        
        //担当部署の登録用情報取得
        $department_in_charge = M0030::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
        
        $data = array(
            'carrier_code'				=> $carrier_code,
            'carrier_company_code'		=> $carrier_company_code,
            'carrier_sales_office_code'	=> $carrier_sales_office_code,
            'carrier_department_code'	=> $carrier_department_code,
            'carrier_name_company'		=> $conditions['carrier_company_name'],
            'carrier_name_sales_office'	=> $conditions['carrier_company_name'].$conditions['carrier_sales_office_name'],
            'carrier_name'				=> $conditions['carrier_company_name'].$conditions['carrier_sales_office_name'].$conditions['carrier_department_name'],
            'closing_date'				=> $conditions['closing_date'],
            'closing_date_1'			=> $conditions['closing_date_1'],
            'closing_date_2'			=> $conditions['closing_date_2'],
            'closing_date_3'			=> $conditions['closing_date_3'],
            'company_section'			=> $conditions['company_section'],
            'criterion_closing_date'	=> $conditions['criterion_closing_date'],
            'official_name'				=> $conditions['official_name'],
            'official_name_kana'		=> $conditions['official_name_kana'],
            'postal_code'				=> $conditions['postal_code'],
            'address'					=> $conditions['address'],
            'address2'					=> $conditions['address2'],
            'phone_number'				=> $conditions['phone_number'],
            'fax_number'				=> $conditions['fax_number'],
            'person_in_charge_surname'	=> $conditions['person_in_charge_surname'],
            'person_in_charge_name'		=> $conditions['person_in_charge_name'],
            'department_in_charge'		=> $department_in_charge,
            );
        
        //庸車先マスタ登録
        $result = self::addCarrier($data, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
            return str_replace('XXXXX','庸車先',Config::get('m_ME0006'));
        }
        
        return null;
    }

    /**
     * 会社マスタ登録
     */
    public static function addCarrierCompany($company_name, $db) {
        
        // 項目セット
        $set = array(
            'company_name' => $company_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_company')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            //インサートした庸車先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_carrier_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 営業所マスタ登録
     */
    public static function addCarrierSalesOffice($carrier_company_code, $sales_office_name, $db) {
        
        // 項目セット
        $set = array(
            'carrier_company_code' => $carrier_company_code,
            'sales_office_name' => $sales_office_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_sales_office')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            //インサートした庸車先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_carrier_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 部署マスタ登録
     */
    public static function addCarrierDepartment($carrier_sales_office_code, $department_name, $db) {
        
        // 項目セット
        $set = array(
            'carrier_sales_office_code' => $carrier_sales_office_code,
            'department_name' => $department_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_department')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            //インサートした庸車先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_carrier_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 庸車先マスタ登録
     */
    public static function addCarrier($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        $carrier_sales_office_code = $items['carrier_sales_office_code'];
        if (empty($carrier_sales_office_code))$carrier_sales_office_code = \DB::expr('null');
        
        $carrier_department_code = $items['carrier_department_code'];
        if (empty($carrier_department_code))$carrier_department_code = \DB::expr('null');
        
        // 項目セット
        $set = array(
            'carrier_code'				=> $items['carrier_code'],
            'carrier_company_code'		=> $items['carrier_company_code'],
            'carrier_sales_office_code'	=> $carrier_sales_office_code,
            'carrier_department_code'	=> $carrier_department_code,
            'carrier_name_company'		=> $items['carrier_name_company'],
            'carrier_name_sales_office'	=> $items['carrier_name_sales_office'],
            'carrier_name'				=> $items['carrier_name'],
            'closing_date'				=> $items['closing_date'],
            'company_section'			=> $items['company_section'],
            'criterion_closing_date'	=> $items['criterion_closing_date'],
            'closing_date_1'			=> !empty($items['closing_date_1']) ? $items['closing_date_1'] : null,
            'closing_date_2'			=> !empty($items['closing_date_2']) ? $items['closing_date_2'] : null,
            'closing_date_3'			=> !empty($items['closing_date_3']) ? $items['closing_date_3'] : null,
            'official_name'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['official_name'].'","'.$encrypt_key.'"))'),
            'official_name_kana'		=> \DB::expr('HEX(AES_ENCRYPT("'.$items['official_name_kana'].'","'.$encrypt_key.'"))'),
            'postal_code'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['postal_code'].'","'.$encrypt_key.'"))'),
            'address'					=> \DB::expr('HEX(AES_ENCRYPT("'.$items['address'].'","'.$encrypt_key.'"))'),
            'address2'					=> \DB::expr('HEX(AES_ENCRYPT("'.$items['address2'].'","'.$encrypt_key.'"))'),
            'phone_number'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['phone_number'].'","'.$encrypt_key.'"))'),
            'fax_number'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['fax_number'].'","'.$encrypt_key.'"))'),
            'person_in_charge_surname'	=> \DB::expr('HEX(AES_ENCRYPT("'.$items['person_in_charge_surname'].'","'.$encrypt_key.'"))'),
            'person_in_charge_name'		=> \DB::expr('HEX(AES_ENCRYPT("'.$items['person_in_charge_name'].'","'.$encrypt_key.'"))'),
            'department_in_charge'	    => $items['department_in_charge'],
            'start_date'                => Date::forge()->format('mysql_date'),
            'end_date'                  => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'carrier_code = VALUES(carrier_code),'
                . 'carrier_company_code = VALUES(carrier_company_code),'
                . 'carrier_sales_office_code = VALUES(carrier_sales_office_code),'
                . 'carrier_department_code = VALUES(carrier_department_code),'
                . 'carrier_name_company = VALUES(carrier_name_company),'
                . 'carrier_name_sales_office = VALUES(carrier_name_sales_office),'
                . 'carrier_name = VALUES(carrier_name),'
                . 'closing_date = VALUES(closing_date),'
                . 'company_section = VALUES(company_section),'
                . 'criterion_closing_date = VALUES(criterion_closing_date),'
                . 'closing_date_1 = VALUES(closing_date_1),'
                . 'closing_date_2 = VALUES(closing_date_2),'
                . 'closing_date_3 = VALUES(closing_date_3),'
                . 'official_name = VALUES(official_name),'
                . 'official_name_kana = VALUES(official_name_kana),'
                . 'postal_code = VALUES(postal_code),'
                . 'address = VALUES(address),'
                . 'address2 = VALUES(address2),'
                . 'phone_number = VALUES(phone_number),'
                . 'fax_number = VALUES(fax_number),'
                . 'person_in_charge_surname = VALUES(person_in_charge_surname),'
                . 'person_in_charge_name = VALUES(person_in_charge_name),'
                . 'department_in_charge = VALUES(department_in_charge),'
                . 'start_date = VALUES(start_date),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        
        // 登録実行
        $stmt = \DB::insert('m_carrier')->set($set);
        $result = \DB::query($stmt->compile() . $duplicate_key_update)->execute();
        if($result[1] > 0) {
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
    
    //最新+1の庸車先コード取得
    public static function getCarrierCode($db) {
        //項目
        $stmt = \DB::select(array(\DB::expr('MAX(m.carrier_code) + 1'), 'carrier_code'));
        
        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 得意先コード
        $stmt->where('m.carrier_code', '<', '99988');
        
        // 検索実行
        $client_code = $stmt->execute($db)->as_array();
        
        return $client_code[0]['carrier_code'];
        
    }
}