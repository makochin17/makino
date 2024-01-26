<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('excel_dl', null, array('id' => 'excel_dl'));?>
  <?php echo Form::hidden('output_dl', null, array('id' => 'output_dl'));?>
  <script>
      var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
      var error_msg1                  = '<?php echo Config::get('m_MW0013'); ?>';
      var processing_msg1             = '<?php echo Config::get('m_TW0006'); ?>';
  </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row">
      <label>■検索条件</label>
    </div>
    <table class="search-area" style="width: 680px">
      <tbody>
        <tr>
          <td style="width: 200px; height: 30px;">
            配車番号
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number']:'', array('id' => 'dispatch_number', 'type' => 'number', 'class' => 'input-text', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?>
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
            納品日
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('from_delivery_date', (!empty($data['from_delivery_date'])) ? $data['from_delivery_date']:'', array('type' => 'date', 'id' => 'from_delivery_date','class' => 'input-date','tabindex' => '8')); ?>
            <label style="margin: 0 10px;">〜</label>
            <?php echo Form::input('to_delivery_date', (!empty($data['to_delivery_date'])) ? $data['to_delivery_date']:'', array('type' => 'date', 'id' => 'to_delivery_date','class' => 'input-date','tabindex' => '9')); ?>
            <p class="error-message"><?php echo $date_error_message1; ?></p>
            <p class="error-message"><?php echo $error_message_sub; ?></p>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            納品先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('delivery_place', (!empty($data['delivery_place'])) ? $data['delivery_place']:'', array('class' => 'input-text', 'id' => 'delivery_place', 'style' => 'width: 300px;', 'tabindex' => '12')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            得意先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('delivery_slip_code', $data['delivery_slip_code'], $client_list, array('class' => 'select-item', 'id' => 'delivery_slip_code', 'style' => 'width: 300px;', 'tabindex' => '14')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            傭車先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code']:'', array('id' => 'carrier_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '16')); ?>
            <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="17" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', 0)" />
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('car_model_code', $data['car_model_code'], $carmodel_list, array('class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '19')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            車両番号
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']):'', array('id' => 'car_code', 'type' => 'number' ,'class' => 'input-text', 'min' => '0', 'max' => '9999', 'tabindex' => '20')); ?>
            <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="21" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', 0)" />
          </td>
        </tr>
      </tbody>
    </table>
    <div class="search-buttons">
        <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'onSearch();', 'tabindex' => '900')); ?>
    </div>
    <?php echo Form::close(); ?>
    <br />
    <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
    <?php echo Form::hidden('select_dispatch_info', '');?>
    <?php echo Form::hidden('delivery_slip_code', '');?>
    <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
    <?php $i = 0; ?>
    <?php if ($total > 0) : ?>
    <div style="width: 1300px;">
        <div class="content-row" style="float: right">
            検索結果：<?php echo $total; ?> 件
        </div>
        <div class="content-row">&nbsp;</div>
        <!-- ここからPager -->
        <div style="float: right">
            <?php echo $pager; ?>
        </div>
        <div class="content-row">
            <button type="button" onclick="onMultipleSelect()" class="buttonA">チェックしたものを出力</button>
        </div>
        <!-- ここまでPager -->
        <div class="table-wrap" style="clear: right">
            <table class="table-inq" style="width: 1300px;">
                <tr>
                    <th style="width: 60px;">選択</th>
                    <th style="width: 80px;">帳票出力</th>
                    <th style="width: 100px;">課</th>
                    <th style="width: 100px;">納品日</th>
                    <th style="width: 230px;">納品先</th>
                    <th style="width: 90px;">庸車先No</th>
                    <th style="width: 300px;">庸車先名</th>
                    <th style="width: 100px;">車種</th>
                    <th style="width: 100px;">車両番号</th>
                </tr>
                <?php if (!empty($list_data)) : ?>
                  <?php foreach ($list_data as $key => $val) : ?>
                    <?php $i++; ?>
                    <?php $select_value = $val['client_code']."@@".$val['division_code']."@@".$val['division_name']."@@".$val['delivery_date']."@@".$val['delivery_place']."@@".$val['carrier_code']."@@".$val['car_model_code']."@@".$val['car_code']; ?>
                    <tr>
                        <td style="width: 60px; text-align: center;">
                            <?php echo Form::checkbox('select_'.$i, $select_value, false, array('id' => 'form_select_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                            <?php echo Form::label('', 'select_'.$i, array('style' => 'display:inline;padding-left: 1.0em;', 'onclick' => 'onCheckBox('.$i.', \''.$select_value.'\');')); ?>
                        </td>
                        <td style="width: 60px; text-align: center;">
                            <button type="button" onclick="onSelect('<?php echo $select_value; ?>')" class="buttonA">出力</button>
                        </td>
                        <td style="text-align: center;"><?php echo $val['division_name']; ?></td>
                        <td style="font-size: 15px;"><?php $delivery_date = new DateTime($val['delivery_date']);echo !empty($val['delivery_date']) ? $delivery_date->format('Y/m/d'):''; ?></td>
                        <td><?php echo mb_substr($val['delivery_place'], 0, 15); ?></td>
                        <td><?php echo sprintf('%05d', $val['carrier_code']); ?></td>
                        <td><?php echo $val['carrier_name']; ?></td>
                        <td><?php echo $val['car_model_name']; ?></td>
                        <td><?php echo sprintf('%04d', $val['car_code']); ?></td>
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
    <?php echo Form::hidden('record_count', $i);?>
    <?php echo Form::close(); ?>
</main>
