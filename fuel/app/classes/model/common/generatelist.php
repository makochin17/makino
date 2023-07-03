<?php
namespace Model\Common;
use \Model\Common\SystemConfig;

class GenerateList extends \Model {

    public static $db       = 'ONISHI';

    /**
     * 支社リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getBranchOfficeList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.branch_office_code', 'branch_office_code'),
                array('m.branch_office_name', 'branch_office_name')
                );

        // テーブル
        $stmt->from(array('m_branch_office', 'm'));
        
        // ソート
        $stmt->order_by('m.branch_office_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $branch_office_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $branch_office_list = array('0'=>"全て");
        }
        
        foreach ($result as $item) {
            $branch_office_list[$item['branch_office_code']] = $item['branch_office_name'];
        }
        
        return $branch_office_list;
    }
    
    /**
     * 課リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDivisionList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.division_code', 'division_code'),
                array('m.division_name', 'division_name')
                );

        // テーブル
        $stmt->from(array('m_division', 'm'));
        
        // ソート
        $stmt->order_by('m.sort', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $division_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $division_list = array('000'=>"全て");
        }
        
        foreach ($result as $item) {
            $division_list[$item['division_code']] = $item['division_name'];
        }
        
        return $division_list;
    }
    
    /**
     * 役職リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getPositionList($all_flag, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // データ取得
        $stmt = \DB::select(
                array('m.position_code', 'position_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.position_name),"'.$encrypt_key.'")'), 'position_name')
                );

        // テーブル
        $stmt->from(array('m_position', 'm'));
        
        // ソート
        $stmt->order_by(\DB::expr('AES_DECRYPT(UNHEX(m.position_name),"'.$encrypt_key.'")'), 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $position_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $position_list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $position_list[$item['position_code']] = $item['position_name'];
        }
        
        return $position_list;
    }
    
    /**
     * 車種リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCarModelList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.car_model_code', 'car_model_code'),
                array('m.car_model_name', 'car_model_name')
                );

        // テーブル
        $stmt->from(array('m_car_model', 'm'));
        
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // ソート
        $stmt->order_by('m.sort', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $car_model_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $car_model_list = array('000'=>"全て");
        }
        
        foreach ($result as $item) {
            $car_model_list[$item['car_model_code']] = $item['car_model_name'];
        }
        
        return $car_model_list;
    }
    
    /**
     * 商品リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getProductList($all_flag, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // データ取得
        $stmt = \DB::select(
                array('m.product_code', 'product_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.product_name),"'.$encrypt_key.'")'), 'product_name')
                );

        // テーブル
        $stmt->from(array('m_product', 'm'));
        
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // ソート
        $stmt->order_by('m.sort', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $product_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $product_list = array('0000'=>"全て");
        }
        
        foreach ($result as $item) {
            $product_list[$item['product_code']] = $item['product_name'];
        }
        
        return $product_list;
    }
    
    /**
     * 商品分類リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getProductCategoryList($all_flag) {
        \Config::load('productcategory');
        $list = \Config::get('category');
        asort($list);
        
        if ($all_flag) {
            $list = array_merge(array('0'=>"全て"), $list);
        }
        
        return $list;
    }
    
    /**
     * 売上区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getSalesCategoryList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.sales_category_code', 'sales_category_code'),
                array('m.sales_category_name', 'sales_category_name'),
                );

        // テーブル
        $stmt->from(array('m_sales_category', 'm'));
        // ソート
        $stmt->order_by('m.sales_category_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $sales_category_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $sales_category_list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $sales_category_list[$item['sales_category_code']] = $item['sales_category_name'];
        }
        
        return $sales_category_list;
    }
    
    /**
     * 処理区分リスト取得
     */
    public static function getProcessingDivisionList() {
        return array('1'=>"新規", '2'=>"更新", '3'=>"削除");
    }
    
    /**
     * 会社区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCompanySectionList($all_flag) {

        $result = array('1'=>"自社", '2'=>"他社");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        
        return $result;
    }
    
    /**
     * 売上ステータスリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     * $mode 表記の種類（1:記号　2:日本語）
     */
    public static function getSalesStatusList($all_flag, $mode = 1) {

        $result = array();
        if ($mode == 1){
            $result = array('1'=>"×", '2'=>"〇");
        } elseif ($mode == 2) {
            $result = array('1'=>"未確定", '2'=>"確定");
        }
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        
        return $result;
    }
    
