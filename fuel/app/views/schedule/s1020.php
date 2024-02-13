<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', '');?>
        <?php // echo Asset::js('car/c0010.js');?>
        <script>
            var list_count      = <?php echo $list_count; ?>;
            var clear_msg       = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_LO0007'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_LO0009'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 800px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">予約日</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('start_date_from', (!empty($data['start_date_from'])) ? $data['start_date_from']:'', array('type' => 'date', 'id' => 'start_date_from','class' => 'input-date','tabindex' => '1')); ?>
                        <label style="margin: 0 10px;">〜</label>
                        <?php echo Form::input('start_date_to', (!empty($data['start_date_to'])) ? $data['start_date_to']:'', array('type' => 'date', 'id' => 'start_date_to','class' => 'input-date','tabindex' => '2')); ?>
                    </td>
                </tr>
                <?php /* ?>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'customer_code', 'style' => 'width:150px;', 'maxlength' => '10', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'id' => 'customer_name', 'style' => 'width: 250px;', 'tabindex' => '3')); ?>
                        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                    </td>
                </tr>
                <?php */ ?>
                <tr>
                    <td style="width: 200px; height: 30px;">車両番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:250px;', 'maxlength' => '50', 'tabindex' => '4')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarCodeSearch('<?php echo Uri::create('search/s0020?mode=num'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name']:'', array('class' => 'input-text', 'id' => 'car_name', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '5')); ?>
                        <?php echo Form::hidden('car_id', (!empty($data['car_id'])) ? $data['car_id']:'');?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarNameSearch('<?php echo Uri::create('search/s0020?mode=name'); ?>', 0)" />
                    </td>
                </tr>
                <?php /* ?>
                <tr>
                    <td style="width: 200px; height: 30px;">使用者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('consumer_name', (!empty($data['consumer_name'])) ? $data['consumer_name']:'', array('class' => 'input-text', 'id' => 'consumer_name', 'style' => 'width: 250px;', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <?php */ ?>
                <tr>
                    <td style="width: 200px; height: 30px;">予約タイプ</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('schedule_type', (!empty($data['schedule_type'])) ? $data['schedule_type'] : '', $schedule_type_list,
                        array('class' => 'select-item', 'id' => 'schedule_type', 'style' => 'width: 150px', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;"> </td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::checkbox('cancel_flg', (!empty($data['cancel_flg'])) ? $data['cancel_flg']:'YES', (!empty($data['cancel_flg'])) ? true:false, array('id' => 'form_cancel_flg', 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '5')); ?>
                        <?php echo Form::label('キャンセルした物も含めて表示', 'cancel_flg', array('style' => 'display:inline;padding-left: 2.8em;padding-top: 0.2em;color:#000000;')); ?>
                        <?php echo Form::checkbox('carry_flg', (!empty($data['carry_flg'])) ? $data['carry_flg']:'YES', (!empty($data['carry_flg'])) ? true:false, array('id' => 'form_carry_flg', 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '5')); ?>
                        <?php echo Form::label('持込みした物も含めて表示', 'carry_flg', array('style' => 'display:inline;padding-left: 2.8em;padding-top: 0.2em;color:#000000;')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '102')); ?>
            <?php /* ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('import_regist', '一括登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0020').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('import_file', '雛形ファイル出力', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0030').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php */ ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('schedule_id', '');?>
        <?php echo Form::hidden('mode', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php if ($total > 0) : ?>
        <div style="width: 1400px;">
            <div class="content-row" style="float: right">
                検索結果：<?php echo $total; ?> 件
            </div>
            <div class="content-row">&nbsp;</div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
            <?php /* ?>
            <div class="content-row">
                <button type="button" onclick="onReceiptPrint('<?php echo Uri::create('logistics/l0020'); ?>')" class="buttonA">　入庫シール印刷　</button>
                <button type="button" onclick="allChecked()" class="buttonA">全て選択</button>
                <button type="button" onclick="allUncheck()" class="buttonA">全て解除</button>
            </div>
            <?php */ ?>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1400px;">
                    <tr>
                        <th style="width: 80px;font-size: 13px;">希望日</th>
                        <th style="width: 60px;font-size: 13px;">区分</th>
                        <th style="width: 100px;font-size: 13px;">車番</th>
                        <th style="width: 160px;font-size: 13px;">車種</th>
                        <th style="width: 100px;">使用者</th>
                        <th style="width: 160px;font-size: 13px;">お客様名</th>
                        <th style="width: 160px;font-size: 13px;">ご要望</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <?php echo Form::hidden('schedule_id_'.$i, $val['schedule_id'], array('id' => 'schedule_id_'.$i));?>
                        <?php if ($val['cancel_flg'] == 'YES') : ?>
                            <tr style="background-color: #DDDDDD;">
                        <?php elseif ($val['carry_flg'] == 'YES') : ?>
                            <tr style="background-color: #FFDBC9;">
                        <?php else: ?>
                            <tr>
                        <?php endif; ?>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;">
                                <?php $start_date = new DateTime($val['start_date']);echo !empty($val['start_date']) ? $start_date->format('Y/m/d'):''; ?>
                            </td>
                            <td style="font-size: 13px;text-align:center;padding-left:10px;"><?php echo (isset($schedule_type_list[$val['schedule_type']])) ? $schedule_type_list[$val['schedule_type']]:''; ?></td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;"><?php echo $val['car_code']; ?></td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;"><?php echo $val['car_name']; ?></td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;"><?php echo $val['consumer_name']; ?></td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;"><?php echo $val['customer_name']; ?></td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;word-break:break-all;white-space: normal;"><?php echo $val['request_memo']; ?></td>
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