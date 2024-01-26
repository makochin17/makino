<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php // echo Asset::js('car/c0010.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_CAR007'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 800px">
            <tbody>
                <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
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
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'id' => 'customer_name', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '2')); ?>
                        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                    </td>
                </tr>
                <?php */ ?>
                <tr>
                    <td style="width: 200px; height: 30px;">車番</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:250px;', 'maxlength' => '50', 'tabindex' => '3')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarCodeSearch('<?php echo Uri::create('search/s0020?mode=num'); ?>', 0)" />
                    </td>
                </tr>
                <?php /* ?>
                <tr>
                    <td style="width: 200px; height: 30px;">タイヤ種別</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('class_flg', ($data['class_flg'] != '') ? $data['class_flg'] : '', $tire_kind_list,
                        array('class' => 'select-item', 'id' => 'class_flg', 'style' => 'width: 150px', 'tabindex' => '4')); ?></td>
                </tr>
                <?php */ ?>
                <tr>
                    <td style="width: 200px; height: 30px;"> </td>
                    <td style="width: 460px; height: 30px;">
                      <?php echo Form::checkbox('warning_flg', (!empty($data['warning_flg'])) ? $data['warning_flg']:'', (!empty($data['warning_flg'])) ? true:false, array('id' => 'form_warning_flg', 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '5')); ?>
                      <?php echo Form::label('警告があるものを表示', 'warning_flg', array('style' => 'display:inline;padding-left: 2.8em;padding-top: 0.2em;color:#000000;')); ?>
                      <?php echo Form::checkbox('caution_flg', (!empty($data['caution_flg'])) ? $data['caution_flg']:'', (!empty($data['caution_flg'])) ? true:false, array('id' => 'form_caution_flg', 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '6')); ?>
                      <?php echo Form::label('注意があるものを表示', 'caution_flg', array('style' => 'display:inline;padding-left: 2.8em;padding-top: 0.2em;color:#000000;')); ?>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php /* ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('car/c0011').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
            <?php echo Form::submit('import_regist', '一括登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0020').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('import_file', '雛形ファイル出力', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0030').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php */ ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
        <?php echo Form::hidden('car_id', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('mode', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
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
        </div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1400px;">
                    <tr>
                        <th style="width: 70px;">選択</th>
                        <th style="width: 180px;">お客様名</th>
                        <th style="width: 100px;">車番</th>
                        <th style="width: 160px;">車種</th>
                        <th style="width: 60px;">使用者</th>
                        <th style="width: 80px;font-size: 13px;">保管中タイヤ種別</th>
                        <th style="width: 80px;">お預かり日</th>
                        <th style="width: 80px;font-size: 13px;">夏タイヤ残溝数</th>
                        <th style="width: 80px;font-size: 13px;">冬タイヤ残溝数</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onDetail('<?php echo Uri::create('car/c0021'); ?>', '<?php echo $data['customer_code']; ?>', '<?php echo $val['car_id']; ?>')" class="buttonA">
                                    <i class='fa fa-edit' style="font-size:14px;"></i> 詳細</button>
                                <br>
                            </td>
                            <td style="font-size: 13px;text-align:left;padding-left:10px;"><?php echo $val['customer_name']; ?></td>
                            <td style="font-size: 15px;padding-left:10px;"><?php echo $val['car_code']; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo $val['car_name']; ?></td>
                            <td style="font-size: 13px;padding-left:10px;"><?php echo $val['consumer_name']; ?></td>
                            <td style="font-size: 15px;text-align: center;">
                                <?php if ($val['summer_class_flg'] == 'YES' && $val['winter_class_flg'] == 'YES') : ?>
                                    <?php echo $tire_kind_list['summer_winter']; ?>
                                <?php elseif ($val['summer_class_flg'] == 'YES') : ?>
                                    <?php echo $tire_kind_list['summer']; ?>
                                <?php elseif ($val['winter_class_flg'] == 'YES') : ?>
                                    <?php echo $tire_kind_list['winter']; ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 15px;text-align: center;"><?php echo $val['receipt_date']; ?></td>
                            <!-- 夏タイヤ背景色設定 -->
                            <?php if ($val['summer_tire_remaining_groove1'] <= $company_list['summer_tire_warning']) : ?>
                                <?php $color = '#FF0000'; ?>
                            <?php elseif ($val['summer_tire_remaining_groove1'] <= $company_list['summer_tire_caution']) : ?>
                                <?php $color = '#FFFF00'; ?>
                            <?php else: ?>
                                <?php $color = '#FFFFFF'; ?>
                            <?php endif; ?>
                            <td style="text-align: center;background-color:<?php echo $color; ?>;">
                                <?php echo $val['summer_tire_remaining_groove1']; ?>mm
                            </td>
                            <!-- 冬タイヤ背景色設定 -->
                            <?php if ($val['winter_tire_remaining_groove1'] <= $company_list['winter_tire_warning']) : ?>
                                <?php $color = '#FF0000'; ?>
                            <?php elseif ($val['winter_tire_remaining_groove1'] <= $company_list['winter_tire_caution']) : ?>
                                <?php $color = '#FFFF00'; ?>
                            <?php else: ?>
                                <?php $color = '#FFFFFF'; ?>
                            <?php endif; ?>
                            <td style="text-align: center;background-color:<?php echo $color; ?>;">
                                <?php echo $val['winter_tire_remaining_groove1']; ?>mm
                            </td>
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