<?php defined('COREPATH') or exit('No direct script access allowed'); ?>

ERROR - 2022-10-03 14:19:04 --> Error - syntax error, unexpected ',' in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 357
ERROR - 2022-10-03 17:37:18 --> Notice - Undefined variable: rec_select in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 452
ERROR - 2022-10-03 17:40:26 --> Warning - Attempt to assign property 'head' of non-object in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 89
ERROR - 2022-10-03 17:40:32 --> Warning - Attempt to assign property 'head' of non-object in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 89
ERROR - 2022-10-03 17:40:40 --> Warning - Attempt to assign property 'head' of non-object in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 89
ERROR - 2022-10-03 17:45:14 --> Notice - Undefined variable: data in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/controller/bill/b1030.php on line 532
ERROR - 2022-10-03 19:25:06 --> Compile Error - Cannot redeclare Model\Bill\B1030::getDispatchShare() in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 677
ERROR - 2022-10-03 19:27:31 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 19:28:02 --> Notice - Undefined variable: dispatch_numbers in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 546
ERROR - 2022-10-03 19:28:41 --> 42000 - SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''7' AND `t`.`delete_flag` = 0' at line 1 with query: "SELECT `t`.`dispatch_number` AS `dispatch_number`, `t`.`division_code` AS `division_code`, AES_DECRYPT(UNHEX(md.division_name),"!asdf1234@@@@#") AS `division`, `t`.`delivery_code` AS `delivery_code`, `t`.`dispatch_code` AS `dispatch_code`, `t`.`area_code` AS `area_code`, `t`.`course` AS `course`, `t`.`delivery_date` AS `delivery_date`, `t`.`pickup_date` AS `pickup_date`, AES_DECRYPT(UNHEX(t.delivery_place),"!asdf1234@@@@#") AS `delivery_place`, AES_DECRYPT(UNHEX(t.pickup_place),"!asdf1234@@@@#") AS `pickup_place`, `t`.`client_code` AS `client_code`, `mcl`.`client_name` AS `client_name`, `t`.`carrier_code` AS `carrier_code`, `mca`.`carrier_name` AS `carrier_name`, `t`.`product_name` AS `product_name`, `t`.`maker_name` AS `maker_name`, `t`.`volume` AS `volume`, `t`.`unit_code` AS `unit_code`, `t`.`car_model_code` AS `car_model_code`, `mcm`.`car_model_name` AS `car_model_name`, `t`.`car_code` AS `car_code`, AES_DECRYPT(UNHEX(mc.car_number),"!asdf1234@@@@#") AS `car_number`, `t`.`member_code` AS `member_code`, AES_DECRYPT(UNHEX(t.driver_name),"!asdf1234@@@@#") AS `driver_name`, `t`.`remarks` AS `remarks`, `t`.`inquiry_no` AS `inquiry_no`, `t`.`carrier_payment` AS `carrier_payment`, `t`.`sales_status` AS `sales_status` FROM `t_dispatch_share` AS `t` LEFT OUTER JOIN `m_client` AS `mcl` ON (`t`.`client_code` = `mcl`.`client_code` AND `mcl`.`start_date` <= `t`.`update_datetime` AND `mcl`.`end_date` > `t`.`update_datetime`) LEFT OUTER JOIN `m_carrier` AS `mca` ON (`t`.`carrier_code` = `mca`.`carrier_code` AND `mca`.`start_date` <= `t`.`update_datetime` AND `mca`.`end_date` > `t`.`update_datetime`) LEFT OUTER JOIN `m_division` AS `md` ON (`t`.`division_code` = `md`.`division_code`) LEFT OUTER JOIN `m_car_model` AS `mcm` ON (`t`.`car_model_code` = `mcm`.`car_model_code` AND `mcm`.`start_date` <= `t`.`update_datetime` AND `mcm`.`end_date` > `t`.`update_datetime`) LEFT OUTER JOIN `m_car` AS `mc` ON (`t`.`car_code` = `mc`.`car_code` AND `mc`.`start_date` <= `t`.`update_datetime` AND `mc`.`end_date` > `t`.`update_datetime`) LEFT OUTER JOIN `m_member` AS `mm` ON (`t`.`member_code` = `mm`.`member_code` AND `mm`.`start_date` <= `t`.`update_datetime` AND `mm`.`end_date` > `t`.`update_datetime`) WHERE `t`.`dispatch_number` IN '7' AND `t`.`delete_flag` = 0" in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/database/pdo/connection.php on line 235
ERROR - 2022-10-03 19:29:19 --> Notice - Undefined variable: i in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 548
ERROR - 2022-10-03 19:30:04 --> Notice - Undefined index: dispatch_number in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 549
ERROR - 2022-10-03 19:31:08 --> Error - Class 'PHPExcel_Cell' not found in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 607
ERROR - 2022-10-03 19:35:09 --> Warning - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 518
ERROR - 2022-10-03 19:35:09 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 19:49:21 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 19:49:30 --> Error - Class 'Model\Bill\Fact' not found in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 664
ERROR - 2022-10-03 19:51:21 --> Notice - Undefined variable: title in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 512
ERROR - 2022-10-03 19:51:53 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 19:55:05 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 19:58:36 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:00:48 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:15:02 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:15:13 --> Error - Class 'Model\Bill\Config' not found in /Library/WebServer/Documents/kobayashi/onishi/fuel/app/classes/model/bill/b1030.php on line 536
ERROR - 2022-10-03 20:16:48 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:16:57 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:17:22 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
ERROR - 2022-10-03 20:17:32 --> shutdown - Cannot modify header information - headers already sent by (output started at /Library/WebServer/Documents/kobayashi/onishi/fuel/vendor/maennchen/zipstream-php/src/ZipStream.php:462) in /Library/WebServer/Documents/kobayashi/onishi/fuel/core/classes/cookie.php on 100
