<?php use \Model\Common\closingdate; ?>
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
  <?php echo Form::hidden('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number']:'', array('id' => 'hidden_dispatch_number'));?>
    <script>
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var error_msg1                  = '<?php echo Config::get('m_MW0013'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_DI0001'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_DI0002'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_DI0003'); ?>';
        var processing_msg4             = '<?php echo Config::get('m_DW0017'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row">
      <?php /* ?>
      <label class="item-name">処理区分</label>
      <?php echo Form::select('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', $processing_division_list,
          array('class' => 'select-item', 'id' => 'processing_division', 'tabindex' => '1')); ?>
      <?php */ ?>
      <?php echo Form::hidden('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'processing_division'));?>
      <label class="item-name">課</label>
      <?php echo Form::select('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'onchange' => 'change()', 'tabindex' => '1')); ?>
    </div>
    <div class="content-row" style="margin-bottom: 40px;">
      <div class="tab-contents">
        <input id="tab1" type="radio" name="tab-radio" checked="checked">
        <input id="tab2" type="radio" name="tab-radio">
        <label class="tab-item" id="tab-item1" for="tab1">配車情報</label>
        <label class="tab-item" id="tab-item2" for="tab2">その他</label>

        <div class="tab-content" id="tab-content1">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th>配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>地区<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>納品日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>納品先</th>
                    <th>数量<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>単位<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>

                    <th style="width: 140px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th><div style="display: inline-block;width: 280px;">得意先名</div></th>
                    <th style="width: 100px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 120px;">車両番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 140px;">運転手<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                  </tr>
                  <tr>
                    <th>配車区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>コース</th>
                    <th>引取日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>引取先</th>
                    <th colspan="2">商品名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>

                    <th>傭車先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>傭車先名</th>
                    <th style="width: 80px;">現場</th>
                    <th colspan="2">納品先住所</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 1;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 配送区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][delivery_code]', (!empty($data['list'][$i]['delivery_code'])) ? $data['list'][$i]['delivery_code']:'1', $delivery_list, array('id' => 'delivery_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 地区 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][area_code]', (!empty($data['list'][$i]['area_code'])) ? $data['list'][$i]['area_code']:'1', $area_list, array('id' => 'area_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'04')); ?>
                    </td>
                    <?php /* 納品日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][delivery_date]', (!empty($data['list'][$i]['delivery_date'])) ? $data['list'][$i]['delivery_date']:'', array('type' => 'date', 'id' => 'delivery_date_'.$i,'style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'06')); ?>
                    </td>
                    <?php /* 納品先 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][delivery_place]', (!empty($data['list'][$i]['delivery_place'])) ? $data['list'][$i]['delivery_place']:'', array('id' => 'delivery_place_'.$i, 'class' => 'input-text', 'style' => 'width:180px;','maxlength' => '30','tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 数量 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][volume]', (!empty($data['list'][$i]['volume'])) ? floatval($data['list'][$i]['volume']):'0.00', array('id' => 'volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][unit_code]', (!empty($data['list'][$i]['unit_code'])) ? $data['list'][$i]['unit_code']:'1', $unit_list, array('id' => 'unit_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>

                    <?php /* 得意先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:70px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'10')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>11" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 得意先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;font-size:14px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 車種 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][car_model_code]', (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'1', $car_model_list, array('id' => 'car_model_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'maxlength' => '3', 'tabindex' => $i.'18')); ?>
                    </td>
                    <?php /* 車両番号 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][car_code]', (!empty($data['list'][$i]['car_code'])) ? sprintf('%04d', $data['list'][$i]['car_code']):'', array('id' => 'car_code_'.$i, 'class' => 'input-text', 'style' => 'width:60px;','maxlength' => '4','tabindex' => $i.'20')); ?>
                      <input type="button" name="s_car_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>21" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 運転手 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][driver_name]', (!empty($data['list'][$i]['driver_name'])) ? $data['list'][$i]['driver_name']:'', array('id' => 'driver_name_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','tabindex' => $i.'23')); ?>
                      <input type="button" name="s_driver_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>24" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', <?php echo $i; ?>)" />
                      <?php echo Form::hidden('list['.$i.'][member_code]', (!empty($data['list'][$i]['member_code'])) ? $data['list'][$i]['member_code']:'', array('id' => 'member_code_'.$i));?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 配車区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][dispatch_code]', (!empty($data['list'][$i]['dispatch_code'])) ? $data['list'][$i]['dispatch_code']:'1', $dispatch_list, array('id' => 'dispatch_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* コース */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][course]', (!empty($data['list'][$i]['course'])) ? $data['list'][$i]['course']:'', array('id' => 'course_'.$i, 'class' => 'input-text', 'style' => 'width:90px;','maxlength' => '5','tabindex' => $i.'05')); ?>
                    </td>
                    <?php /* 引取日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][pickup_date]', (!empty($data['list'][$i]['pickup_date'])) ? $data['list'][$i]['pickup_date']:'', array('type' => 'date', 'id' => 'pickup_date_'.$i,'style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'07')); ?>
                    </td>
                    <?php /* 引取先 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][pickup_place]', (!empty($data['list'][$i]['pickup_place'])) ? $data['list'][$i]['pickup_place']:'', array('id' => 'pickup_place_'.$i, 'class' => 'input-text', 'style' => 'width:180px;','maxlength' => '30','tabindex' => $i.'09')); ?>
                    </td>
                    <?php /* 商品名 */ ?>
                    <td colspan="2">
                      <?php echo Form::input('list['.$i.'][product_name]', (!empty($data['list'][$i]['product_name'])) ? $data['list'][$i]['product_name']:'', array('id' => 'product_name_'.$i, 'class' => 'input-text', 'style' => 'width:210px;','maxlength' => '30','tabindex' => $i.'17')); ?>
                    </td>
                    <?php /* 傭車先No */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][carrier_code]', (!empty($data['list'][$i]['carrier_code'])) ? sprintf('%05d', $data['list'][$i]['carrier_code']):'', array('id' => 'carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:70px;','maxlength' => '5', 'id' => 'carrier_code_'.$i,'tabindex' => $i.'12')); ?>
                      <input type="button" name="s_carrier_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>13" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 傭車先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', 'list['.$i.'][carrier_name]', array('id' => 'carrier_name_'.$i, 'style' => 'display:inline;font-size:14px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][carrier_name]', (!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', array('id' => 'carrier_name_'.$i));?>
                    </td>
                    <?php /* 現場 */ ?>
                    <td style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][onsite_flag]', (!empty($data['list'][$i]['onsite_flag'])) ? $data['list'][$i]['onsite_flag']:'0', ($data['list'][$i]['onsite_flag'] == '1') ? true:false, array('id' => 'form_onsite_flag_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => $i.'09')); ?>
                      <?php echo Form::label('', 'onsite_flag_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 納品先住所 */ ?>
                    <td colspan="2">
                      <?php echo Form::input('list['.$i.'][delivery_address]', (!empty($data['list'][$i]['delivery_address'])) ? $data['list'][$i]['delivery_address']:'', array('id' => 'delivery_address_'.$i, 'class' => 'input-text', 'style' => 'width:260px;','maxlength' => '40','tabindex' => $i.'19')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="tab-content" id="tab-content2">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th>配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>地区<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>納品日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>納品先</th>
                    <th>数量<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>単位<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>

                    <th>依頼者</th>
                    <th>問い合わせNo</th>
                    <th rowspan="2">備考1</th>
                    <th rowspan="2">備考2</th>
                    <th rowspan="2">備考3</th>
                  </tr>
                  <tr>
                    <th>配車区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>コース</th>
                    <th>引取日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>引取先</th>
                    <th colspan="2">商品名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>メーカー</th>
                    <th>傭車費用<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 1;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 配送区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][delivery_code]', (!empty($data['list'][$i]['delivery_code'])) ? $data['list'][$i]['delivery_code']:'1', $delivery_list, array('id' => 'delivery_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 地区 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][area_code]', (!empty($data['list'][$i]['area_code'])) ? $data['list'][$i]['area_code']:'1', $area_list, array('id' => 'area_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'04')); ?>
                    </td>
                    <?php /* 納品日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][delivery_date]', (!empty($data['list'][$i]['delivery_date'])) ? $data['list'][$i]['delivery_date']:'', array('type' => 'date', 'id' => 'delivery_date_'.$i,'style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'06')); ?>
                    </td>
                    <?php /* 納品先 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][delivery_place]', (!empty($data['list'][$i]['delivery_place'])) ? $data['list'][$i]['delivery_place']:'', array('id' => 'delivery_place_'.$i, 'class' => 'input-text', 'style' => 'width:180px;','maxlength' => '30','tabindex' => $i.'08')); ?>
                    </td>
                    <?php /* 数量 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][volume]', (!empty($data['list'][$i]['volume'])) ? floatval($data['list'][$i]['volume']):'0.00', array('id' => 'volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][unit_code]', (!empty($data['list'][$i]['unit_code'])) ? $data['list'][$i]['unit_code']:'1', $unit_list, array('id' => 'unit_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 依頼者 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][requester]', (!empty($data['list'][$i]['requester'])) ? $data['list'][$i]['requester']:'', array('id' => 'requester_'.$i, 'class' => 'input-text', 'style' => 'width:130px;','maxlength' => '15','tabindex' => $i.'22')); ?>
                    </td>
                    <?php /* 問い合わせNo */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][inquiry_no]', (!empty($data['list'][$i]['inquiry_no'])) ? $data['list'][$i]['inquiry_no']:'', array('id' => 'inquiry_no_'.$i, 'class' => 'input-text', 'style' => 'width:140px;','maxlength' => '15','tabindex' => $i.'22')); ?>
                    </td>
                    <?php /* 備考1 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][remarks1]', (!empty($data['list'][$i]['remarks1'])) ? $data['list'][$i]['remarks1']:'', array('id' => 'remarks1_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'maxlength' => '15', 'tabindex' => $i.'25')); ?>
                    </td>
                    <?php /* 備考2 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][remarks2]', (!empty($data['list'][$i]['remarks2'])) ? $data['list'][$i]['remarks2']:'', array('id' => 'remarks2_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'maxlength' => '15', 'tabindex' => $i.'25')); ?>
                    </td>
                    <?php /* 備考3 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][remarks3]', (!empty($data['list'][$i]['remarks3'])) ? $data['list'][$i]['remarks3']:'', array('id' => 'remarks3_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'maxlength' => '15', 'tabindex' => $i.'25')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 配車区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][dispatch_code]', (!empty($data['list'][$i]['dispatch_code'])) ? $data['list'][$i]['dispatch_code']:'1', $dispatch_list, array('id' => 'dispatch_code_'.$i, 'class' => 'select-item', 'style' => 'width:90px;', 'tabindex' => $i.'03')); ?>
                    </td>
                    <?php /* コース */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][course]', (!empty($data['list'][$i]['course'])) ? $data['list'][$i]['course']:'', array('id' => 'course_'.$i, 'class' => 'input-text', 'style' => 'width:90px;','maxlength' => '5','tabindex' => $i.'05')); ?>
                    </td>
                    <?php /* 引取日 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][pickup_date]', (!empty($data['list'][$i]['pickup_date'])) ? $data['list'][$i]['pickup_date']:'', array('type' => 'date', 'id' => 'pickup_date_'.$i,'style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => $i.'07')); ?>
                    </td>
                    <?php /* 引取先 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][pickup_place]', (!empty($data['list'][$i]['pickup_place'])) ? $data['list'][$i]['pickup_place']:'', array('id' => 'pickup_place_'.$i, 'class' => 'input-text', 'style' => 'width:180px;','maxlength' => '30','tabindex' => $i.'09')); ?>
                    </td>
                    <?php /* 商品名 */ ?>
                    <td colspan="2">
                      <?php echo Form::input('list['.$i.'][product_name]', (!empty($data['list'][$i]['product_name'])) ? $data['list'][$i]['product_name']:'', array('id' => 'product_name_'.$i, 'class' => 'input-text', 'style' => 'width:210px;','maxlength' => '30','tabindex' => $i.'17')); ?>
                    </td>
                    <?php /* メーカー */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][maker_name]', (!empty($data['list'][$i]['maker_name'])) ? $data['list'][$i]['maker_name']:'', array('id' => 'maker_name_'.$i, 'class' => 'input-text', 'style' => 'width:130px;','maxlength' => '15','tabindex' => $i.'19')); ?>
                    </td>
                    <?php /* 傭車費用 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][carrier_payment]', (!empty($data['list'][$i]['carrier_payment'])) ? $data['list'][$i]['carrier_payment']:0, array('id' => 'carrier_payment_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'inputmode' => 'numeric', 'tabindex' => $i.'16')); ?>
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
