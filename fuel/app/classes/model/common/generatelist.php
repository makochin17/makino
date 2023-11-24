<?php
namespace Model\Common;
use \Model\Common\SystemConfig;

class GenerateList extends \Model {

    public static $db                   = 'MAKINO';

    /**
     * 予約管理権限設定(m_memberのuser_authority)
     * 1：システム管理者、2：管理職、3：業務管理者、4：一般
     */
    // 予約管理での使用ユーザー判定
    public static $schedule_authority   = array(1, 2, 3);

    /**
     * 保管場所列リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStorageColumnList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'storage_column_id'),
                array('m.name', 'storage_column_name')
                );

        // テーブル
        $stmt->from(array('m_storage_column', 'm'));
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $list = array();
        if ($all_flag) {
            // リストの先頭に"-"を追加
            $list = array(''=>"-");
            if ($all_flag === 'all') {
                // リストの先頭に"全て"を追加
                $list = array('0'=>"全て");
            }
        }

        foreach ($result as $item) {
            $list[$item['storage_column_id']] = $item['storage_column_name'];
        }

        return $list;
    }

    /**
     * 保管場所奥行リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStorageDepthList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'storage_depth_id'),
                array('m.name', 'storage_depth_name')
                );

        // テーブル
        $stmt->from(array('m_storage_depth', 'm'));
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $list = array();
        if ($all_flag) {
            // リストの先頭に"-"を追加
            $list = array(''=>"-");
            if ($all_flag === 'all') {
                // リストの先頭に"全て"を追加
                $list = array('0'=>"全て");
            }
        }

        foreach ($result as $item) {
            $list[$item['storage_depth_id']] = $item['storage_depth_name'];
        }

        return $list;
    }

    /**
     * 保管場所高さリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStorageHeightList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'storage_height_id'),
                array('m.name', 'storage_height_name')
                );

        // テーブル
        $stmt->from(array('m_storage_height', 'm'));
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $list = array();
        if ($all_flag) {
            // リストの先頭に"-"を追加
            $list = array(''=>"-");
            if ($all_flag === 'all') {
                // リストの先頭に"全て"を追加
                $list = array('0'=>"全て");
            }
        }

        foreach ($result as $item) {
            $list[$item['storage_height_id']] = $item['storage_height_name'];
        }

        return $list;
    }

    /**
     * 性別リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getSexList($all_flag) {

        $result = array('Man'=>"男性", 'Woman'=>"女性");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * お客様区分リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCustomerTypeList($all_flag) {

        $result = array('individual'=>"個人", 'corporation'=>"法人", 'dealer'=>"ディーラー");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * 退会フラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getResignFlgList($all_flag) {

        $result = array('NO'=>"-", 'YES'=>"退会");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * 会社マスタ取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCompanyList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select();

        // テーブル
        $stmt->from(array('m_company', 'm'));
        // 検索実行
        $result = $stmt->execute($db)->current();

        return $result;
    }

    /**
     * タイヤ種別リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getTireKindList($all_flag) {

        $result = array('summer_winter'=>"夏／冬", 'summer'=>"夏", 'winter'=>"冬");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * YES/NOフラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getYesnoFlgList($all_flag) {

        $result = array('NO'=>"なし", 'YES'=>"あり");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * 作業所要時間リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getWorkTimeList($all_flag) {

        $result = array('20'=>"20", '40'=>"40", '60'=>"60");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * 作業所要時間リスト取得(選択用)
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getSelectWorkTimeList($all_flag) {

        // $result = array('00'=>"00分", '20'=>"20分", '40'=>"40分");
        $result = array(
            '00'=>"00分",
            '10'=>"10分",
            '20'=>"20分",
            '30'=>"30分",
            '40'=>"40分",
            '50'=>"50分",
        );

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
        }

        return $result;
    }

    /**
     * 保管場所リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getLocationList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'location_id'),
                array(\DB::expr('CONCAT(
                    (SELECT name FROM m_storage_column WHERE id = m.storage_column_id),
                    " - ",
                    (SELECT name FROM m_storage_depth WHERE id = m.storage_depth_id),
                    " - ",
                    (SELECT name FROM m_storage_height WHERE id = m.storage_height_id)
                    )'), 'location')
                );

        // テーブル
        $stmt->from(array('rel_storage_location', 'm'));
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $list = array();
        if ($all_flag) {
            // リストの先頭に"-"を追加
            $list = array(''=>"-");
            if ($all_flag === 'all') {
                // リストの先頭に"全て"を追加
                $list = array('0'=>"全て");
            }
        }

        foreach ($result as $item) {
            $list[$item['location_id']] = $item['location'];
        }

        return $list;
    }

    /**
     * 会社情報リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCompanySelectList($db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'id'),
                array('m.company_name', 'company_name'),
                array('m.system_name', 'system_name'),
                array('m.start_time', 'start_time'),
                array('m.end_time', 'end_time'),
                array('m.span_min', 'span_min'),
                array('m.summer_tire_warning', 'summer_tire_warning'),
                array('m.summer_tire_caution', 'summer_tire_caution'),
                array('m.winter_tire_warning', 'winter_tire_warning'),
                array('m.winter_tire_caution', 'winter_tire_caution'),
                );

        // テーブル
        $stmt->from(array('m_company', 'm'));
        // 会社情報ID
        $stmt->where('m.id', '=', 1);
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $company_list = array();
        if (!empty($result)) {
            foreach ($result as $item) {
                $company_list = $item;
            }
        }

        return $company_list;
    }

    /**
     * ユニットリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getUnitList($all_flag, $schedule_type, $db) {

        // データ取得
        // 項目
        $stmt = \DB::select(
                array('m.id', 'unit_id'),
                array('m.name', 'unit_name')
                );

        // テーブル
        $stmt->from(array('m_unit', 'm'));

        if (!empty($schedule_type)) {
            $stmt->where('schedule_type', $schedule_type);
        }
        // ソート
        $stmt->order_by('m.id', 'ASC');
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $list = array();
        if ($all_flag) {
            // リストの先頭に"-"を追加
            $list = array(''=>"-");
            if ($all_flag === 'all') {
                // リストの先頭に"全て"を追加
                $list = array('0'=>"全て");
            }
        }

        foreach ($result as $item) {
            $list[$item['unit_id']] = $item['unit_name'];
        }

        return $list;
    }

    /**
     * 予約タイプリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getScheduleTypeList($all_flag) {

        $result = array('usually'=>"通常", 'delivery'=>"配達");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('all'=>"全て"), $result);
        }

        return $result;
    }



    /**
     * 依頼区分リスト取得
     */
    public static function getRequestClassList($all_flag) {

        $result = array(
                'delivery'      =>"配達",
                'pick_up'       =>"引取り",
                'extradition'   =>"引渡し",
                'business_trip' =>"出張",
                'shipping'      =>"発送",
                'inspection'    =>"点検",
            );
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }
        return $result;
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