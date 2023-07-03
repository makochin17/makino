<?php use \Model\Common\closingdate; ?>
<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('list_url', $list_url);?>
  <?php echo Form::hidden('current_url', $current_url);?>
  <?php echo Form::hidden('master_url', $master_url);?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('select_carrying', null);?>
  <?php echo Form::hidden('list_no', null);?>

  <?php /* フォームデータ */ ?>
  <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'hidden_processing_division'));?>
  <?php echo Form::hidden('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], array('id' => 'hidden_division_code'));?>
  <?php echo Form::hidden('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number']:'', array('id' => 'hidden_dispatch_number'));?>
  <?php if (!is_array($data['list'])): ?>
    <?php echo Form::hidden('list', $data['list']);?>
  <?php else: ?>
    <?php foreach ($data['list'] as $key_cnt => $v): ?>
      <?php foreach ($v as $key => $val): ?>
        <?php if ($key == 'carrying'): ?>
          <?php foreach ($val as $carryingcnt => $carryingdata): ?>
            <?php foreach ($carryingdata as $carryingkey => $carryingval): ?>
              <?php if ($carryingkey == 'car_model_code') : ?>
                <?php echo Form::hidden('list['.$key_cnt.']['.$key.']['.$carryingcnt.']['.$carryingkey.']', (!empty($carryingval)) ? $carryingval:'1'
                , array('id' => 'hidden_list_'.$key_cnt.'_carrying_'.$carryingcnt.'_'.$carryingkey));?>
              <?php else : ?>
                <?php echo Form::hidden('list['.$key_cnt.']['.$key.']['.$carryingcnt.']['.$carryingkey.']', $carryingval
                , array('id' => 'hidden_list_'.$key_cnt.'_carrying_'.$carryingcnt.'_'.$carryingkey));?>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <?php if ($key == 'car_model_code') : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', (!empty($val)) ? $val:'1', array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php elseif ($key == 'product_code') : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', (!empty($val)) ? $val:'1', array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php elseif ($key == 'delivery_category') : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', (!empty($val)) ? $val:'1', array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php elseif ($key == 'tax_category') : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', (!empty($val)) ? $val:'1', array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php elseif ($key == 'stack_date' || $key == 'drop_date') : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', (!empty($val)) ? $val:date('Y-m-d'), array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php else : ?>
            <?php echo Form::hidden('list['.$key_cnt.']['.$key.']', $val, array('id' => 'hidden_list_'.$key_cnt.'_'.$key));?>
          <?php endif; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>

    <script>
        var processing_division_list    = '<?php echo json_encode($processing_division_list); ?>';
        var division_list               = '<?php echo json_encode($division_list); ?>';
        var position_list               = '<?php echo json_encode($position_list); ?>';
        var product_list                = '<?php echo json_encode($product_list); ?>';
        var car_model_list              = '<?php echo json_encode($car_model_list); ?>';
        var delivery_category_list      = '<?php echo json_encode($delivery_category_list); ?>';
        var tax_category_list           = '<?php echo json_encode($tax_category_list); ?>';
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var error_msg1                  = '<?php echo Config::get('m_MW0013'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_DI0001'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_DI0002'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_DI0003'); ?>';
        var processing_msg4             = '<?php echo Config::get('m_DW0017'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row" style="margin-bottom: 20px;">
      <?php /* ?>
      <label class="item-name">処理区分</label>
      <?php echo Form::select('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', $processing_division_list,
          array('class' => 'select-item', 'id' => 'processing_division', 'tabindex' => '1')); ?>
      <?php */ ?>
      <?php echo Form::hidden('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'processing_division'));?>
      <label class="item-name">課</label>
      <?php echo Form::select('dispatch_division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'onchange' => 'change()', 'tabindex' => '2')); ?>

      <input type="button" name="search" value="配車履歴引用" class='buttonB' tabindex="3" onclick="onDispatchCharterSearch('<?php echo Uri::create('search/s0080'); ?>', 0)" style="margin-left: 60px;" />
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
    </div>
    <div class="content-row" style="margin-bottom: 40px;">
      <div class="tab-contents">
        <input id="tab1" type="radio" name="tab-radio" checked="checked">
        <input id="tab2" type="radio" name="tab-radio">
        <input id="tab3" type="radio" name="tab-radio">
        <label class="tab-item" id="tab-item1" for="tab1">配車登録</label>
        <label class="tab-item" id="tab-item2" for="tab2">売上登録</label>
        <label class="tab-item" id="tab-item3" for="tab3">その他</label>
        <div class="tab-content" id="tab-content1" style="margin-top: 60px;">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>請求売上<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">傭車先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">車番<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>運転手<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>電話番号</th>
                    <th rowspan="2" style="width: 150px;">備考</th>
                  </tr>
                  <tr>
                    <th>積地</th>
                    <th>降地</th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th colspan="2" style="width: 300px;">傭車先</th>
                    <th>運行先</th>
                    <th>分載</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 5;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('sales_status_'.$i, (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_0_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_0_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 積日 */ ?>
                    <td>
                      <?php echo Form::input('stack_date_'.$i, (!empty($data['list'][$i]['stack_date'])) ? $data['list'][$i]['stack_date']:'', array('type' => 'date', 'id' => 'stack_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 降日 */ ?>
                    <td>
                      <?php echo Form::input('drop_date_'.$i, (!empty($data['list'][$i]['drop_date'])) ? $data['list'][$i]['drop_date']:'', array('type' => 'date', 'id' => 'drop_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('client_code_'.$i, (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'04')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>05" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 車種 */ ?>
                    <td>
                      <?php echo Form::select('car_model_code_'.$i, (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'1', $car_model_list, array('id' => 'car_model_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'maxlength' => '3', 'tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 請求売上 */ ?>
                    <td>
                      <?php echo Form::input('claim_sales_'.$i, (!empty($data['list'][$i]['claim_sales'])) ? $data['list'][$i]['claim_sales']:0, array('id' => 'claim_sales_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 傭車先No */ ?>
                    <td>
                      <?php echo Form::input('carrier_code_'.$i, (!empty($data['list'][$i]['carrier_code'])) ? sprintf('%05d', $data['list'][$i]['carrier_code']):'', array('id' => 'carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5', 'id' => 'carrier_code_'.$i,'tabindex' => $i.'09')); ?>
                      <input type="button" name="s_carrier_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>10" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 車番 */ ?>
                    <td>
                      <?php echo Form::input('car_code_'.$i, (!empty($data['list'][$i]['car_code'])) ? sprintf('%04d', $data['list'][$i]['car_code']):'', array('id' => 'car_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '4','tabindex' => $i.'11')); ?>
                      <input type="button" name="s_car_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>12" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 運転手 */ ?>
                    <td>
                      <?php echo Form::input('driver_name_'.$i, (!empty($data['list'][$i]['driver_name'])) ? $data['list'][$i]['driver_name']:'', array('id' => 'driver_name_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','tabindex' => $i.'13')); ?>
                      <input type="button" name="s_driver_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>14" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 電話番号 */ ?>
                    <td>
                      <?php echo Form::input('phone_number_'.$i, (!empty($data['list'][$i]['phone_number'])) ? $data['list'][$i]['phone_number']:'', array('id' => 'phone_number_'.$i, 'class' => 'input-text','tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 備考 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('remarks_'.$i, (!empty($data['list'][$i]['remarks'])) ? $data['list'][$i]['remarks']:'', array('id' => 'remarks_'.$i, 'class' => 'input-text', 'style' => 'width:200px;', 'maxlength' => '15', 'tabindex' => $i.'16')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 積地 */ ?>
                    <td>
                      <?php echo Form::input('stack_place_'.$i, (!empty($data['list'][$i]['stack_place'])) ? $data['list'][$i]['stack_place']:'', array('id' => 'stack_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 降地 */ ?>
                    <td>
                      <?php echo Form::input('drop_place_'.$i, (!empty($data['list'][$i]['drop_place'])) ? $data['list'][$i]['drop_place']:'', array('id' => 'drop_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 得意先 */ ?>
                    <td colspan="2">
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;')); ?>
                      <?php echo Form::hidden('client_name_'.$i, (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 傭車支払 */ ?>
                    <td>
                      <?php echo Form::input('carrier_payment_'.$i, (!empty($data['list'][$i]['carrier_payment'])) ? $data['list'][$i]['carrier_payment']:0, array('id' => 'carrier_payment_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 傭車先 */ ?>
                    <td colspan="2">
                      <?php echo Form::label((!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', 'list['.$i.'][carrier_name]', array('id' => 'carrier_name_'.$i, 'style' => 'display:inline;')); ?>
                      <?php echo Form::hidden('carrier_name_'.$i, (!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', array('id' => 'carrier_name_'.$i));?>
                    </td>
                    <?php /* 運行先 */ ?>
                    <td>
                      <?php echo Form::input('destination_'.$i, (!empty($data['list'][$i]['destination'])) ? $data['list'][$i]['destination']:'', array('id' => 'destination_'.$i, 'class' => 'input-text', 'tabindex' => $i.'19')); ?>
                    </td>
                    <?php /* 分載 */ ?>
                    <td>
                      <input type="button" name="s_carrying_<?php echo $i; ?>" value="入力" class='buttonA' tabindex="<?php echo $i; ?>20" id="s_carrying_<?php echo $i; ?>" style="margin-right: 10px;" />
                      <?php echo Form::label((!empty($data['list'][$i]['carrying_count'])) ? $data['list'][$i]['carrying_count'].'台':'分載なし', 'list['.$i.'][carrying_count]', array('id' => 'carrying_count_'.$i, 'style' => 'display:inline;')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-content" id="tab-content2" style="margin-top: 60px;">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>請求売上<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th rowspan="2" style="width: 180px;">商品<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th rowspan="2" style="width: 120px;">配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th rowspan="2" style="width: 120px;">税区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>請求高速料金<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th rowspan="2">ドライバー<br>高速料金<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                  </tr>
                  <tr>
                    <th>積地<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降地<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>傭車高速料金<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 5;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('sales_status_'.$i, (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_1_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_1_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 積日 */ ?>
                    <td>
                      <?php echo Form::input('stack_date_'.$i, (!empty($data['list'][$i]['stack_date'])) ? $data['list'][$i]['stack_date']:'', array('type' => 'date', 'id' => 'stack_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 降日 */ ?>
                    <td>
                      <?php echo Form::input('drop_date_'.$i, (!empty($data['list'][$i]['drop_date'])) ? $data['list'][$i]['drop_date']:'', array('type' => 'date', 'id' => 'drop_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('client_code_'.$i, (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5','tabindex' => $i.'04')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>05" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 車種 */ ?>
                    <td>
                      <?php echo Form::select('car_model_code_'.$i, (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'1', $car_model_list, array('id' => 'car_model_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'maxlength' => '3', 'tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 請求売上 */ ?>
                    <td>
                      <?php echo Form::input('claim_sales_'.$i, (!empty($data['list'][$i]['claim_sales'])) ? $data['list'][$i]['claim_sales']:0, array('id' => 'claim_sales_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 商品 */ ?>
                    <td rowspan="2">
                      <?php echo Form::select('product_code_'.$i, (!empty($data['list'][$i]['product_code'])) ? $data['list'][$i]['product_code']:'1', $product_list, array('class' => 'select-item', 'style' => 'width:140px;','maxlength' => '4', 'id' => 'product_code_'.$i,'tabindex' => $i.'06')); ?>
                      <input type="button" name="s_product_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>07" onclick="onProductSearch('<?php echo Uri::create('search/s0060'); ?>', <?php echo $i; ?>)" />
                    </td rowspan="2">
                    <?php /* 配送区分 */ ?>
                    <td rowspan="2">
                      <?php echo Form::select('delivery_category_'.$i, $data['list'][$i]['delivery_category'], $delivery_category_list, array('id' => 'delivery_category_'.$i, 'class' => 'select-item','maxlength' => '3', 'tabindex' => $i.'09')); ?>
                    </td rowspan="2">
                    <?php /* 税区分 */ ?>
                    <td rowspan="2">
                      <?php echo Form::select('tax_category_'.$i, $data['list'][$i]['tax_category'], $tax_category_list, array('id' => 'tax_category_'.$i, 'class' => 'select-item','maxlength' => '3', 'tabindex' => $i.'10')); ?>
                    </td>
                    <?php /* 請求高速料金＆請求高速料金請求有無 */ ?>
                    <td>
                      <?php echo Form::input('claim_highway_fee_'.$i, (!empty($data['list'][$i]['claim_highway_fee'])) ? $data['list'][$i]['claim_highway_fee']:0, array('id' => 'claim_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'17')); ?>
                      <?php echo Form::checkbox('claim_highway_claim_'.$i, (!empty($data['list'][$i]['claim_highway_claim'])) ? $data['list'][$i]['claim_highway_claim']:'1', ($data['list'][$i]['claim_highway_claim'] == '2') ? true:false, array('id' => 'form_claim_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'18')); ?>
                      <?php echo Form::label('', 'claim_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                    </td>
                    <?php /* ドライバー高速料金＆ドライバー高速料金請求有無 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('driver_highway_fee_'.$i, (!empty($data['list'][$i]['driver_highway_fee'])) ? $data['list'][$i]['driver_highway_fee']:0, array('id' => 'driver_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'13')); ?>
                      <?php echo Form::checkbox('driver_highway_claim_'.$i, (!empty($data['list'][$i]['driver_highway_claim'])) ? $data['list'][$i]['driver_highway_claim']:'1', ($data['list'][$i]['driver_highway_claim'] == '2') ? true:false, array('id' => 'form_driver_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'14')); ?>
                      <?php echo Form::label('', 'driver_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 積地 */ ?>
                    <td>
                      <?php echo Form::input('stack_place_'.$i, (!empty($data['list'][$i]['stack_place'])) ? $data['list'][$i]['stack_place']:'', array('id' => 'stack_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 降地 */ ?>
                    <td>
                      <?php echo Form::input('drop_place_'.$i, (!empty($data['list'][$i]['drop_place'])) ? $data['list'][$i]['drop_place']:'', array('id' => 'drop_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 得意先 */ ?>
                    <td colspan="2">
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;')); ?>
                      <?php echo Form::hidden('client_name_'.$i, (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 傭車支払 */ ?>
                    <td>
                      <?php echo Form::input('carrier_payment_'.$i, (!empty($data['list'][$i]['carrier_payment'])) ? $data['list'][$i]['carrier_payment']:0, array('id' => 'carrier_payment_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 庸車高速料金＆庸車高速料金請求有無 */ ?>
                    <td>
                      <?php echo Form::input('carrier_highway_fee_'.$i, (!empty($data['list'][$i]['carrier_highway_fee'])) ? $data['list'][$i]['carrier_highway_fee']:0, array('id' => 'carrier_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'19')); ?>
                      <?php echo Form::checkbox('carrier_highway_claim_'.$i, (!empty($data['list'][$i]['carrier_highway_claim'])) ? $data['list'][$i]['carrier_highway_claim']:'1', ($data['list'][$i]['carrier_highway_claim'] == '2') ? true:false, array('id' => 'form_carrier_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'20')); ?>
                      <?php echo Form::label('', 'carrier_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-content" id="tab-content3" style="margin-top: 60px;">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 180px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>請求売上<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>手当<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>時間外<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>往復</th>
                    <th>受領書送付日</th>
                    <th rowspan="2">社内向け備考</th>
                  </tr>
                  <tr>
                    <th>積地<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降地<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>泊まり<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>連結・ラップ<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>降日計上</th>
                    <th>受領書受領日</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 5;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('sales_status_'.$i, (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_2_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_2_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 積日 */ ?>
                    <td>
                      <?php echo Form::input('stack_date_'.$i, (!empty($data['list'][$i]['stack_date'])) ? $data['list'][$i]['stack_date']:'', array('type' => 'date', 'id' => 'stack_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 降日 */ ?>
                    <td>
                      <?php echo Form::input('drop_date_'.$i, (!empty($data['list'][$i]['drop_date'])) ? $data['list'][$i]['drop_date']:'', array('type' => 'date', 'id' => 'drop_date_'.$i,'style' => 'width: 160px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('client_code_'.$i, (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5','tabindex' => $i.'04')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>05" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 車種 */ ?>
                    <td>
                      <?php echo Form::select('car_model_code_'.$i, (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'1', $car_model_list, array('id' => 'car_model_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'maxlength' => '3', 'tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 請求売上 */ ?>
                    <td>
                      <?php echo Form::input('claim_sales_'.$i, (!empty($data['list'][$i]['claim_sales'])) ? $data['list'][$i]['claim_sales']:0, array('id' => 'claim_sales_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 手当 */ ?>
                    <td>
                      <?php echo Form::input('allowance_'.$i, (!empty($data['list'][$i]['allowance'])) ? $data['list'][$i]['allowance']:0, array('id' => 'allowance_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'09')); ?>
                    </td>
                    <?php /* 時間外 */ ?>
                    <td>
                      <?php echo Form::input('overtime_fee_'.$i, (!empty($data['list'][$i]['overtime_fee'])) ? $data['list'][$i]['overtime_fee']:0, array('id' => 'overtime_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'09')); ?>
                    </td>
                    <?php /* 往復 */ ?>
                    <td>
                      <?php echo Form::checkbox('round_trip_'.$i, (!empty($data['list'][$i]['round_trip'])) ? $data['list'][$i]['round_trip']:'1', ($data['list'][$i]['round_trip'] == '2') ? true:false, array('id' => 'form_round_trip_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'11')); ?>
                      <?php echo Form::label('', 'round_trip_'.$i, array('style' => 'display:inline;margin-left:24px;padding-right: 0.05em !important;')); ?>
                    </td>
                    <?php /* 受領書送付日 */ ?>
                    <td>
                      <?php echo Form::input('receipt_send_date_'.$i, (!empty($data['list'][$i]['receipt_send_date'])) ? $data['list'][$i]['receipt_send_date']:'', array('type' => 'date', 'id' => 'receipt_send_date_'.$i, 'style' => 'width: 160px;', 'class' => 'input-date', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 社内向け備考 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('in_house_remarks_'.$i, (!empty($data['list'][$i]['in_house_remarks'])) ? $data['list'][$i]['in_house_remarks']:'', array('id' => 'in_house_remarks_'.$i, 'class' => 'input-text', 'style' => 'width:200px;', 'maxlength' => '30', 'tabindex' => $i.'13')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 積地 */ ?>
                    <td>
                      <?php echo Form::input('stack_place_'.$i, (!empty($data['list'][$i]['stack_place'])) ? $data['list'][$i]['stack_place']:'', array('id' => 'stack_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 降地 */ ?>
                    <td>
                      <?php echo Form::input('drop_place_'.$i, (!empty($data['list'][$i]['drop_place'])) ? $data['list'][$i]['drop_place']:'', array('id' => 'drop_place_'.$i, 'class' => 'input-text', 'style' => 'width:160px;', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 得意先 */ ?>
                    <td colspan="2">
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;')); ?>
                      <?php echo Form::hidden('client_name_'.$i, (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 傭車支払 */ ?>
                    <td>
                      <?php echo Form::input('carrier_payment_'.$i, (!empty($data['list'][$i]['carrier_payment'])) ? $data['list'][$i]['carrier_payment']:0, array('id' => 'carrier_payment_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'16')); ?>
                    </td>
                    <?php /* 泊まり */ ?>
                    <td>
                      <?php echo Form::input('stay_'.$i, (!empty($data['list'][$i]['stay'])) ? $data['list'][$i]['stay']:0, array('id' => 'stay_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'09')); ?>
                    </td>
                    <?php /* 連結・ラップ */ ?>
                    <td>
                      <?php echo Form::input('linking_wrap_'.$i, (!empty($data['list'][$i]['linking_wrap'])) ? $data['list'][$i]['linking_wrap']:0, array('id' => 'linking_wrap_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'tabindex' => $i.'10')); ?>
                    </td>
                    <?php /* 卸日計上 */ ?>
                    <td>
                      <?php echo Form::checkbox('drop_appropriation_'.$i, (!empty($data['list'][$i]['drop_appropriation'])) ? $data['list'][$i]['drop_appropriation']:'1', ($data['list'][$i]['drop_appropriation'] == '2') ? true:false, array('id' => 'form_drop_appropriation_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'17')); ?>
                      <?php echo Form::label('', 'drop_appropriation_'.$i, array('style' => 'display:inline;margin-left:24px;padding-right: 0.05em !important;')); ?>
                    </td>
                    <?php /* 受領書受領日 */ ?>
                    <td>
                      <?php echo Form::input('receipt_receive_date_'.$i, (!empty($data['list'][$i]['receipt_receive_date'])) ? $data['list'][$i]['receipt_receive_date']:'', array('type' => 'date', 'id' => 'receipt_receive_date_'.$i, 'style' => 'width: 160px;', 'class' => 'input-date', 'tabindex' => $i.'18')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="content-row">
          <?php echo Form::submit('execution', '確　　　定', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '900')); ?>
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '902')); ?>
        </div>
      </div>
    </div>
  <?php echo Form::close(); ?>
</main>

<?php /* 分載入力モーダル */ ?>
<section id="carrying_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalWrapper">

    <header id="header" style="margin-top: 40px;">
      <h1 class="page-title">分載入力</h1>
    </header>

    <main class="l-main">
      <?php echo Form::hidden('dispatch_list_no', '', array('id' => 'dispatch_list_no'));?>
      <?php echo Form::hidden('dispatch_claim_sales', '', array('id' => 'dispatch_claim_sales'));?>
      <?php echo Form::hidden('dispatch_carrier_payment', '', array('id' => 'dispatch_carrier_payment'));?>
      <?php echo Form::hidden('dispatch_claim_highway_fee', '', array('id' => 'dispatch_claim_highway_fee'));?>
      <?php echo Form::hidden('dispatch_carrier_highway_fee', '', array('id' => 'dispatch_carrier_highway_fee'));?>
      <?php echo Form::hidden('dispatch_driver_highway_fee', '', array('id' => 'dispatch_driver_highway_fee'));?>
      <script>
          var clear_msg     = '<?php echo Config::get('m_CI0005'); ?>';
          var error_msg1    = '<?php echo Config::get('m_MW0013'); ?>';
          // 分載データを確定しますか？
          var processing_carrying_msg1 = '<?php echo Config::get('m_DI0004'); ?>';
          // 分載データ入力を取消しますか？
          var processing_carrying_msg2 = '<?php echo Config::get('m_DI0005'); ?>';
          // 【DW0008】分載情報と配車情報の請求売上が一致しません
          var processing_carrying_msg3 = '<?php echo Config::get('m_DW0008'); ?>';
          // 【DW0009】分載情報と配車情報の庸車支払が一致しません
          var processing_carrying_msg4 = '<?php echo Config::get('m_DW0009'); ?>';
          // 【DW0010】分載情報は２件以上入力してください
          var processing_carrying_msg5 = '<?php echo Config::get('m_DW0010'); ?>';
          // 【DW0018】分載情報と配車情報の請求高速料金が一致しません
          var processing_carrying_msg6 = '<?php echo Config::get('m_DW0018'); ?>';
          // 【DW0019】分載情報と配車情報のドライバー高速料金が一致しません
          var processing_carrying_msg7 = '<?php echo Config::get('m_DW0019'); ?>';
          // 【DW0020】分載情報と配車情報の庸車先高速料金が一致しません
          var processing_carrying_msg8 = '<?php echo Config::get('m_DW0020'); ?>';

      </script>
      <div class="content-row">
        <label>■配車情報</label>
      </div>
      <div class="content-row" style="margin-bottom: 20px;">
        <div class="table-wrap">
          <table class="table-inq">
            <thead>
              <tr>
                <th>積日</th>
                <th>降日</th>
                <th style="width: 120px;">得意先No</th>
                <th>車種</th>
                <th>請求売上</th>
                <th style="width: 120px;">傭車先No</th>
                <th style="width: 120px;">車番</th>
                <th rowspan="2" style="width: 100px;">運転手</th>
                <th rowspan="2" style="width: 180px;">商品</th>
                <th>配送区分</th>
                <th>請求高速料金</th>
                <th rowspan="2">ドライバー<br>高速料金</th>
              </tr>
              <tr>
                <th>積地</th>
                <th>降地</th>
                <th colspan="2" style="width: 300px;">得意先</th>
                <th>傭車支払</th>
                <th colspan="2" style="width: 300px;">傭車先</th>
                <th>税区分</th>
                <th>傭車高速料金</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <span id="dispatch_stack_date"></span>
                </td>
                <td>
                  <span id="dispatch_drop_date"></span>
                </td>
                <td>
                  <span id="dispatch_client_code"></span>
                </td>
                <td>
                  <span id="dispatch_car_model_code"></span>
                </td>
                <td style="text-align: right;">
                  <span id="dispatch_claim_sales_view"></span>
                </td>
                <td>
                  <span id="dispatch_carrier_code"></span>
                </td>
                <td>
                  <span id="dispatch_car_code"></span>
                </td>
                <td rowspan="2">
                  <span id="dispatch_driver_name"></span>
                </td>
                <td rowspan="2">
                  <span id="dispatch_product_name"></span>
                </td>
                <td>
                  <span id="dispatch_delivery_category"></span>
                </td>
                <td style="text-align: right;">
                  <span id="dispatch_claim_highway_fee_view"></span>
                </td>
                <td rowspan="2" style="text-align: right;">
                  <span id="dispatch_driver_highway_fee_view"></span>
                </td>
              </tr>
              <tr>
                <td>
                  <span id="dispatch_stack_place"></span>
                </td>
                <td>
                  <span id="dispatch_drop_place"></span>
                </td>
                <td colspan="2">
                  <span id="dispatch_client_name"></span>
                </td>
                <td style="text-align: right;">
                  <span id="dispatch_carrier_payment_view"></span>
                </td>
                <td colspan="2">
                  <span id="dispatch_carrier_name"></span>
                </td>
                <td>
                  <span id="dispatch_tax_category"></span>
                </td>
                <td style="text-align: right;">
                  <span id="dispatch_carrier_highway_fee_view"></span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="content-row">
        <label>■分載情報</label>
      </div>
      <div class="content-row">
        <div class="table-wrap">
          <table class="table-mnt">
            <thead>
              <tr>
                <th>積日</th>
                <th>降日</th>
                <th style="width: 120px;">得意先No</th>
                <th>車種</th>
                <th>請求売上</th>
                <th style="width: 120px;">傭車先No</th>
                <th style="width: 170px;">車番</th>
                <th style="width: 150px;">運転手</th>
                <th>請求高速料金</th>
                <th rowspan="2" style="width: 170px;">ドライバー<br>高速料金</th>
              </tr>
              <tr>
                <th>積地</th>
                <th>降地</th>
                <th colspan="2" style="width: 300px;">得意先</th>
                <th>傭車支払</th>
                <th colspan="2" style="width: 300px;">傭車先</th>
                <th style="width: 170px;">電話番号</th>
                <th style="width: 170px;">傭車高速料金</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0;$i < 3;$i++) : ?>
              <tr>
                <?php /* 積日 */ ?>
                <td>
                  <?php echo Form::input('carrying_stack_date_'.$i, '', array('type' => 'date', 'id' => 'carrying_stack_date_'.$i, 'class' => 'input-text', 'style' => 'width:150px;','tabindex' => $i.'01')); ?>
                </td>
                <?php /* 降日 */ ?>
                <td>
                  <?php echo Form::input('carrying_drop_date_'.$i, '', array('type' => 'date', 'id' => 'carrying_drop_date_'.$i, 'class' => 'input-text', 'style' => 'width:150px;','tabindex' => $i.'02')); ?>
                </td>
                <?php /* 得意先 */ ?>
                <td>
                  <?php echo Form::input('carrying_client_code_'.$i, '', array('id' => 'carrying_client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5','tabindex' => $i.'02')); ?>
                  <input type="button" name="c_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>03" id="c_client_<?php echo $i; ?>" />
                </td>
                <?php /* 車種 */ ?>
                <td>
                  <?php echo Form::select('carrying_car_model_code_'.$i, 1, $car_model_list, array('id' => 'carrying_car_model_code_'.$i, 'class' => 'select-item','maxlength' => '3', 'tabindex' => $i.'03')); ?>
                </td>
                <?php /* 請求売上 */ ?>
                <td>
                  <?php echo Form::input('carrying_claim_sales_'.$i, '0', array('id' => 'carrying_claim_sales_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'tabindex' => $i.'04')); ?>
                </td>
                <?php /* 傭車先No */ ?>
                <td>
                  <?php echo Form::input('carrying_carrier_code_'.$i, '', array('id' => 'carrying_carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5','tabindex' => $i.'05')); ?>
                  <input type="button" name="c_carrier_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>03" id="c_carrier_<?php echo $i; ?>" />
                </td>
                <?php /* 車番 */ ?>
                <td>
                  <?php echo Form::input('carrying_car_code_'.$i, '', array('id' => 'carrying_car_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '4','tabindex' => $i.'06')); ?>
                  <input type="button" name="c_car_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>05" id="c_car_<?php echo $i; ?>" />
                </td>
                <?php /* 運転手 */ ?>
                <td>
                  <?php echo Form::input('carrying_driver_name_'.$i, '', array('id' => 'carrying_driver_name_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','tabindex' => $i.'07')); ?>
                  <input type="button" name="c_driver_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>07" id="c_driver_<?php echo $i; ?>" />
                  <?php echo Form::hidden('carrying_member_code_'.$i, '', array('id' => 'carrying_member_code_'.$i));?>
                </td>
                <?php /* 請求高速料金 */ ?>
                <td>
                  <?php echo Form::input('carrying_claim_highway_fee_'.$i, 0, array('id' => 'carrying_claim_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'tabindex' => $i.'08')); ?>
                  <?php echo Form::checkbox('carrying_claim_highway_claim_'.$i, '1', false, array('id' => 'form_carrying_claim_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'09')); ?>
                  <?php echo Form::label('', 'carrying_claim_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                </td>
                <?php /* ドライバー高速料金 */ ?>
                <td rowspan="2">
                  <?php echo Form::input('carrying_driver_highway_fee_'.$i, 0, array('id' => 'carrying_driver_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'tabindex' => $i.'10')); ?>
                  <?php echo Form::checkbox('carrying_driver_highway_claim_'.$i, '1', false, array('id' => 'form_carrying_driver_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'11')); ?>
                  <?php echo Form::label('', 'carrying_driver_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                </td>
              </tr>
              <tr>
                <?php /* 積地 */ ?>
                <td>
                  <?php echo Form::input('carrying_stack_place_'.$i, '', array('id' => 'carrying_stack_place_'.$i, 'class' => 'input-text', 'style' => 'width:150px;','tabindex' => $i.'12')); ?>
                </td>
                <?php /* 降地 */ ?>
                <td>
                  <?php echo Form::input('carrying_drop_place_'.$i, '', array('id' => 'carrying_drop_place_'.$i, 'class' => 'input-text', 'style' => 'width:150px;','tabindex' => $i.'13')); ?>
                </td>
                <?php /* 得意先 */ ?>
                <td colspan="2">
                  <?php echo Form::label('', 'carrying_client_name_'.$i, array('id' => 'carrying_client_name_'.$i, 'style' => 'display:inline;')); ?>
                  <?php echo Form::hidden('carrying_client_name_'.$i, '', array('id' => 'carrying_client_name_'.$i));?>
                </td>
                <?php /* 傭車支払 */ ?>
                <td>
                  <?php echo Form::input('carrying_carrier_payment_'.$i, '0', array('id' => 'carrying_carrier_payment_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'tabindex' => $i.'14')); ?>
                </td>
                <?php /* 傭車先 */ ?>
                <td colspan="2">
                  <?php echo Form::label('', 'carrying_carrier_name_'.$i, array('id' => 'carrying_carrier_name_'.$i, 'style' => 'display:inline;')); ?>
                  <?php echo Form::hidden('carrying_carrier_name_'.$i, '', array('id' => 'carrying_carrier_name_'.$i));?>
                </td>
                <?php /* 電話番号 */ ?>
                <td>
                  <?php echo Form::input('carrying_phone_number_'.$i, '', array('id' => 'carrying_phone_number_'.$i, 'class' => 'input-text','tabindex' => $i.'15')); ?>
                </td>
                <?php /* 傭車先高速料金 */ ?>
                <td>
                  <?php echo Form::input('carrying_carrier_highway_fee_'.$i, 0, array('id' => 'carrying_carrier_highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'tabindex' => $i.'08')); ?>
                  <?php echo Form::checkbox('carrying_carrier_highway_claim_'.$i, '1', false, array('id' => 'form_carrying_carrier_highway_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'09')); ?>
                  <?php echo Form::label('', 'carrying_carrier_highway_claim_'.$i, array('style' => 'display:inline;margin-left:10px;padding-right: 0.05em !important;')); ?>
                </td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="content-row">
        <?php echo Form::button('select', '確　　　定', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_submit', 'tabindex' => '900')); ?>
        <?php echo Form::button('cancel', '入力取消', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_cancel', 'tabindex' => '901')); ?>
        <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_back', 'tabindex' => '902')); ?>
      </div>
    </main>
  </div>
</section>

<?php /* 得意先検索モーダル */ ?>
<section id="carrying_client_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalClientWrapper">
    <section id="banner" style="padding-top:10px;">
      <div class="content" style="margin-top:0px;">
        <div class="content-row">
          <label>■得意先検索</label>
        </div>
        <div class="content-row">
          <div class="table-wrap">
            <?php echo Form::hidden('client_list_no', '', array('id' => 'client_list_no'));?>
            <table class="table-inq" style="width: 600px">
              <tr>
                <th style="width: 60px">選択</th>
                <th style="width: 120px">得意先コード</th>
                <th style="width: 160px">会社名</th>
                <th style="width: 100px">営業所名</th>
                <th style="width: 100px">部署名</th>
                <th style="width: 130px">締日</th>
              </tr>
              <?php if (!empty($client_list)) : ?>
                <?php foreach ($client_list as $key => $val) : ?>
                    <?php
                    //締日成形
                    $closing_date = closingdate::genClosingDate($val['closing_date'], $val['closing_date_1'], $val['closing_date_2'], $val['closing_date_3']);
                    ?>
                  <tr>
                    <td style="width: 60px; text-align: center;">
                      <?php echo Form::button(
                        'carrying_select_client_'.$key, '選択',
                        array(
                          'type'      => 'button',
                          'class'     => 'buttonA',
                          'id'        => 'carrying_select_client_'.$key,
                          'data-id'   => $val['client_code'],
                          'data-name' => $val['client_name'],
                          'tabindex'  => '900'
                        )); ?>
                    </td>
                    <td style="width: 120px"><?php echo sprintf('%05d', $val['client_code']); ?></td>
                    <td style="width: 160px"><?php echo $val['company_name']; ?></td>
                    <td style="width: 100px"><?php echo (empty($val['sales_office_name'])) ? "-" : $val['sales_office_name']; ?></td>
                    <td style="width: 100px"><?php echo (empty($val['department_name'])) ? "-" : $val['department_name']; ?></td>
                    <td><?php echo $closing_date; ?>日</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif ; ?>
            </table>
          </div>
          <div class="content-row">
            <?php echo Form::button('cancel', 'キャンセル', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_client_cancel', 'tabindex' => '901')); ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</section>

<?php /* 傭車先検索モーダル */ ?>
<section id="carrying_carrier_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalCarrierWrapper">
    <section id="banner" style="padding-top:10px;">
      <div class="content" style="margin-top:0px;">
        <div class="content-row">
          <label>■傭車先検索</label>
        </div>
        <div class="content-row">
          <div class="table-wrap">
            <?php echo Form::hidden('carrying_list_no', '', array('id' => 'carrying_list_no'));?>
            <table class="table-inq" style="width: 600px">
              <tr>
                <th style="width: 60px">選択</th>
                <th style="width: 120px">庸車先コード</th>
                <th style="width: 100px">会社区分</th>
                <th style="width: 160px">会社名</th>
                <th style="width: 100px">営業所名</th>
                <th style="width: 100px">部署名</th>
                <th style="width: 130px">締日</th>
              </tr>
              <?php if (!empty($carrier_list)) : ?>
                <?php foreach ($carrier_list as $key => $val) : ?>
                    <?php
                    //締日成形
                    $closing_date = closingdate::genClosingDate($val['closing_date'], $val['closing_date_1'], $val['closing_date_2'], $val['closing_date_3']);
                    ?>
                  <tr>
                    <td style="width: 60px; text-align: center;">
                      <?php echo Form::button(
                        'carrying_select_carrier_'.$key, '選択',
                        array(
                          'type'      => 'button',
                          'class'     => 'buttonA',
                          'id'        => 'carrying_select_carrier_'.$key,
                          'data-id'   => $val['carrier_code'],
                          'data-name' => $val['carrier_name'],
                          'tabindex'  => '900'
                        )); ?>
                    </td>
                    <td style="width: 120px"><?php echo sprintf('%05d', $val['carrier_code']); ?></td>
                    <td style="width: 100px"><?php echo $company_section_list[$val['company_section']]; ?></td>
                    <td style="width: 160px"><?php echo $val['company_name']; ?></td>
                    <td style="width: 100px"><?php echo (empty($val['sales_office_name'])) ? "-" : $val['sales_office_name']; ?></td>
                    <td style="width: 100px"><?php echo (empty($val['department_name'])) ? "-" : $val['department_name']; ?></td>
                    <td><?php echo $closing_date; ?>日</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif ; ?>
            </table>
          </div>
          <div class="content-row">
            <?php echo Form::button('cancel', 'キャンセル', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_carrier_cancel', 'tabindex' => '901')); ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</section>

<?php /* 車両検索モーダル */ ?>
<section id="carrying_car_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalCarWrapper">
    <section id="banner" style="padding-top:10px;">
      <div class="content" style="margin-top:0px;">
        <div class="content-row">
          <label>■車両検索</label>
        </div>
        <div class="content-row">
          <div class="table-wrap">
            <?php echo Form::hidden('carrying_list_no', '', array('id' => 'carrying_list_no'));?>
            <table class="table-inq" style="width: 720px">
              <tr>
                <th style="width: 60px">選択</th>
                <th style="width: 100px">車両コード</th>
                <th style="width: 120px">車種</th>
                <th style="width: 370px">車両名</th>
                <th style="width: 200px">車両番号</th>
              </tr>
              <?php if (!empty($car_list)) : ?>
                <?php foreach ($car_list as $key => $val) : ?>
                  <tr>
                    <td style="width: 60px; text-align: center;">
                      <?php echo Form::button(
                        'carrying_select_car_'.$key, '選択',
                        array(
                          'type'      => 'button',
                          'class'     => 'buttonA',
                          'id'        => 'carrying_select_car_'.$key,
                          'data-id'   => $val['car_code'],
                          'data-name' => $val['car_number'],
                          'tabindex'  => '900'
                        )); ?>
                    </td>
                    <td style="width: 100px"><?php echo sprintf('%04d', $val['car_code']); ?></td>
                    <td style="width: 120px"><?php echo $val['car_model_name']; ?></td>
                    <td style="width: 370px"><?php echo $val['car_name']; ?></td>
                    <td><?php echo $val['car_number']; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif ; ?>
            </table>
          </div>
          <div class="content-row">
            <?php echo Form::button('cancel', 'キャンセル', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_car_cancel', 'tabindex' => '901')); ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</section>

<?php /* ドライバー検索モーダル */ ?>
<section id="carrying_driver_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalDriverWrapper">
    <section id="banner" style="padding-top:10px;">
      <div class="content" style="margin-top:0px;">
        <div class="content-row">
          <label>■ドライバー検索</label>
        </div>
        <div class="content-row">
          <div class="table-wrap">
            <?php echo Form::hidden('carrying_list_no', '', array('id' => 'carrying_list_no'));?>
            <table class="table-inq" style="width: 1100px">
              <tr>
                <th style="width: 60px">選択</th>
                <th style="width: 110px">社員コード</th>
                <th style="width: 190px">氏名</th>
                <th style="width: 270px">ふりがな</th>
                <th style="width: 80px">課</th>
                <th style="width: 90px">役職</th>
                <th style="width: 160px">車両番号</th>
                <th style="width: 120px">ドライバー名</th>
                <th style="width: 200px">電話番号</th>
              </tr>
              <?php if (!empty($driver_list)) : ?>
                <?php foreach ($driver_list as $key => $val) : ?>
                  <tr>
                    <td style="width: 60px; text-align: center;">
                      <?php echo Form::button(
                        'carrying_select_driver_'.$key, '選択',
                        array(
                          'type'       => 'button',
                          'class'      => 'buttonA',
                          'id'         => 'carrying_select_driver_'.$key,
                          'data-id'    => $val['member_code'],
                          'data-name'  => $val['driver_name'],
                          'data-phone' => $val['phone_number'],
                          'tabindex'   => '900'
                        )); ?>
                    </td>
                    <td style="width: 110px"><?php echo sprintf('%05d', $val['member_code']); ?></td>
                    <td style="width: 190px"><?php echo $val['full_name']; ?></td>
                    <td style="width: 270px"><?php echo $val['name_furigana']; ?></td>
                    <td style="width: 80px"><?php echo $val['division']; ?></td>
                    <td style="width: 90px"><?php echo $val['position']; ?></td>
                    <td style="width: 160px"><?php echo (empty($val['car_number'])) ? "-" : $val['car_number']; ?></td>
                    <td style="width: 120px"><?php echo (empty($val['driver_name'])) ? "-" : $val['driver_name']; ?></td>
                    <td><?php echo (empty($val['phone_number'])) ? "-" : $val['phone_number']; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif ; ?>
            </table>
          </div>
          <div class="content-row">
            <?php echo Form::button('cancel', 'キャンセル', array('type' => 'button', 'class' => 'buttonB', 'id' => 'carrying_driver_cancel', 'tabindex' => '901')); ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</section>
