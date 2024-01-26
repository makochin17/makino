<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('search/s1030.js');?>
        <script>
            var processing_msg1 = '<?php echo str_replace('XXXXX','保管料データ',Config::get('m_CW0015')); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
          <tbody>
            <tr>
              <td style="width: 200px; height: 30px;">
                保管料番号
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('storage_fee_number', (!empty($data['storage_fee_number'])) ? $data['storage_fee_number']:'', array('id' => 'storage_fee_number', 'type' => 'number', 'class' => 'input-text', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                課<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::select('division_code', $data['division_code'], $division_list, array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px;', 'tabindex' => '2')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                売上状態<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list, array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 150px;', 'tabindex' => '3')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                締日
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('from_closing_date', (!empty($data['from_closing_date'])) ? $data['from_closing_date']:'', array('type' => 'date', 'id' => 'from_closing_date','class' => 'input-date','tabindex' => '4')); ?>
                <label style="margin: 0 10px;">〜</label>
                <?php echo Form::input('to_closing_date', (!empty($data['to_closing_date'])) ? $data['to_closing_date']:'', array('type' => 'date', 'id' => 'to_closing_date','class' => 'input-date','tabindex' => '5')); ?>
                <p class="error-message"><?php echo $date_error_message1; ?></p>
                <p class="error-message"><?php echo $error_message_sub; ?></p>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                得意先
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('client_code', (!empty($data['client_code'])) ? sprintf('%05d', $data['client_code']):'', array('id' => 'client_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '6')); ?>
                <input type="button" name="s_client" value="検索" class='buttonA' tabindex="6" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                保管場所
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('storage_location', (!empty($data['storage_location'])) ? $data['storage_location']:'', array('class' => 'input-text', 'id' => 'storage_location', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '7')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                商品名
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('class' => 'input-text', 'id' => 'product_name', 'style' => 'width: 300px;', 'maxlength' => '30', 'tabindex' => '8')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                メーカー名
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::input('maker_name', (!empty($data['maker_name'])) ? $data['maker_name']:'', array('class' => 'input-text', 'id' => 'maker_name', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '9')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                保管料区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
              </td>
              <td style="width: 480px; height: 30px;">
                <?php echo Form::select('storage_fee_code', $data['storage_fee_code'], $storage_fee_list, array('class' => 'select-item', 'id' => 'storage_fee_code', 'style' => 'width: 180px;', 'tabindex' => '10')); ?>
              </td>
            </tr>
            <tr>
              <td style="width: 200px; height: 30px;">
                登録者
              </td>
              <td style="width: 460px; height: 30px;">
                <?php echo Form::select('create_user', $data['create_user'], $create_user_list, array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '11')); ?></td>
            </tr>
          </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('select_storage_fee_number', '');?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php $i = 0; ?>
        <?php if ($total > 0) : ?>
        <div style="width: 1650px;">
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div class="content-row">
                <?php echo $pager; ?>
                <button type="button" onclick="onMultipleSelect()" class="buttonA">チェックしたものを選択</button>
			</div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1400px;">
                    <tr>
                      <th rowspan="2" style="width: 120px;">選択</th>
                      <th rowspan="2" style="width: 100px;">課</th>
                      <th rowspan="2" style="width: 90px;">売上<br>状態</th>
                      <th rowspan="2" style="width: 100px;">締日</th>
                      <th rowspan="2" style="width: 90px;">得意先No</th>
                      <th style="width: 300px;">得意先名</th>
                      <th style="width: 90px;">保管料区分</th>
                      <th style="width: 90px;">保管料</th>
                      <th style="width: 80px;">単価</th>
                      <th style="width: 80px;">数量</th>
                      <th style="width: 70px;">単位</th>
                      <th style="width: 90px;">端数処理</th>
                      <th rowspan="2" style="width: 200px;">備考</th>
                    </tr>
                    <tr>
                      <th>保管場所</th>
                      <th colspan="3">商品名</th>
                      <th colspan="3">メーカー名</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td rowspan="2" style="width: 60px; text-align: center;">
                                <?php echo Form::checkbox('select_'.$i, $val['storage_fee_number'], false, array('id' => 'form_select_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                                <?php echo Form::label('', 'select_'.$i, array('style' => 'display:inline;padding-left: 1.0em;', 'onclick' => 'onCheckBox('.$i.', '.$val['storage_fee_number'].');')); ?>
                                &nbsp;
                                <button type="button" onclick="onSelect('<?php echo $val['storage_fee_number']; ?>')" class="buttonA">選択</button>
                            </td>
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division_name']; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo (isset($sales_status_list[$val['sales_status']]) && !empty($sales_status_list[$val['sales_status']])) ? $sales_status_list[$val['sales_status']]:''; ?></td>
                            <td rowspan="2" style="font-size: 15px;"><?php $closing_date = new DateTime($val['closing_date']);echo !empty($val['closing_date']) ? $closing_date->format('Y/m/d'):''; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td><?php echo $val['client_name']; ?></td>
                            <td style="text-align: center;"><?php echo (isset($storage_fee_list[$val['storage_fee_code']]) && !empty($storage_fee_list[$val['storage_fee_code']])) ? $storage_fee_list[$val['storage_fee_code']]:''; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['storage_fee']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['unit_price'], 2); ?></td>
                            <td style="text-align: right;"><?php echo floatval(number_format($val['volume'], 6)); ?></td>
                            <td><?php echo (isset($unit_list[$val['unit_code']]) && !empty($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                            <td><?php echo (isset($rounding_list[$val['rounding_code']]) && !empty($val['rounding_code'])) ? $rounding_list[$val['rounding_code']]:''; ?></td>
                            <td rowspan="2" style="font-size: 13px;"><?php echo $val['remarks']; ?></td>
                        </tr>
                        <tr>
                          <td><?php echo $val['storage_location']; ?></td>
                          <td colspan="3" style="font-size: 14px;"><?php echo mb_substr($val['product_name'], 0, 15); ?></td>
                          <td colspan="3" style="font-size: 14px;"><?php echo $val['maker_name']; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif ; ?>
                </table>
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
        </div>
        <?php endif ; ?>
        <?php echo Form::hidden('record_count', $i);?>
        <?php echo Form::close(); ?>
    </div>
</section>