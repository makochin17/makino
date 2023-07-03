<?php
namespace Model\Search;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;
use \Model\Dispatch\D0040\D0040;

class S0080 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車データ（チャーター便）検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return D0040::getSearchCount($conditions, $db);
    }

    /**
     * 配車データ（チャーター便）検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return D0040::getSearch($conditions, $offset, $limit, $db);
    }
    
    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }
    
    /**
     * 庸車先の検索
     */
    public static function getSearchCarrier($code, $db) {
        return M0030::getCarrier($code, $db);
    }
    
    /**
     * 商品の検索
     */
    public static function getSearchProduct($code, $db) {
        return M0060::getProduct($code, $db);
    }
    
    /**
     * 車両の検索
     */
    public static function getSearchCar($code, $db) {
        return M0050::getCar($code, $db);
    }
    
    /**
     * 社員の検索
     */
    public static function getSearchMember($code, $db) {
        return M0010::getMember($code, $db);
    }
}