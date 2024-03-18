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
     * 保管場所倉庫リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getStorageWarehouseList($all_flag, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.id', 'storage_warehouse_id'),
                array('m.name', 'storage_warehouse_name')
                );

        // テーブル
        $stmt->from(array('m_storage_warehouse', 'm'));
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
            $list[$item['storage_warehouse_id']] = $item['storage_warehouse_name'];
        }

        return $list;
    }

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
                    (SELECT name FROM m_storage_warehouse WHERE id = m.storage_warehouse_id),
                    " - ",
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
    public static function getUnitList($all_flag, $schedule_type, $flg, $db) {

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
        if ($flg === false) {
            $stmt->where('disp_flg', 'NO');
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
     * タイヤ種別リスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getTireKubunList($all_flag) {

        $result = array('summer'=>"夏", 'winter'=>"冬");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * 入庫フラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getReceiptFlgList($all_flag) {

        $result = array('YES'=>"入庫済", 'NO'=>"未入庫");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * 出庫フラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDeliveryFlgList($all_flag) {

        $result = array('YES'=>"出庫済", 'NO'=>"未出庫");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * 出庫指示フラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDeliveryScheduleFlgList($all_flag) {

        $result = array('YES'=>"指示済", 'NO'=>"未指示");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * 完了フラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getCompleteFlgList($all_flag) {

        $result = array('YES'=>"完了済", 'NO'=>"未完了");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array('0'=>"全て"), $result);
        }

        return $result;
    }

    /**
     * ユーザー権限取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getUserAuthority($all_flag, $db) {

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // データ取得
        $stmt = \DB::select(
                array('m.user_authority', 'user_authority'),
                array(\DB::expr("
                        CASE
                            WHEN m.user_authority = 1 THEN 'システム管理者'
                            WHEN m.user_authority = 2 THEN '管理職'
                            WHEN m.user_authority = 3 THEN '業務管理者'
                            WHEN m.user_authority = 4 THEN '一般'
                        END
                    "), 'authority')
                );

        // テーブル
        $stmt->from(array('m_member', 'm'));
        // ログインユーザ名
        $stmt->where('m.user_id', '!=', null);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        // 検索実行
        $result = $stmt->execute($db)->as_array();

        $user_authority_list = array();
        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $user_authority_list = array(''=>"全て");
        }

        foreach ($result as $item) {
            if (!empty($item['user_authority'])) {
                $user_authority_list[$item['user_authority']] = $item['authority'];
            }
        }

        return $user_authority_list;
    }

    /**
     * YES/NOフラグリスト取得
     * $all_flag リストに"全て"を含めるフラグ（trueで含める）
     */
    public static function getDispFlgList($all_flag) {

        $result = array('NO'=>"表示", 'YES'=>"非表示");

        if ($all_flag) {
            // リストの先頭に"全て"を追加
            $result = array_merge(array(''=>"-"), $result);
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

}