    /**
     * 配送区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDeliveryCategoryList($all_flag) {

        $result = array('1'=>"ローカル", '2'=>"長距離");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        
        return $result;
    }
    
    /**
     * 配送区分リスト取得（共配便）
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getShareDeliveryCategoryList($all_flag) {
        \Config::load('deliverycategory');
        $list = \Config::get('delivery_category');
        //asort($list);
        
        if ($all_flag) {
            $list = array_merge(array('0'=>"全て"), $list);
        }
        
        return $list;
    }
    
    /**
     * 配車区分リスト取得（共配便）
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDispatchCategoryList($all_flag) {
        \Config::load('dispatchcategory');
        $list = \Config::get('dispatch_category');
        //asort($list);
        
        if ($all_flag) {
            $list = array_merge(array('0'=>"全て"), $list);
        }
        
        return $list;
    }
    
    /**
     * 入出庫区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStockChangeCategoryList($all_flag, $db) {
        // データ取得
        $stmt = \DB::select(
                array('m.stock_change_code', 'stock_change_code'),
                array('m.stock_change_name', 'stock_change_name'),
                );

        // テーブル
        $stmt->from(array('m_stock_change', 'm'));
        // ソート
        $stmt->order_by('m.stock_change_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $list[$item['stock_change_code']] = $item['stock_change_name'];
        }
        
        return $list;
    }
    
    /**
     * 保管料区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStorageFeeCategoryList($all_flag, $db) {
        // データ取得
        $stmt = \DB::select(
                array('m.storage_fee_code', 'storage_fee_code'),
                array('m.storage_fee_name', 'storage_fee_name'),
                );

        // テーブル
        $stmt->from(array('m_storage_fee', 'm'));
        // ソート
        $stmt->order_by('m.storage_fee_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $list[$item['storage_fee_code']] = $item['storage_fee_name'];
        }
        
        return $list;
    }
    
    /**
     * 地区リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getAreaList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.area_code', 'area_code'),
                array('m.area_name', 'area_name'),
                );

        // テーブル
        $stmt->from(array('m_area', 'm'));
        // ソート
        $stmt->order_by('m.area_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $area_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $area_list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $area_list[$item['area_code']] = $item['area_name'];
        }
        
        return $area_list;
    }
    
    /**
     * 単位リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getUnitList($all_flag, $db) {
        
        // データ取得
        $stmt = \DB::select(
                array('m.unit_code', 'unit_code'),
                array('m.unit_name', 'unit_name'),
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));
        // ソート
        $stmt->order_by('m.unit_code', 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $area_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $area_list = array('00'=>"全て");
        }
        
        foreach ($result as $item) {
            $area_list[$item['unit_code']] = $item['unit_name'];
        }
        
        return $area_list;
    }
    
    /**
     * 締日リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getClosingDateList($all_flag) {

        $result = array();
        
        // 1～28のリスト生成
        for($i = 1; $i < 29; $i++){
            $result += array($i => $i);
        }
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        
        $result += array(99=>"月末");
        $result += array(50=>"都度");
        $result += array(51=>"月2回");
        $result += array(52=>"月3回");
        
        return $result;
    }
    
    /**
     * 締日リスト取得
     * $all_flag リストに" "を含めるフラグ（trueで含める）
     */
    public static function getClosingDateList2($all_flag) {

        $result = array();
        
        // 1～28のリスト生成
        for($i = 1; $i < 29; $i++){
            $result += array($i => $i);
        }
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>" "), $result);
        }
        
        $result += array(99=>"月末");
        
        return $result;
    }
    
    /**
     * 締日区分リスト取得
     */
    public static function getClosingCategoryList() {

        $result = array();
        
        $result += array(1=>"月1回");
        $result += array(2=>"月2回");
        $result += array(3=>"月3回");
        $result += array(4=>"都度");
        
        return $result;
    }

    /**
     * 税区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getTaxCategoryList($all_flag) {

        $result = array('1'=>"課税", '2'=>"非課税");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        
        return $result;
    }
    
    /**
     * 集計項目リスト取得
     */
    public static function getAggregationItemList() {
        return array('1'=>"請求売上", '2'=>"庸車費用", '3'=>"差益", '4'=>"差益率");
    }

    /**
     * 集計単位日付リスト取得
     */
    public static function getAggregationUnitDateList() {
        return array('1'=>"日単位", '2'=>"月単位", '3'=>"年単位");
    }
    
    /**
     * 集計単位会社リスト取得
     */
    public static function getAggregationUnitCompanyList() {
        return array('1'=>"会社単位", '2'=>"営業所単位", '3'=>"部署単位");
    }
    
    /**
     * 年リスト取得
     */
    public static function getYearList() {
        $result = array();
        
        for ($i=2021; $i <= 2050; $i++) {
            $result += array($i=>$i);
        }
        
        return $result;
    }
    
    /**
     * 月リスト取得
     */
    public static function getMonthList() {
        $result = array();
        
        for ($i=1; $i <= 12; $i++) {
            $result += array($i=>$i);
        }
        
        return $result;
    }
    
    /**
     * 日リスト取得
     */
    public static function getDayList() {
        $result = array();
        
        for ($i=1; $i <= 31; $i++) {
            $result += array($i=>$i);
        }
        
        return $result;
    }
    
    /**
     * 登録者リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCreateUserList($all_flag, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // データ取得
        $stmt = \DB::select(
                array('m.user_id', 'user_id'),
                array(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'name')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));
        
        // ログインユーザ名
        $stmt->where('m.user_id', '!=', null);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // ソート
        $stmt->order_by(\DB::expr('AES_DECRYPT(UNHEX(m.name),"'.$encrypt_key.'")'), 'ASC');
        
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        
        $create_user_list = array();
        
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $create_user_list = array(''=>"全て");
        }
        
        foreach ($result as $item) {
            if (!empty($item['user_id'])) {
                $create_user_list[$item['user_id']] = $item['name'];
            }
        }
        
        return $create_user_list;
    }

    /**
     * 共配便配車情報雛形リスト取得
     */
    public static function getDispatchShareOrgFileList() {
        return array('1'=>"共通", '3'=>"家具", '4'=>"建材", '5'=>"電材");
    }

    /**
     * 端数処理区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getRoundingList($all_flag) {
        \Config::load('rounding');
        $list = \Config::get('rounding');
        //asort($list);
        
        if ($all_flag) {
            $list = array_merge(array('0'=>"全て"), $list);
        }
        
        return $list;
    }
    
    /**
     * 請求帳票種別リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getBillReportList($all_flag) {
        \Config::load('billreportcategory');
        $list = \Config::get('billreportcategory');
        //asort($list);
        
        if ($all_flag) {
            $list = array_merge(array('0'=>"全て"), $list);
        }
        
        return $list;
    }

}