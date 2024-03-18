<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php // echo Asset::js('car/c0010.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_LO0007'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_LO0009'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 800px">
            <tbody>
                <?php /* ?>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'customer_code', 'style' => 'width:150px;', 'maxlength' => '10', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <?php */ ?>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'id' => 'customer_name', 'style' => 'width: 250px;', 'tabindex' => '2')); ?>
                        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車両番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:250px;', 'maxlength' => '50', 'tabindex' => '3')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarCodeSearch('<?php echo Uri::create('search/s0020?mode=num'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name']:'', array('class' => 'input-text', 'id' => 'car_name', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '4')); ?>
                        <?php echo Form::hidden('car_id', (!empty($data['car_id'])) ? $data['car_id']:'');?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarNameSearch('<?php echo Uri::create('search/s0020?mode=name'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">使用者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('consumer_name', (!empty($data['consumer_name'])) ? $data['consumer_name']:'', array('class' => 'input-text', 'id' => 'consumer_name', 'style' => 'width: 250px;', 'tabindex' => '5')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">タイヤ種別</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('tire_type', (!empty($data['tire_type'])) ? $data['tire_type'] : '', $tire_kubun_list,
                        array('class' => 'select-item', 'id' => 'tire_type', 'style' => 'width: 150px', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <?php if (!empty($location_combo_list)) : ?>
                    <tr>
                        <td style="width: 200px; height: 30px;">保管場所</td>
                        <td style="width: 460px; height: 30px;">
                            <?php echo Form::select('location_id', (!empty($data['location_id'])) ? $data['location_id'] : '', $location_combo_list,
                            array('class' => 'select-item', 'id' => 'location_id', 'style' => 'width: 250px', 'tabindex' => '7')); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="width: 200px; height: 30px;">入庫</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::radio('receipt_flg', 0, '', 
                        array('id' => 'form_receipt_flg1', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('全て', 'receipt_flg1'); ?>
                        &emsp;
                        <?php echo Form::radio('receipt_flg', 'YES', $data['receipt_flg'] == 'YES', 
                        array('id' => 'form_receipt_flg2', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('入庫済', 'receipt_flg2'); ?>
                        &emsp;
                        <?php echo Form::radio('receipt_flg', 'NO', $data['receipt_flg'] == 'NO', 
                        array('id' => 'form_receipt_flg3', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('未入庫', 'receipt_flg3'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">出庫</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::radio('delivery_flg', 0, '', 
                        array('id' => 'form_delivery_flg1', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('全て', 'delivery_flg1'); ?>
                        &emsp;
                        <?php echo Form::radio('delivery_flg', 'YES', $data['delivery_flg'] == 'YES', 
                        array('id' => 'form_delivery_flg2', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('出庫済', 'delivery_flg2'); ?>
                        &emsp;
                        <?php echo Form::radio('delivery_flg', 'NO', $data['delivery_flg'] == 'NO', 
                        array('id' => 'form_delivery_flg3', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('未出庫', 'delivery_flg3'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">出庫指示</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::radio('delivery_schedule_flg', 0, '', 
                        array('id' => 'form_delivery_schedule_flg1', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('全て', 'delivery_schedule_flg1'); ?>
                        &emsp;
                        <?php echo Form::radio('delivery_schedule_flg', 'YES', $data['delivery_schedule_flg'] == 'YES', 
                        array('id' => 'form_delivery_schedule_flg2', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('指示済', 'delivery_schedule_flg2'); ?>
                        &emsp;
                        <?php echo Form::radio('delivery_schedule_flg', 'NO', $data['delivery_schedule_flg'] == 'NO', 
                        array('id' => 'form_delivery_schedule_flg3', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('未指示', 'delivery_schedule_flg3'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;"> </td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::checkbox('location_flg', (!empty($data['location_flg'])) ? $data['location_flg']:'YES', (!empty($data['location_flg'])) ? true:false, array('id' => 'form_location_flg', 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '5')); ?>
                        <?php echo Form::label('倉庫にあるもののみ表示', 'location_flg', array('style' => 'display:inline;padding-left: 2.8em;padding-top: 0.2em;color:#000000;')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php /* ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php */ ?>
            <?php echo Form::submit('add', '入庫処理', array('class' => 'buttonB', 'onclick' => 'onReceipt(\''.Uri::create('logistics/l0011').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
            <?php echo Form::submit('add', '出庫処理', array('class' => 'buttonB', 'onclick' => 'onDelivery(\''.Uri::create('logistics/l0012').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('add', '出庫指示', array('class' => 'buttonB', 'onclick' => 'onDelivery(\''.Uri::create('logistics/l0013').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php /* ?>
            <?php echo Form::submit('add', '出庫処理', array('class' => 'buttonB', 'onclick' => 'onDelivery(\''.Uri::create('logistics/l0013').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php echo Form::submit('import_regist', '一括登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0020').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('import_file', '雛形ファイル出力', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0030').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php */ ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('print_status_id', (!empty($print_status_id)) ? $print_status_id:'', array('id' => 'print_status_id'));?>
        <?php echo Form::hidden('all_logistics_ids', $all_logistics_ids);?>
        <?php echo Form::hidden('logistics_id', '');?>
        <?php echo Form::hidden('mode', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php if ($total > 0) : ?>
        <div style="width: 1600px;">
            <div class="content-row" style="float: right">
                検索結果：<?php echo $total; ?> 件
            </div>
            <div class="content-row">&nbsp;</div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
            <div class="content-row">
                <button type="button" onclick="onReceiptPrint('<?php echo Uri::create('logistics/l0020'); ?>')" class="buttonA">　入庫シール印刷　</button>
                <button type="button" onclick="allChecked()" class="buttonA">全て選択</button>
                <button type="button" onclick="allUncheck()" class="buttonA">全て解除</button>
            </div>
        </div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1600px;">
                    <tr>
                        <th rowspan="2" style="width: 60px;">選択</th>
                        <th rowspan="2" style="width: 60px;font-size: 13px;">印刷対象</th>
                        <th rowspan="2" style="width: 60px;font-size: 13px;">ユニット</th>
                        <th rowspan="2" style="width: 60px;font-size: 13px;">入庫状況</th>
                        <th rowspan="2" style="width: 60px;font-size: 13px;">出庫指示状況</th>
                        <th rowspan="2" style="width: 60px;font-size: 13px;">出庫状況</th>
                        <th style="width: 60px;font-size: 13px;">入庫日</th>
                        <th style="width: 60px;font-size: 13px;">出庫指示日</th>
                        <th style="width: 60px;font-size: 13px;">出庫日</th>
                        <th style="width: 140px;">お客様名</th>
                        <th style="width: 160px;">車種</th>
                        <th style="width: 140px;font-size: 13px;">タイヤ種別</th>
                        <!-- <th style="width: 60px;font-size: 13px;">残溝</th> -->
                    </tr>
                    <tr>
                        <th style="font-size: 13px;">入庫時間</th>
                        <th style="font-size: 13px;">出庫指示時間</th>
                        <th style="font-size: 13px;">出庫時間</th>
                        <th>使用者</th>
                        <th>車番</th>
                        <th style="font-size: 13px;">保管場所</th>
                        <!-- <th style="font-size: 13px;">予約状況</th> -->
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <?php echo Form::hidden('logistics_id_'.$i, $val['logistics_id'], array('id' => 'logistics_id_'.$i));?>
                        <tr>
                            <td rowspan="2" style="font-size: 13px;text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['logistics_id']; ?>, '<?php echo $val['receipt_date']; ?>', '<?php echo $val['delivery_date']; ?>', '<?php echo $val['customer_name']; ?>', '<?php echo $val['car_code']; ?>')" class="buttonA" style="width:60px; height:30px;">
                                    <i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td rowspan="2" style="text-align: center;">
                                <?php echo Form::checkbox('print_status_'.$i, $val['logistics_id'], false, array('id' => 'form_print_status_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                                <?php echo Form::label('', 'print_status_'.$i, array('style' => 'display:inline;padding-left: 1.0em;')); ?>
                            </td>
                            <td rowspan="2" style="font-size: 11px;text-align: center;"><?php echo (isset($unit_list[$val['unit_id']])) ? $unit_list[$val['unit_id']]:''; ?></td>
                            <td rowspan="2" style="font-size: 13px;text-align: center;">
                                <?php if ($val['receipt_flg'] == 'NO') : ?>
                                    <!-- 未入庫の場合 -->
                                    <button type="button" onclick="onEdit('<?php echo Uri::create('logistics/l0011'); ?>', <?php echo $val['logistics_id']; ?>, 'receipt_no')" class="buttonA" style="width:60px; height:30px;margin-bottom: 4px;">
                                        <i class='fa fa-edit' style="font-size:14px;"></i> 入庫</button>
                                <?php else : ?>
                                    <?php if ($val['delivery_schedule_flg'] == 'NO') : ?>
                                    <!-- 入庫済の場合 -->
                                    <button type="button" onclick="onEdit('<?php echo Uri::create('logistics/l0011'); ?>', <?php echo $val['logistics_id']; ?>, 'receipt_yes')" class="buttonA" style="width:60px; height:30px;margin-bottom: 4px;">
                                        <i class='fa fa-edit' style="font-size:10px;"></i>入庫済</button>
                                    <?php else : ?>
                                        <span style="width:60px; height:30px;line-height: 22px;font-size: 12px;">入庫済</span>
                                    <?php endif ; ?>
                                <?php endif ; ?>
                            </td>
                            <td rowspan="2" style="font-size: 13px;text-align: center;">
                                <?php if ($val['delivery_schedule_flg'] == 'NO') : ?>
                                    <!-- 未出庫指示の場合 -->
                                    <span style="width:60px; height:30px;line-height: 22px;font-size: 12px;">未出庫指示</span>
                                <?php else : ?>
                                    <!-- 出庫指示済の場合 -->
                                    <span style="width:60px; height:30px;line-height: 22px;font-size: 12px;">出庫指示済</span>
                                <?php endif ; ?>
                            </td>
                            <td rowspan="2" style="font-size: 13px;text-align: center;">
                                <?php if ($val['delivery_flg'] == 'NO') : ?>
                                    <!-- 未出庫の場合 -->
                                    <!--
                                    <button type="button" onclick="onEdit('<?php echo Uri::create('logistics/l0012'); ?>', <?php echo $val['logistics_id']; ?>, 'delivery_no')" class="buttonA" style="width:60px; height:30px;margin-bottom: 4px;">
                                        <i class='fa fa-edit' style="font-size:14px;"></i> 出庫</button>
                                    -->
                                    <span style="width:60px; height:30px;line-height: 22px;font-size: 12px;">未出庫</span>
                                <?php else : ?>
                                    <!-- 出庫済の場合 -->
                                    <span style="width:60px; height:30px;line-height: 22px;font-size: 12px;">出庫済</span>
                                <?php endif ; ?>
                            </td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;">
                                <?php $receipt_date = new DateTime($val['receipt_date']);echo !empty($val['receipt_date']) ? $receipt_date->format('Y/m/d'):''; ?>
                            </td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;">
                                <?php $delivery_schedule_date = new DateTime($val['delivery_schedule_date']);echo !empty($val['delivery_schedule_date']) ? $delivery_schedule_date->format('Y/m/d'):''; ?>
                            </td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;">
                                <?php $delivery_date = new DateTime($val['delivery_date']);echo !empty($val['delivery_date']) ? $delivery_date->format('Y/m/d'):''; ?>
                            </td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;">
                                <?php echo $val['customer_name']; ?>
                            </td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo $val['car_name']; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo (isset($tire_kubun_list[$val['tire_type']])) ? $tire_kubun_list[$val['tire_type']]:''; ?></td>
                            <!-- <td style="font-size: 13px;padding-left:10px;"><?php echo $val['tire_remaining_groove1']; ?>mm</td> -->
                        </tr>
                        <tr>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo ($val['receipt_time'] != '00:00') ? $val['receipt_time']:''; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo ($val['delivery_schedule_time'] != '00:00') ? $val['delivery_schedule_time']:''; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo ($val['delivery_time'] != '00:00') ? $val['delivery_time']:''; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo $val['consumer_name']; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo $val['car_code']; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo (isset($location_list[$val['location_id']])) ? $location_list[$val['location_id']]:'不明'; ?></td>
                            <!-- <td style="font-size: 13px;padding-left:10px;"><?php echo (!empty($val['schedule_id'])) ? '予約有':'予約無'; ?></td> -->
                        </tr>
                      <?php endforeach; ?>
                    <?php endif ; ?>
                </table>
            </div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
        </div>
        <?php endif ; ?>
        <?php echo Form::close(); ?>
    </div>
</section>