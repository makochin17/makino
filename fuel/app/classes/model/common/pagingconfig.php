<?php
namespace Model\Common;

class PagingConfig extends \Model {

    public static $db       = 'ONISHI';

    /**
     * ページング設定値取得
     * $function_id 機能ID
     */
    public static function getPagingConfig($function_id, $db) {

        // データ取得
        $stmt = \DB::select(
                array('m.display_link_number', 'display_link_number'),
                array('m.display_record_number', 'display_record_number')
                );

        // 条件
        $stmt->from(array('m_paging_config', 'm'));
        // 機能ID
        $stmt->where('m.function_id', '=', $function_id);
        // 検索実行
        $result = $stmt->execute($db)->as_array();
        foreach ($result as $item) {
            $paging_config['display_link_number']   = $item['display_link_number'];
            $paging_config['display_record_number'] = $item['display_record_number'];
        }

        return $paging_config;
    }

}