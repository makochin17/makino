<?php
namespace Model\Mainte\M0020;
use \Model\Mainte\M0020\M0024;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;
use \Log;
use \Config;

class M0025 extends \Model {

    public static $db       = 'ONISHI';
    
    /**
     * 得意先マスタレコード取得
     */
    public static function getClient($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        // 項目
        $stmt = \DB::select(
                array('mc.client_code', 'client_code'),
                array('mc.client_company_code', 'client_company_code'),
                array('mcc.company_name', 'client_company_name'),
                array('mc.client_sales_office_code', 'client_sales_office_code'),
                array('mcs.sales_office_name', 'client_sales_office_name'),
                array('mc.client_department_code', 'client_department_code'),
                array('mcd.department_name', 'client_department_name'),
                array('mc.closing_date', 'closing_date'),
                array('mc.criterion_closing_date', 'criterion_closing_date'),
                array('mc.closing_date_1', 'closing_date_1'),
                array('mc.closing_date_2', 'closing_date_2'),
                array('mc.closing_date_3', 'closing_date_3'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'official_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.postal_code),"'.$encrypt_key.'")'), 'postal_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address),"'.$encrypt_key.'")'), 'address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address2),"'.$encrypt_key.'")'), 'address2'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_surname),"'.$encrypt_key.'")'), 'person_in_charge_surname'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_name),"'.$encrypt_key.'")'), 'person_in_charge_name'),
                array('mc.storage_fee', 'storage_fee'),
                array('mc.storage_in_charge', 'storage_in_charge'),
                array('mc.department_in_charge', 'department_in_charge'),
                array('mc.start_date', 'start_date')
                );

        // テーブル
        $stmt->from(array('m_client', 'mc'))
            ->join(array('m_client_company', 'mcc'), 'left outer')
                ->on('mc.client_company_code', '=', 'mcc.client_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_client_sales_office', 'mcs'), 'left outer')
                ->on('mc.client_sales_office_code', '=', 'mcs.client_sales_office_code')
                ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_client_department', 'mcd'), 'left outer')
                ->on('mc.client_department_code', '=', 'mcd.client_department_code')
                ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 得意先コード
        $stmt->where('mc.client_code', '=', $code);
        // 適用開始日
        $stmt->where('mc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先会社マスタレコード取得
     */
    public static function getClientCompany($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.client_company_code', 'client_company_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client_company', 'm'));
        
        // 得意先会社コード
        $stmt->where('m.client_company_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先営業所マスタレコード取得
     */
    public static function getClientSalesOffice($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.client_sales_office_code', 'client_sales_office_code'),
                array('m.client_company_code', 'client_company_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client_sales_office', 'm'));
        
        // 得意先会社コード
        $stmt->where('m.client_sales_office_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先部署マスタレコード取得
     */
    public static function getClientDepartment($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.client_department_code', 'client_department_code'),
                array('m.client_sales_office_code', 'client_sales_office_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client_department', 'm'));
        
        // 得意先会社コード
        $stmt->where('m.client_department_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先マスタレコード存在チェック（得意先会社コード指定）
     */
    public static function existsClientCompany($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先会社コード
        $stmt->where('m.client_company_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先マスタレコード存在チェック（得意先営業所コード指定）
     */
    public static function existsClientSalesOffice($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先営業所コード
        $stmt->where('m.client_sales_office_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先マスタレコード存在チェック（得意先部署コード指定）
     */
    public static function existsClientDepartment($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先部署コード
        $stmt->where('m.client_department_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先削除
     */
    public static function delete_record($client_code, $db) {
        
        //得意先マスタ情報取得
        $result = self::getClient($client_code, $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $client_data = $result[0];
        
        //得意先マスタ削除
        $result = self::delClient($client_code, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0008'))."[".$client_code."]");
            return str_replace('XXXXX','得意先',Config::get('m_ME0008'));
        }
        
        //得意先会社レコードが未使用なら削除
        $result = self::delClientCompany($client_data['client_company_code'], $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','得意先会社',Config::get('m_ME0008'))."[".$client_data['client_company_code']."]");
            return str_replace('XXXXX','得意先会社',Config::get('m_ME0008'));
        }
        
        if (!empty($client_data['client_sales_office_code'])) {
            //得意先営業所レコードが未使用なら削除
            $result = self::delClientSalesOffice($client_data['client_sales_office_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'))."[".$client_data['client_sales_office_code']."]");
                return str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'));
            }
        }
        
        if (!empty($client_data['client_department_code'])) {
            //得意先部署レコードが未使用なら削除
            $result = self::delClientDepartment($client_data['client_department_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0008'))."[".$client_data['client_department_code']."]");
                return str_replace('XXXXX','得意先部署',Config::get('m_ME0008'));
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '得意先マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        return null;
    }

    /**
     * 得意先更新
     */
    public static function update_record($conditions, $db) {
        
        //得意先マスタ情報取得
        $result = self::getClient($conditions['client_code'], $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $client_data = $result[0];
        
        ////////////////////////////////////////////
        //得意先マスタ更新
        
        // 取得レコードの「適用開始日」がシステム日付より過去日か
        if (strtotime($client_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
            // レコード削除（論理）
            $result = self::delClient($client_data['client_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0008'))."[".$client_data['client_code']."]");
                return str_replace('XXXXX','得意先',Config::get('m_ME0008'));
            }
            
            // 得意先マスタ登録情報作成
            $client_sales_office_code = $conditions['client_sales_office_code'];
            if (empty($client_sales_office_code) || $conditions['sales_office_radio'] == 3)$client_sales_office_code = \DB::expr('null');
            
            $client_department_code = $conditions['client_department_code'];
            if (empty($client_department_code) || $conditions['department_radio'] == 3)$client_department_code = \DB::expr('null');
            
            //担当部署の登録用情報取得
            $department_in_charge = M0020::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
            
            $data = array(
                'client_code'				=> $conditions['client_code'],
                'client_company_code'		=> $conditions['client_company_code'],
                'client_sales_office_code'	=> $client_sales_office_code,
                'client_department_code'	=> $client_department_code,
                'client_name_company'		=> $conditions['company_name'],
                'client_name_sales_office'	=> $conditions['company_name'].$conditions['sales_office_name'],
                'client_name'				=> $conditions['company_name'].$conditions['sales_office_name'].$conditions['department_name'],
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
                'storage_fee'               => $conditions['storage_fee'],
                'storage_in_charge'         => $conditions['storage_in_charge'],
                'department_in_charge'		=> $department_in_charge,
                );

            //得意先マスタ登録
            $result = M0024::addClient($data, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先',Config::get('m_ME0006'));
            }
        } else {
            //担当部署の登録用情報取得
            $conditions['department_in_charge'] = M0020::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
            
            //　レコード更新
            $result = self::updClient($conditions, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先',Config::get('m_ME0007'));
            }
        }
        
        $name_change_list = array('client_company_code' => '', 'client_sales_office_code' => '', 'client_department_code' => '');
        
        ////////////////////////////////////////////
        //得意先会社マスタ更新
        if ($conditions['company_radio'] == 2) {
            //名称変更の場合
            $result = self::getClientCompany($conditions['client_company_code'], $db);
            $client_company_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($client_company_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delClientCompany($conditions['client_company_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先会社',Config::get('m_ME0008'))."[".$client_data['client_company_code']."]");
                    return str_replace('XXXXX','得意先会社',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addClientCompany($conditions['client_company_code'], $conditions['company_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先会社',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先会社',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updClientCompany($conditions['client_company_code'], $conditions['company_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先会社',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先会社',Config::get('m_ME0007'));
                }
            }
            $name_change_list['client_company_code'] = $conditions['client_company_code'];
        }
        
        ////////////////////////////////////////////
        //得意先営業所マスタ更新
        if ($conditions['sales_office_radio'] == 2) {
            //名称変更の場合
            $result = self::getClientSalesOffice($conditions['client_sales_office_code'], $db);
            $client_sales_office_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($client_sales_office_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delClientSalesOffice($conditions['client_sales_office_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'))."[".$client_data['client_sales_office_code']."]");
                    return str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addClientSalesOffice($conditions['client_sales_office_code'], $client_sales_office_data['client_company_code'], $conditions['sales_office_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先営業所',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updClientSalesOffice($conditions['client_sales_office_code'], $conditions['sales_office_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先営業所',Config::get('m_ME0007'));
                }
            }
            $name_change_list['client_sales_office_code'] = $conditions['client_sales_office_code'];
        } elseif ($conditions['sales_office_radio'] == 3) {
            //得意先営業所レコードが未使用なら削除
            $result = self::delClientSalesOffice($conditions['client_sales_office_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'))."[".$conditions['client_sales_office_code']."]");
                return str_replace('XXXXX','得意先営業所',Config::get('m_ME0008'));
            }
        }
        
        ////////////////////////////////////////////
        //得意先部署マスタ更新
        if ($conditions['department_radio'] == 2) {
            //名称変更の場合
            $result = self::getClientDepartment($conditions['client_department_code'], $db);
            $client_department_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($client_department_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delClientDepartment($conditions['client_department_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0008'))."[".$client_data['client_department_code']."]");
                    return str_replace('XXXXX','得意先部署',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addClientDepartment($conditions['client_department_code'], $client_department_data['client_sales_office_code'], $conditions['department_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先部署',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updClientDepartment($conditions['client_department_code'], $conditions['department_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','得意先部署',Config::get('m_ME0007'));
                }
            }
            $name_change_list['client_department_code'] = $conditions['client_department_code'];
        } elseif ($conditions['department_radio'] == 3) {
            //得意先部署レコードが未使用なら削除
            $result = self::delClientDepartment($conditions['client_department_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先部署',Config::get('m_ME0008'))."[".$conditions['client_department_code']."]");
                return str_replace('XXXXX','得意先部署',Config::get('m_ME0008'));
            }
        }
        
        ////////////////////////////////////////////
        //得意先名称最新化
        $client_company_code = $name_change_list['client_company_code'];
        $client_sales_office_code = $name_change_list['client_sales_office_code'];
        $client_department_code = $name_change_list['client_department_code'];
        
        //名称変更があるかチェック
        if (!empty($client_company_code) || !empty($client_sales_office_code) || !empty($client_department_code)) {
            $client_list = array();
        
            //変更された会社を持つ得意先取得
            if (!empty($client_company_code)) {
                $result = self::existsClientCompany($client_company_code, $db);
                foreach ($result as $client) {
                    $client_list[] = $client['client_code'];
                }
            }

            //変更された営業所を持つ得意先取得
            if (!empty($client_sales_office_code)) {
                $result = self::existsClientSalesOffice($client_sales_office_code, $db);
                foreach ($result as $client) {
                    $client_list[] = $client['client_code'];
                }
            }

            //変更された部署を持つ得意先取得
            if (!empty($client_department_code)) {
                $result = self::existsClientDepartment($client_department_code, $db);
                foreach ($result as $client) {
                    $client_list[] = $client['client_code'];
                }
            }
            
            //得意先名称更新
            $result = self::updClientName($client_list, $conditions['client_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0007'))."[".print_r($client_list,true)."]");
                return str_replace('XXXXX','得意先',Config::get('m_ME0007'));
            }
            
            //営業所もしくは部署に削除があるかチェック
        } elseif ($conditions['sales_office_radio'] == 3 || $conditions['department_radio'] == 3) {
            //得意先名称更新
            $result = self::updClientName(array($conditions['client_code']), $conditions['client_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','得意先',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','得意先',Config::get('m_ME0007'));
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '得意先マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        return null;
    }
    
    /**
     * 得意先マスタ更新
     */
    public static function updClient($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        $client_sales_office_code = $items['client_sales_office_code'];
        if (empty($client_sales_office_code) || $items['sales_office_radio'] == 3)$client_sales_office_code = \DB::expr('null');
        
        $client_department_code = $items['client_department_code'];
        if (empty($client_department_code) || $items['department_radio'] == 3)$client_department_code = \DB::expr('null');
        
        // テーブル
        $stmt = \DB::update('m_client');
        
        // 項目セット
        $set = array(
            'client_code'				=> $items['client_code'],
            'client_company_code'		=> $items['client_company_code'],
            'client_sales_office_code'	=> $client_sales_office_code,
            'client_department_code'	=> $client_department_code,
            'client_name_company'		=> $items['company_name'],
            'client_name_sales_office'	=> $items['company_name'].$items['sales_office_name'],
            'client_name'				=> $items['company_name'].$items['sales_office_name'].$items['department_name'],
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
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車両コード
        $stmt->where('client_code', '=', $items['client_code']);
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
     * 得意先マスタ削除
     */
    public static function delClient($code, $db) {
        
        // テーブル
        $stmt = \DB::update('m_client');
        
        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 得意先コード
        $stmt->where('client_code', '=', $code);
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
     * 会社マスタ登録
     */
    public static function addClientCompany($client_company_code, $company_name, $db) {
        
        // 項目セット
        $set = array(
            'client_company_code' => $client_company_code,
            'company_name' => $company_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_client_company')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 会社マスタ更新
     */
    public static function updClientCompany($client_company_code, $company_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_client_company');
        
        // 項目セット
        $set = array('company_name' => $company_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 得意先会社コード
        $stmt->where('client_company_code', '=', $client_company_code);
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
     * 会社マスタ削除
     */
    public static function delClientCompany($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //得意先会社コードを持つ得意先マスタ存在チェック
            $result = self::existsClientCompany($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else {
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_client_company');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 得意先会社コード
            $stmt->where('client_company_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 営業所マスタ登録
     */
    public static function addClientSalesOffice($client_sales_office_code, $client_company_code, $sales_office_name, $db) {
        
        // 項目セット
        $set = array(
            'client_sales_office_code' => $client_sales_office_code,
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
            return true;
        }
        return false;
    }
    
    /**
     * 営業所マスタ更新
     */
    public static function updClientSalesOffice($client_sales_office_code, $sales_office_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_client_sales_office');
        
        // 項目セット
        $set = array('sales_office_name' => $sales_office_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 得意先営業所コード
        $stmt->where('client_sales_office_code', '=', $client_sales_office_code);
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
     * 営業所マスタ削除
     */
    public static function delClientSalesOffice($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //得意先営業所コードを持つ得意先マスタ存在チェック
            $result = self::existsClientSalesOffice($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else { 
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_client_sales_office');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 得意先営業所コード
            $stmt->where('client_sales_office_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 部署マスタ登録
     */
    public static function addClientDepartment($client_department_code, $client_sales_office_code, $department_name, $db) {
        
        // 項目セット
        $set = array(
            'client_department_code' => $client_department_code,
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
            return true;
        }
        return false;
    }
    
    /**
     * 部署マスタ更新
     */
    public static function updClientDepartment($client_department_code, $department_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_client_department');
        
        // 項目セット
        $set = array('department_name' => $department_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 得意先部署コード
        $stmt->where('client_department_code', '=', $client_department_code);
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
     * 部署マスタ削除
     */
    public static function delClientDepartment($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //得意先部署コードを持つ得意先マスタ存在チェック
            $result = self::existsClientDepartment($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else {
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_client_department');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 得意先部署コード
            $stmt->where('client_department_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 得意先名称最新化
     */
    public static function updClientName($input_list, $no_check_code, $db) {
        //重複値削除
        $client_list = array_unique($input_list);
        
        foreach ($client_list as $client_code) {
            //得意先マスタ情報取得
            $result = self::getClient($client_code, $db);
            $client_data = $result[0];
            
            // テーブル
            $stmt = \DB::update('m_client');

            // 項目セット
            $set = array(
                'client_name_company' => $client_data['client_company_name'],
                'client_name_sales_office' => $client_data['client_company_name'].$client_data['client_sales_office_name'],
                'client_name' => $client_data['client_company_name'].$client_data['client_sales_office_name'].$client_data['client_department_name']
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 得意先コード
            $stmt->where('client_code', '=', $client_code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0 && $no_check_code != $client_code) {
                return false;
            }
        }
        return true;
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