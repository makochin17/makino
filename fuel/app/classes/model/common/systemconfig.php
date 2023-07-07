<?php
namespace Model\Common;

class SystemConfig extends \Model {

    public static $db       = 'MAKINO';

    /**
     * システム設定値取得
     * $item_name 取得する項目名
     */
    public static function getSystemConfig($item_name, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // データ取得
        $stmt = \DB::select(
                array(\DB::expr('CONCAT(\'!\',m.encrypt_key,\'#\')'), 'encrypt_key'),
                array('m.password_limit', 'password_limit'),
                array('m.password_default', 'password_default')
                );

        // テーブル
        $stmt->from(array('m_system_config', 'm'));

        // 検索実行
        $result = $stmt->execute($db)->as_array();

        foreach ($result as $item) {
            $system_config = $item[$item_name];
        }

        return $system_config;
    }

}