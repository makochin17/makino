<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('excel_dl', null, array('id' => 'excel_dl'));?>
  <?php echo Form::hidden('output_dl', null, array('id' => 'output_dl'));?>
  <script>
      var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
      var error_msg1                  = '<?php echo Config::get('m_MW0013'); ?>';
      var processing_msg1             = '<?php echo Config::get('m_DI0006'); ?>';
      var processing_msg2             = '<?php echo Config::get('m_DI0007'); ?>';
      var processing_msg3             = '<?php echo Config::get('m_DI0008'); ?>';
      var processing_msg4             = '<?php echo Config::get('m_MI0008'); ?>';
      var processing_msg5             = '<?php echo Config::get('m_MI0010'); ?>';
      var processing_msg6             = '<?php echo Config::get('m_DW0017'); ?>';
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
            売上状態<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list, array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 150px;', 'tabindex' => '3')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('delivery_code', $data['delivery_code'], $delivery_category_list, array('class' => 'select-item', 'id' => 'delivery_code', 'style' => 'width: 150px;', 'tabindex' => '4')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            配車区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('dispatch_code', $data['dispatch_code'], $dispatch_category_list, array('class' => 'select-item', 'id' => 'dispatch_code', 'style' => 'width: 150px;', 'tabindex' => '5')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            地区<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('area_code', $data['area_code'], $area_list, array('class' => 'select-item', 'id' => 'area_code', 'style' => 'width: 150px;', 'tabindex' => '6')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            コース
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('course', (!empty($data['course'])) ? $data['course']:'', array('class' => 'input-text', 'id' => 'course', 'style' => 'width: 150px;', 'tabindex' => '7')); ?>
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
            引取日
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('from_pickup_date', (!empty($data['from_pickup_date'])) ? $data['from_pickup_date']:'', array('type' => 'date', 'id' => 'from_pickup_date','class' => 'input-date','tabindex' => '10')); ?>
            <label style="margin: 0 10px;">〜</label>
            <?php echo Form::input('to_pickup_date', (!empty($data['to_pickup_date'])) ? $data['to_pickup_date']:'', array('type' => 'date', 'id' => 'to_pickup_date','class' => 'input-date','tabindex' => '11')); ?>
            <p class="error-message"><?php echo $date_error_message2; ?></p>
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
            引取先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('pickup_place', (!empty($data['pickup_place'])) ? $data['pickup_place']:'', array('class' => 'input-text', 'id' => 'pickup_place', 'style' => 'width: 300px;', 'tabindex' => '13')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            得意先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code']:'', array('id' => 'client_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '14')); ?>
            <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
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
            商品名
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('class' => 'input-text', 'id' => 'product_name', 'style' => 'width: 300px;', 'tabindex' => '18')); ?>
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
        <tr>
          <td style="width: 200px; height: 30px;">
            運転手
          </td>
          <td style="width: 480px; height: 30px;">
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
      <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
      <?php echo Form::button('output', '配車表出力', array('type' => 'button', 'id' => 'output', 'class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '902')); ?>
      <?php echo Form::button('excel', 'エクセル出力', array('type' => 'button', 'id' => 'excel', 'class' => 'buttonB', 'tabindex' => '903')); ?>
    </div>
  <?php echo Form::close(); ?>
  <br>
  <form action="" class="formBody">
    <?php if (!empty($list_data)) : ?>
    <div class="content-row">
      <label>検索結果：<?php echo $total; ?> 件</label>
    </div>
    <div class="content-row">
      <div class="pagenation">
        <!-- ここからPager -->
        <?php echo $pager; ?>
        <!-- ここまでPager -->
      </div>
    </div>
    <div class="table-wrap" style="clear: right">
        <table class="table-inq" style="width: 1650px;">
            <tr>
                <th rowspan="2" style="width: 100px;">課</th>
                <th rowspan="2" style="width: 60px;">売上<br>状態</th>
                <th style="width: 90px;">配送区分</th>
                <th style="width: 90px;">地区</th>
                <th style="width: 100px;">納品日</th>
                <th style="width: 200px;">納品先</th>
                <th style="width: 90px;">得意先No</th>
                <th style="width: 300px;">得意先名</th>
                <th style="width: 80px;">数量</th>
                <th style="width: 70px;">単位</th>
                <th style="width: 90px;">庸車費用</th>
                <th style="width: 100px;">車種</th>
                <th style="width: 100px;">車両番号</th>
                <th style="width: 200px;">運転手</th>
            </tr>
            <tr>
                <th>配車区分</th>
                <th>コース</th>
                <th>引取日</th>
                <th>引取先</th>
                <th>庸車先No</th>
                <th>庸車先名</th>
                <th colspan="3">商品名</th>
                <th colspan="2">メーカー</th>
                <th>備考</th>
            </tr>
              <?php foreach ($list_data as $key => $val) : ?>
                <tr>
                    <td rowspan="2" style="text-align: center;"><?php echo $val['division_name']; ?></td>
                    <td rowspan="2" style="text-align: center;"><?php echo (isset($sales_status_list[$val['sales_status']]) && !empty($sales_status_list[$val['sales_status']])) ? $sales_status_list[$val['sales_status']]:''; ?></td>
                    <td><?php echo (isset($delivery_category_list[$val['delivery_code']]) && !empty($delivery_category_list[$val['delivery_code']])) ? $delivery_category_list[$val['delivery_code']]:''; ?></td>
                    <td><?php echo (isset($area_list[$val['area_code']]) && !empty($area_list[$val['area_code']])) ? $area_list[$val['area_code']]:''; ?></td>
                    <td style="font-size: 14px;"><?php $delivery_date = new DateTime($val['delivery_date']);echo !empty($val['delivery_date']) ? $delivery_date->format('Y/m/d'):''; ?></td>
                    <td style="font-size: 13px;"><?php echo mb_substr($val['delivery_place'], 0, 15); ?></td>
                    <td><?php echo sprintf('%05d', $val['client_code']); ?></td>
                    <td><?php echo $val['client_name']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($val['volume'], 2); ?></td>
                    <td><?php echo (isset($unit_list[$val['unit_code']]) && !empty($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                    <td style="text-align: right;"><?php echo number_format($val['carrier_payment']); ?></td>
                    <td><?php echo $val['car_model_name']; ?></td>
                    <td><?php echo sprintf('%04d', $val['car_code']); ?></td>
                    <td><?php echo $val['driver_name']; ?></td>
                </tr>
                <tr>
                    <td><?php echo (isset($dispatch_category_list[$val['dispatch_code']]) && !empty($dispatch_category_list[$val['dispatch_code']])) ? $dispatch_category_list[$val['dispatch_code']]:''; ?></td>
                    <td><?php echo $val['course']; ?></td>
                    <td style="font-size: 14px;"><?php $pickup_date = new DateTime($val['pickup_date']);echo !empty($val['pickup_date']) ? $pickup_date->format('Y/m/d'):''; ?></td>
                    <td style="font-size: 13px;"><?php echo mb_substr($val['pickup_place'], 0, 15); ?></td>
                    <td><?php echo sprintf('%05d', $val['carrier_code']); ?></td>
                    <td><?php echo $val['carrier_name']; ?></td>
                    <td colspan="3"><?php echo mb_substr($val['product_name'], 0, 15); ?></td>
                    <td colspan="2" style="font-size: 13px;"><?php echo $val['maker_name']; ?></td>
                    <td style="font-size: 13px;"><?php echo $val['remarks1']; ?></td>
                </tr>
              <?php endforeach; ?>
        </table>
    </div>
    <div class="content-row">
      <div class="pagenation">
        <!-- ここからPager -->
        <?php echo $pager; ?>
        <!-- ここまでPager -->
      </div>
    </div>
    <?php endif ; ?>
  </form>
</main>
