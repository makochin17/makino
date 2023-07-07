<?php
namespace Model\Mainte\M0020;
use \Model\Mainte\M0020\M0020;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Date;
use \Log;
use \Config;

class M0024 extends \Model {

    public static $db       = 'MAKINO';
    
    /**
     * 得意先登録
     */
    public static function create_record($conditions, $db) {
        
        //得意先存在チェック
        if ($conditions['client_radio'] == '2') {
            $result = M0020::getClient($conditions['client_code'], $db);
            if (is_countable($result)){
                if (count($result) == 1) {
                    return Config::get('m_MW0004');
                }
            }
        }
        
        //会社マスタ登録
        $client_company_code = '';
        if ($conditions['company_radio'] == 1) {
            //新規登録の場合
            $client_company_code = self::addClientCompany($conditions['client_company_name'], $db);
            if (is_null($client_company_code)) {
                Log::error(str_replace('XXXXX','得意先会社',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先会社',Config::get('m_ME0006'));
            }
        } else {
            //既存の場合
            $client_company_code = $conditions['client_company_code'];
        }
        
        //営業所マスタ登録
        $client_sales_office_code = '';
        if ($conditions['sales_office_radio'] == 1) {
            //新規登録の場合
            $client_sales_office_code = self::addClientSalesOffice($client_company_code, $conditions['client_sales_office_name'], $db);
            if (is_null($client_sales_office_code)) {
                Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先営業所',Config::get('m_ME0006'));
            }
        } elseif ($conditions['sales_office_radio'] == 2) {
            //既存の場合
            $client_sales_office_code = $conditions['client_sales_office_code'];
        }
        
        //部署マスタ登録
        $client_department_code = '';
        if ($conditions['department_radio'] == 1) {
            //新規登録の場合
            $client_department_code = self::addClientDepartment($client_sales_office_code, $conditions['client_department_name'], $db);
            if (is_null($client_department_code)) {
                Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先部署',Config::get('m_ME0006'));
            }
        }
        
        //得意先マスタ登録情報作成
        $client_code = $conditions['client_code'];
        if (empty($client_code))$client_code = \DB::expr('null');
        
        //担当部署の登録用情報取得
        $department_in_charge = M0020::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
        
        $data = array(
            'client_code'				=> $client_code,
            'client_company_code'		=> $client_company_code,
            'client_sales_office_code'	=> $client_sales_office_code,
            'client_department_code'	=> $client_department_code,
            'client_name_company'		=> $conditions['client_company_name'],
            'client_name_sales_office'	=> $conditions['client_company_name'].$conditions['client_sales_office_name'],
            'client_name'				=> $conditions['client_company_name'].$conditions['client_sales_office_name'].$conditions['client_department_name'],
            'closing_date'				=> $conditions['closing_date'],
            'criterion_closing_date'	=> $conditions['criterion_closing_date'],
            'closing_date_1'			=> $conditions['closing_date_1'],
            'closing_date_2'			=> $conditions['closing_date_2'],
            'closing_date_3'			=> $conditions['closing_date_3'],
            'official_name'				=> $conditions['official_name'],
            'official_name_kana'		=> $conditions['official_name_kana'],
            'postal_code'				=> $conditions['postal_code'],
            'address'					=> $conditions['address'],
            'address2'					=> $conditions['address2'],
            'phone_number'				=> $conditions['phone_number'],
            'fax_number'				=> $conditions['fax_number'],
            'person_in_charge_surname'	=> $conditions['person_in_charge_surname'],
            'person_in_charge_name'		=> $conditions['person_in_charge_name'],
            'storage_fee'				=> $conditions['storage_fee'],
            'storage_in_charge'			=> $conditions['storage_in_charge'],
            'department_in_charge'		=> $department_in_charge,
            );
        
        //得意先マスタ登録
        $result = self::addClient($data, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
            return str_replace('XXXXX','得意先',Config::get('m_ME0006'));
        }
        
        return null;
    }

    /**
     * 会社マスタ登録
     */
    public static function addClientCompany($company_name, $db) {
        
        // 項目セット
        $set = array(
            'company_name' => $company_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_client_company')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            //インサートした得意先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_client_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 営業所マスタ登録
     */
    public static function addClientSalesOffice($client_company_code, $sales_office_name, $db) {
        
        // 項目セット
        $set = array(
            'client_company_code' => $client_company_code,
            'sales_office_name' => $sales_office_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_client_sales_office')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            //インサートした得意先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_client_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 部署マスタ登録
     */
    public static function addClientDepartment($client_sales_office_code, $department_name, $db) {
        
        // 項目セット
        $set = array(
            'client_sales_office_code' => $client_sales_office_code,
            'department_name' => $department_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_client_department')->set($set);
        $result = $stmt->execute($db);
        if($result[1] > 0) {
            //インサートした得意先会社コードを取得
            $stmt = \DB::select(array(\DB::expr('LAST_INSERT_ID()'), 'insert_id'));
            $stmt->from(array('m_client_company', 'm'));
            $result = $stmt->execute($db)->as_array();
            $insert_id = $result[0]['insert_id'];
            
            return $insert_id;
        }
        return null;
    }
    
    /**
     * 得意先マスタ登録
     */
    public static function addClient($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        $client_sales_office_code = $items['client_sales_office_code'];
        if (empty($client_sales_office_code))$client_sales_office_code = \DB::expr('null');
        
        $client_department_code = $items['client_department_code'];
        if (empty($client_department_code))$client_department_code = \DB::expr('null');
        
        // 項目セット
        $set = array(
            'client_code'				=> $items['client_code'],
            'client_company_code'		=> $items['client_company_code'],
            'client_sales_office_code'	=> $client_sales_office_code,
            'client_department_code'	=> $client_department_code,
            'client_name_company'		=> $items['client_name_company'],
            'client_name_sales_office'	=> $items['client_name_sales_office'],
            'client_name'				=> $items['client_name'],
            'closing_date'				=> $items['closing_date'],
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
            'storage_fee'               => !empty($items['storage_fee']) ? $items['storage_fee'] : 0,
            'storage_in_charge'         => $items['storage_in_charge'],
            'department_in_charge'	    => $items['department_in_charge'],
            'start_date'                => Date::forge()->format('mysql_date'),
            'end_date'                  => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // ON DUPLICATE KEY UPDATE用の更新項目セット
        $duplicate_key_update = 'ON DUPLICATE KEY UPDATE '
                . 'client_code = VALUES(client_code),'
                . 'client_company_code = VALUES(client_company_code),'
                . 'client_sales_office_code = VALUES(client_sales_office_code),'
                . 'client_department_code = VALUES(client_department_code),'
                . 'client_name_company = VALUES(client_name_company),'
                . 'client_name_sales_office = VALUES(client_name_sales_office),'
                . 'client_name = VALUES(client_name),'
                . 'closing_date = VALUES(closing_date),'
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
                . 'storage_fee = VALUES(storage_fee),'
                . 'storage_in_charge = VALUES(storage_in_charge),'
                . 'department_in_charge = VALUES(department_in_charge),'
                . 'start_date = VALUES(start_date),'
                . 'end_date = VALUES(end_date),'
                . 'create_datetime = VALUES(create_datetime),'
                . 'create_user = VALUES(create_user),'
                . 'update_datetime = VALUES(update_datetime),'
                . 'update_user = VALUES(update_user)';
        
        // 登録実行
        $stmt = \DB::insert('m_client')->set($set);
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
    
    //最新+1の得意先コード取得
    public static function getClientCode($db) {
        //項目
        $stmt = \DB::select(array(\DB::expr('MAX(m.client_code) + 1'), 'client_code'));
        
        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先コード（庸車先の採番開始値である50000未満の範囲で自動採番）
        $stmt->where('m.client_code', '<', '50000');
        
        // 検索実行
        $client_code = $stmt->execute($db)->as_array();
        
        return $client_code[0]['client_code'];
        
    }
}