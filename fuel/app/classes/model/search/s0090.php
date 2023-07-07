<?php
namespace Model\Search;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Dispatch\D0060\D0060;

class S0090 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 売上補正レコード検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return D0060::getSearchCount($conditions, $db);
    }

    /**
     * 売上補正レコード検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return D0060::getSearch($conditions, $offset, $limit, $db);
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