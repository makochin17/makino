<?php
namespace Model\System;
use \Model\Init;
use \Model\Table\CalendarHoliday;

class C0050 extends \Model {

    public static $db               = 'MAKINO';
    public static $table            = 'calendar_holiday';

    // カレンダー表示処理
    public static function calendar($year = '', $month = '', $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($year) && empty($month)) {
            $year = date('Y');
            $month = date('n');
        }

        //月末の取得
        $l_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));

        $html = "<table class=\"calendar\" style=\"border-collapse: collapse;\">";
        $html .= "<caption style=\"text-align:center;font-weight:bold;\">\n";
        $html .= $year."年".$month."月\n";
        $html .= "</caption>\n";
        $html .= "<tr>\n";
        $html .= "<th class=\"sun\">".\Html::anchor('#', '日', array('class'=>'sun', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'0'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '月', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'1'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '火', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'2'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '水', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'3'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '木', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'4'))."</th>\n";
        $html .= "<th>".\Html::anchor('#', '金', array('class'=>'no', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'5'))."</th>\n";
        $html .= "<th class=\"sat\">".\Html::anchor('#', '土', array('class'=>'sat', 'id'=>'w_holiday', 'data-year'=>$year, 'data-month'=>$month, 'data-no'=>'6'))."</th>\n";
        $html .= "</tr>\n";

        // カレンダーデータ取得
        $list = self::getCalendar($year, $month, self::$db);

        $holidays = array();
        $holidays2 = array();
        if (!empty($list)) {
            foreach ($list as $key => $row) {
                array_push($holidays, $row['holiday']);

                if (isset($row['level']) && $row['level'] == "1") {
                    array_push($holidays2, $row['holiday']);
                }
            }
        }
        $lc = 0;
        $tab = '';

        // 月末まで繰り返す
        for ($i = 1; $i < $l_day + 1;$i++) {
            $classes = array();
            $class   = '';

            // 曜日の取得
            $week = date('w', mktime(0, 0, 0, $month, $i, $year));

            // 曜日が日曜日の場合
            if ($week == 0) {
                $html .= $tab."\t\t<tr>\n";
                $lc++;
            }

            // 1日の場合、それよりも前のブランクを生成
            if ($i == 1) {
                if($week != 0) {
                    $html .= $tab."\t\t<tr>\n";
                    $lc++;
                }
                $html .= str_repeat("\t\t<td> </td>\n", $week);
            }

            //土曜と日曜を設定
            $classes[] = 'no';
            if ($week == 6) {
                $classes[] = 'sat';
            } else if ($week == 0) {
                $classes[] = 'sun';
            }
            // 「今日」の日付の場合
            if ($i == date('j') && $year == date('Y') && $month == date('n')) {
                $classes[] = 'today';
            }

            //cssクラスを設定
            if (count($classes) > 0) {
                $class = ' class="'.implode(' ', $classes).'"';
            }

            //休日かどうかを設定
            $style  = '';
            $today  = date("Y-m-d",mktime(0, 0, 0, $month , $i, $year));
            $mode   = '0';    //0：平日 1：休日 2：出荷お休み
            if (in_array($today, $holidays)) {
                if (in_array($today, $holidays2)) {
                    $mode = '2';
                    $style = ' style="background-color:#FFFFCC;"';
                } else {
                    $mode = '1';
                    $style = ' style="background-color:#FFD2E1;"';
                }
            }

            //日付をひとつ作成
            switch ($mode) {
                case '1':   //休日
                    $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="0" >'.$i.'</a></td>'."\n";
                break;
                case '2':   //出荷お休み
                    $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="2" >'.$i.'</a></td>'."\n";
                break;
                default:    //平日
                    $html .= $tab."\t\t\t".'<td'.$class.' '.$style.'><a href="#"'.$class.' '.$style.' id="days" data-year="'.$year.'"" data-month="'.$month.'" data-day="'.$i.'" data-mode="1" >'.$i.'</a></td>'."\n";
                break;
            }

            // 月末の場合、週の残りをブランクにする
            if ($i == $l_day) {
                $html .= str_repeat("\t\t<td> </td>\n", (6 - $week));
            }

            // 土曜日の場合
            if ($week == 6) {
                $html .= $tab."\t\t</tr>\n";
            }
        }

        if ($lc < 6) {
            $html .= "\t<tr>\n";
            $html .= str_repeat("\t\t<td>　</td>\n", 7);
            $html .= "\t</tr>\n";
        }

        if ($lc == 4) {
            $html .= "\t<tr>\n";
            $html .= str_repeat("\t\t<td>　</td>\n", 7);
            $html .= "\t</tr>\n";
        }

        $html .= "</table>\n";

        return $html;
    }

    /**
     * カレンダーデータ取得
     */
    public static function getCalendar($year, $month, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }
        $start_day  = date("Y/m/d",mktime(0, 0, 0, $month, 1, $year));
        $end_day    = date("Y/m/d",mktime(0, 0, 0, $month + 1, 0, $year));

        return \DB::select(
            array('m.holiday', 'holiday'),
            array(\DB::expr("'0'"), 'level'),
            array('m.comment', 'comment')
        )
        ->from(array('calendar_holiday', 'm'))
        ->where('m.del_flg', 'NO')
        ->where('m.holiday', 'BETWEEN', array($start_day, $end_day))
        ->order_by('m.holiday')
        ->execute($db)
        ->as_array();
        ;
    }

    /**
     * カレンダーデータ休日追加
     */
    public static function setCalendar($holiday, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $set = array(
            'holiday' => $holiday
        );

        return self::set($set, $db);
    }

    /**
     * カレンダーデータ休日削除
     */
    public static function delCalendar($holiday, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return self::del($holiday, $db);
    }

    /**
     * 追加
     */
    public static function set($set, $db = null) {

        if (empty($db)) {
            $db = self::$db;
        }

        $sql  = \DB::insert(self::$table)->set($set);
        list($insert_id, $rows_affected) =  $sql->execute($db);

        if(!$insert_id) {
            return false;
        }
        return $insert_id;

    }

    /**
     * 更新
     */
    public static function update($holiday, $db = null) {

        if (empty($db)) {
            $db = self::$db;
        }

        $sql = \DB::update(self::$table)
        ->set($set)
        ->where('holiday', $holiday);
        $res = $sql->execute($db)
        ;

        if ($res === false) {
            return false;
        }
        return true;
    }

    /**
     * 削除
     */
    public static function del($holiday, $db = null) {

        if (empty($db)) {
            $db = self::$db;
        }

        $sql = \DB::delete(self::$table)
        ->where('holiday', $holiday);
        $res = $sql->execute($db)
        ;

        if(is_null($res)) {
            return false;
        }
        return true;
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

    //=========================================================================//
    //=======================   共               通   =========================//
    //=========================================================================//
    /**
     * 日付チェック
     */
    public static function is_date($date) {

        // 空文字は通す 通したくない場合はrequiredを使用させる
        if ($date == '') {
            return true;
        }

        $date = str_replace('/', '-', $date);
        if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $m)) {
            // yyyy-mm-dd形式でない場合
            return false;
        }
        return checkdate($m[2], $m[3], $m[1]);
    }

    /**
     * 日付チェック
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * 日時チェック
     */
    public static function validateDateTime($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * 数値チェック
     */
    public static function validateNumeric($data) {

        if (empty($data)) {
            return true;
        }

        if (is_numeric($data)) {
            return true;
        }

        return false;
    }

    /**
     * 数値整形
     * $str         値
     */
    public static function moldingNumeric($str) {

        if (substr($str, -1) == '.') {
            return (string)((int)substr($str, 0, -1));
        }

        if (is_numeric($str)) {
            if (preg_match('/\./', $str)) {
                $tmp = explode('.', $str);
                return (string)((int)$tmp[0].'.'.$tmp[1]);
            } else {
                return (string)((int)$str);
            }
        }
        return $str;
    }

}