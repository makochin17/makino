<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('list_url', $list_url);?>
  <?php echo Form::hidden('current_url', $current_url);?>
  <?php echo Form::hidden('master_url', $master_url);?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('list_no', null);?>

  <?php /* フォームデータ */ ?>
  <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'2', array('id' => 'hidden_processing_division'));?>
  <?php echo Form::hidden('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], array('id' => 'hidden_division_code'));?>
  <?php echo Form::hidden('bill_number', (!empty($data['bill_number'])) ? $data['bill_number']:'', array('id' => 'hidden_bill_number'));?>
    <script>
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_BI0001'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_BI0002'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_BI0003'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row" style="margin-bottom: 20px;">
      <?php echo Form::hidden('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'2', array('id' => 'processing_division'));?>
      <label class="item-name">課</label>
      <?php echo Form::select('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'onchange' => 'change()', 'tabindex' => '1')); ?>
    </div>
    <div class="content-row" style="margin-bottom: 40px;">
      <div class="tab-contents">
        <input id="tab4" type="radio" name="tab-radio" checked="checked">
        <input id="tab5" type="radio" name="tab-radio">
        <label class="tab-item" id="tab-item4" for="tab4">配車情報</label>
        <label class="tab-item" id="tab-item5" for="tab5">請求情報</label>
        <div class="tab-content" id="tab-content4" style="margin-top: 60px;">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th rowspan="2" style="width: 50px;">売上<br>確定</th>
                    <th style="width: 80px;">配送区分</th>
                    <th style="width: 80px;">地区</th>
                    <th style="width: 80px;">運行日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 90px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 300px;">得意先名</th>
                    <th style="width: 80px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 80px;">車両番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 80px;">運転手<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 80px;">依頼者</th>
                    <th style="width: 80px;">問い合わせNo</th>
                  </tr>
                  <tr>
                    <th colspan="3">運行先</th>
                    <th style="width: 90px;">傭車先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 300px;">傭車先名</th>
                    <th style="width: 80px;">現場</th>
                    <th style="width: 80px;">納品先住所</th>
                    <th style="width: 100px;">備考1</th>
                    <th style="width: 100px;">備考2</th>
                    <th style="width: 100px;">備考3</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 1;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 配送No */ ?>
                    <?php echo Form::hidden('list['.$i.'][dispatch_number]', (!empty($data['list'][$i]['dispatch_number'])) ? $data['list'][$i]['dispatch_number']:'', array('id' => 'dispatch_number_'.$i));?>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][sales_status]', (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'1', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_0_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_0_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 配送区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][delivery_code]', $data['list'][$i]['delivery_code'], $delivery_category_list, array('id' => 'delivery_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;','maxlength' => '3', 'tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 地区 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][area_code]', $data['list'][$i]['area_code'], $area_list, array('id' => 'area_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;','maxlength' => '3', 'tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* 運行日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][destination_date]', (!empty($data['list'][$i]['destination_date'])) ? $data['list'][$i]['destination_date']:'', array('type' => 'date', 'id' => 'destination_date_'.$i,'class' => 'input-date','style' => 'width: 140px;','maxlength' => '20','tabindex' => $i.'04')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'06')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>11" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 得意先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;font-size:12px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>

                    <?php /* 車種 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][car_model_code]', (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'1', $car_model_list, array('id' => 'car_model_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 車両番号 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][car_code]', (!empty($data['list'][$i]['car_code'])) ? sprintf('%04d', $data['list'][$i]['car_code']):'', array('id' => 'car_code_'.$i, 'class' => 'input-text', 'style' => 'width:60px;','maxlength' => '4','tabindex' => $i.'10')); ?>
                      <input type="button" name="s_car_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>21" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 運転手 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][driver_name]', (!empty($data['list'][$i]['driver_name'])) ? $data['list'][$i]['driver_name']:'', array('id' => 'driver_name_'.$i, 'class' => 'input-text', 'style' => 'width:100px;','tabindex' => $i.'11')); ?>
                      <input type="button" name="s_driver_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>24" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', <?php echo $i; ?>)" />
                      <?php echo Form::hidden('list['.$i.'][member_code]', (!empty($data['list'][$i]['member_code'])) ? $data['list'][$i]['member_code']:'', array('id' => 'member_code_'.$i));?>
                    </td>
                    <?php /* 依頼者 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][requester]', (!empty($data['list'][$i]['requester'])) ? $data['list'][$i]['requester']:'', array('id' => 'requester_'.$i, 'class' => 'input-text', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 問い合わせNo */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][inquiry_no]', (!empty($data['list'][$i]['inquiry_no'])) ? $data['list'][$i]['inquiry_no']:'', array('id' => 'inquiry_no_'.$i, 'class' => 'input-text', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => $i.'13')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 運行先 */ ?>
                    <td colspan="3">
                      <?php echo Form::input('list['.$i.'][destination]', (!empty($data['list'][$i]['destination'])) ? $data['list'][$i]['destination']:'', array('id' => 'destination_'.$i, 'class' => 'input-text', 'style' => 'width:360px;','maxlength' => '30','tabindex' => $i.'05')); ?>
                    </td>
                    <?php /* 傭車先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][carrier_code]', (!empty($data['list'][$i]['carrier_code'])) ? sprintf('%05d', $data['list'][$i]['carrier_code']):'', array('id' => 'carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5', 'tabindex' => $i.'07')); ?>
                      <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="7" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 傭車先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', 'list['.$i.'][carrier_name]', array('id' => 'carrier_name_'.$i, 'style' => 'display:inline;font-size:12px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][carrier_name]', (!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', array('id' => 'carrier_name_'.$i));?>
                    </td>

                    <?php /* 現場 */ ?>
                    <td style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][onsite_flag]', (!empty($data['list'][$i]['onsite_flag'])) ? $data['list'][$i]['onsite_flag']:'0', ($data['list'][$i]['onsite_flag'] == '1') ? true:false, array('id' => 'form_onsite_flag_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'09')); ?>
                      <?php echo Form::label('', 'onsite_flag_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 納品先住所 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][delivery_address]', (!empty($data['list'][$i]['delivery_address'])) ? $data['list'][$i]['delivery_address']:'', array('id' => 'delivery_address_'.$i, 'class' => 'input-text', 'style' => 'width:120px;', 'maxlength' => '40', 'tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 備考1 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][remarks1]', (!empty($data['list'][$i]['remarks1'])) ? $data['list'][$i]['remarks1']:'', array('id' => 'remarks1_'.$i, 'class' => 'input-text', 'style' => 'width:158px;', 'maxlength' => '15', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 備考2 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][remarks2]', (!empty($data['list'][$i]['remarks2'])) ? $data['list'][$i]['remarks2']:'', array('id' => 'remarks2_'.$i, 'class' => 'input-text', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 備考3 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][remarks3]', (!empty($data['list'][$i]['remarks3'])) ? $data['list'][$i]['remarks3']:'', array('id' => 'remarks3_'.$i, 'class' => 'input-text', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => $i.'14')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-content" id="tab-content5" style="margin-top: 60px;">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th rowspan="2" style="width: 50px;">売上<br>確定</th>
                    <th style="width: 80px;">配送区分</th>
                    <th style="width: 80px;">地区</th>
                    <th style="width: 80px;">運行日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 90px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 300px;">得意先名</th>
                    <th style="width: 90px;">金額<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 80px;">単価</th>
                    <th style="width: 80px;">数量<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 90px;">単位</th>
                  </tr>
                  <tr>
                    <th colspan="3">運行先</th>
                    <th style="width: 90px;">傭車先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 300px;">傭車先名</th>
                    <th colspan="3">商品名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 90px;">端数処理</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 1;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 配送No */ ?>
                    <?php echo Form::hidden('list['.$i.'][dispatch_number]', (!empty($data['list'][$i]['dispatch_number'])) ? $data['list'][$i]['dispatch_number']:'', array('id' => 'dispatch_number_'.$i));?>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][sales_status]', (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'1', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_1_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_1_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 配送区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][delivery_code]', $data['list'][$i]['delivery_code'], $delivery_category_list, array('id' => 'delivery_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;','maxlength' => '3', 'tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 地区 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][area_code]', $data['list'][$i]['area_code'], $area_list, array('id' => 'area_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;','maxlength' => '3', 'tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* 運行日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][destination_date]', (!empty($data['list'][$i]['destination_date'])) ? $data['list'][$i]['destination_date']:'', array('type' => 'date', 'id' => 'destination_date_'.$i,'class' => 'input-date','style' => 'width: 140px;','maxlength' => '20','tabindex' => $i.'04')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'06')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>11" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 得意先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;font-size:12px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>

                    <?php /* 金額 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][price]', (!empty($data['list'][$i]['price'])) ? $data['list'][$i]['price']:'0', array('id' => 'price_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 単価 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][unit_price]', (!empty($data['list'][$i]['unit_price'])) ? $data['list'][$i]['unit_price']:'0.00', array('id' => 'unit_price_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 数量 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][volume]', (!empty($data['list'][$i]['volume'])) ? floatval($data['list'][$i]['volume']):'0.00', array('id' => 'volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][unit_code]', (!empty($data['list'][$i]['unit_code'])) ? $data['list'][$i]['unit_code']:'1', $unit_list, array('id' => 'unit_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'17')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 運行先 */ ?>
                    <td colspan="3">
                      <?php echo Form::input('list['.$i.'][destination]', (!empty($data['list'][$i]['destination'])) ? $data['list'][$i]['destination']:'', array('id' => 'destination_'.$i, 'class' => 'input-text', 'style' => 'width:360px;','maxlength' => '30','tabindex' => $i.'05')); ?>
                    </td>
                    <?php /* 傭車先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][carrier_code]', (!empty($data['list'][$i]['carrier_code'])) ? sprintf('%05d', $data['list'][$i]['carrier_code']):'', array('id' => 'carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5', 'tabindex' => $i.'07')); ?>
                      <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="7" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 傭車先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', 'list['.$i.'][carrier_name]', array('id' => 'carrier_name_'.$i, 'style' => 'display:inline;font-size:12px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][carrier_name]', (!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', array('id' => 'carrier_name_'.$i));?>
                    </td>

                    <?php /* 商品名 */ ?>
                    <td colspan="3">
                      <?php echo Form::input('list['.$i.'][product_name]', (!empty($data['list'][$i]['product_name'])) ? $data['list'][$i]['product_name']:'', array('id' => 'product_name_'.$i, 'class' => 'input-text', 'style' => 'width:320px;','maxlength' => '30','tabindex' => $i.'16')); ?>
                    </td>
                    <?php /* 端数処理 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][rounding_code]', (!empty($data['list'][$i]['rounding_code'])) ? $data['list'][$i]['rounding_code']:'1', $rounding_list, array('id' => 'rounding_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'18')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="content-row">
          <?php echo Form::submit('execution', '更　　　新', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(2)', 'tabindex' => '900')); ?>
          <?php echo Form::submit('execution', '削　　　除', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(3)', 'tabindex' => '901')); ?>
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '902')); ?>
        </div>
      </div>
    </div>
  <?php echo Form::close(); ?>
</main>