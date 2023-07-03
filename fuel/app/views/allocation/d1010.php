<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('allocation/d1010.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_DI0018'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_DI0019'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">配車番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'dispatch_number', 'style' => 'width:150px;', 'maxlength' => '10', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">課</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('division', ($data['division'] != '') ? $data['division'] : $userinfo['division_code'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">配送区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('delivery_code', $data['delivery_code'], $delivery_list,
                        array('class' => 'select-item', 'id' => 'delivery_code', 'style' => 'width: 150px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">配車区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('dispatch_code', $data['dispatch_code'], $dispatch_list,
                        array('class' => 'select-item', 'id' => 'dispatch_code', 'style' => 'width: 150px', 'tabindex' => '4')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">地区</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('area_code', $data['area_code'], $area_list,
                        array('class' => 'select-item', 'id' => 'area_code', 'style' => 'width: 150px', 'tabindex' => '5')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">コース</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('course', (!empty($data['course'])) ? $data['course']:'', array('class' => 'input-text', 'id' => 'course', 'maxlength' => '5', 'style' => 'width: 150px;', 'tabindex' => '7')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">納品日</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('delivery_date_from', (!empty($data['delivery_date_from'])) ? $data['delivery_date_from'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'delivery_date_from', 'style' => 'width:160px;', 'tabindex' => '4')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('delivery_date_to', (!empty($data['delivery_date_to'])) ? $data['delivery_date_to'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'delivery_date_to', 'style' => 'width:160px;', 'tabindex' => '5')); ?>
                        <p class="error-message"><?php echo $error_message_sub; ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">引取日</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('pickup_date_from', (!empty($data['pickup_date_from'])) ? $data['pickup_date_from'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'pickup_date_from', 'style' => 'width:160px;', 'tabindex' => '6')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('pickup_date_to', (!empty($data['pickup_date_to'])) ? $data['pickup_date_to'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'pickup_date_to', 'style' => 'width:160px;', 'tabindex' => '7')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">納品先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('delivery_place', (!empty($data['delivery_place'])) ? $data['delivery_place']:'', array('class' => 'input-text', 'id' => 'delivery_place', 'style' => 'width: 300px;', 'tabindex' => '12')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">引取先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('pickup_place', (!empty($data['pickup_place'])) ? $data['pickup_place']:'', array('class' => 'input-text', 'id' => 'pickup_place', 'style' => 'width: 300px;', 'tabindex' => '13')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">得意先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code']:'', array('id' => 'client_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '14')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">傭車先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code']:'', array('id' => 'carrier_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '16')); ?>
                        <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="17" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">商品名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('class' => 'input-text', 'id' => 'product_name', 'style' => 'width: 300px;', 'tabindex' => '18')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('car_model_code', $data['car_model_code'], $car_model_list, array('class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '19')); ?>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車両番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']):'', array('id' => 'car_code', 'type' => 'number' ,'class' => 'input-text', 'min' => '0', 'max' => '9999', 'tabindex' => '20')); ?>
                        <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="21" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">運転手</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name']:'', array('id' => 'driver_name', 'class' => 'input-text', 'tabindex' => '22')); ?>
                        <input type="button" name="s_driver" value="検索" class='buttonA' tabindex="23" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">登録者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('create_user', $data['create_user'], $create_user_list,
                          array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '24')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('allocation/d1011').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
            <?php echo Form::submit('import_regist', '一括登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('allocation/d1020').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('import_file', '雛形ファイル出力', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('allocation/d1030').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('dispatch_number', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1650px;">
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
                <table class="table-inq" style="width: 1650px;">
                    <tr>
                        <th rowspan="2" style="width: 80px;">選択</th>
                        <th rowspan="2" style="width: 100px;">課</th>
                        <!-- <th rowspan="2" style="width: 120px;">配車番号</th> -->
                        <th style="width: 90px;">配送区分</th>
                        <th style="width: 90px;">地区</th>
                        <th style="width: 100px;">納品日</th>
                        <th style="width: 200px;">納品先</th>
                        <th style="width: 90px;">得意先No</th>
                        <th style="width: 280px;">得意先名</th>
                        <th style="width: 80px;">数量</th>
                        <th style="width: 70px;">単位</th>
                        <th style="width: 90px;">傭車費用</th>
                        <th style="width: 100px;">車種</th>
                        <th style="width: 100px;">車両番号</th>
                        <th style="width: 200px;">運転手</th>
                    </tr>
                    <tr>
                        <th>配車区分</th>
                        <th>コース</th>
                        <th>引取日</th>
                        <th>引取先</th>
                        <th>傭車先No</th>
                        <th>傭車先名</th>
                        <th colspan="3">商品名</th>
                        <th colspan="2">メーカー</th>
                        <th>備考</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('allocation/d1012'); ?>', <?php echo $val['dispatch_number']; ?>)" class="buttonA">
                                    <i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                            </td>
                            <!-- <td rowspan="2">
                                <?php echo sprintf('%010d', $val['dispatch_number']); ?>
                            </td> -->
                            <?php echo Form::hidden('dispatch_number_'.$i, $val['dispatch_number']);?>
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division_name']; ?></td>
                            <td><?php echo (isset($delivery_list[$val['delivery_code']])) ? $delivery_list[$val['delivery_code']]:''; ?></td>
                            <td><?php echo (isset($area_list[$val['area_code']])) ? $area_list[$val['area_code']]:''; ?></td>
                            <td style="font-size: 14px;"><?php $delivery_date = new DateTime($val['delivery_date']);echo !empty($val['delivery_date']) ? $delivery_date->format('Y/m/d'):''; ?></td>
                            <td style="font-size: 13px;"><?php echo mb_substr($val['delivery_place'], 0, 15); ?></td>
                            <td><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td style="font-size: 15px;"><?php echo $val['client_name']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['volume'], 2); ?></td>
                            <td><?php echo (isset($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['carrier_payment']); ?></td>
                            <td><?php echo (isset($car_model_list[$val['car_model_code']])) ? $car_model_list[$val['car_model_code']]:''; ?></td>
                            <td><?php echo sprintf('%04d', $val['car_code']); ?></td>
                            <td><?php echo $val['driver_name']; ?></td>
                        </tr>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['dispatch_number']; ?>)" class="buttonA">
                                    <i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td><?php echo (isset($dispatch_list[$val['dispatch_code']])) ? $dispatch_list[$val['dispatch_code']]:''; ?></td>
                            <td><?php echo $val['course']; ?></td>
                            <td style="font-size: 14px;"><?php $pickup_date = new DateTime($val['pickup_date']);echo !empty($val['pickup_date']) ? $pickup_date->format('Y/m/d'):''; ?></td>
                            <td style="font-size: 13px;"><?php echo mb_substr($val['pickup_place'], 0, 15); ?></td>
                            <td><?php echo sprintf('%05d', $val['carrier_code']); ?></td>
                            <td style="font-size: 15px;"><?php echo $val['carrier_name']; ?></td>
                            <td colspan="3"><?php echo mb_substr($val['product_name'], 0, 15); ?></td>
                            <td colspan="2" style="font-size: 13px;"><?php echo $val['maker_name']; ?></td>
                            <td style="font-size: 13px;"><?php echo $val['remarks']; ?></td>
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