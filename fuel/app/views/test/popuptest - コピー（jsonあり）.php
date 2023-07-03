<section id="banner" style="padding-top:10px;">
	<div class="content" style="margin-top:0px;">
		<div style="margin:20px 0px 15px 10px;">
      <?php echo Html::anchor(\Uri::create('master/mroster/regist'), ' ＞ 新しい名簿を追加', array('class' => 'button')); ?>
    </div>
    <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
    <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
      <table class="table2">
        <tbody>
          <tr>
            <th style="width:190px;">名簿ID</th>
            <td>
              <?php echo Form::input('from_code', (!empty($data['from_code'])) ? $data['from_code'] : '', array('type' => 'text', 'class' => 'text', 'id' => 'from_code', 'style' => 'ime-mode:disabled;width:150px;', 'size' => '8', 'maxlength' => '8', 'tabindex' => '1')); ?>
               ～ 
              <?php echo Form::input('to_code', (!empty($data['to_code'])) ? $data['to_code'] : '', array('type' => 'text', 'class' => 'text', 'id' => 'to_code', 'style' => 'ime-mode:disabled;width:150px;', 'size' => '8', 'maxlength' => '8', 'tabindex' => '2')); ?>
            </td>
          </tr>
          <tr>
            <th>名簿名</th>
            <td>
              <?php echo Form::input('name', (!empty($data['name'])) ? $data['name'] : '', array('type' => 'text', 'class' => 'text', 'id' => 'name', 'size' => '30', 'tabindex' => '3')); ?>
            </td>
          </tr>
        </tbody>
      </table>
      <div style="text-align:center;margin-top:10px;font-size:16px;">
        <?php echo Form::submit('search', '&nbsp;検&nbsp;索&nbsp;', array('class' => 'button', 'tabindex' => '900')); ?>
        <input type="button" value="ポップアップ"
    onclick="
        var callback_id = 'callback_s0040'; //適当にIDをふる
        window[callback_id] = function(json){ //windowにコールバックを登録
            document.getElementById('from_code').value = json.cd;
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0040/s0040'); ?>', callback_id, 'width=700,height=500');
    "/>
        
      </div>
    <?php echo Form::close(); ?>

    <!-- ここからPager -->
    <div class="floatright">
      <?php echo $pager; ?>
    </div>
    <!-- ここまでPager -->

    <table class="tablelist">
      <tbody>
        <tr>
          <th style="width:100px;text-align:center;">ID</th>
          <th style="width:240px;">名　簿</th>
          <th>委員会</th>
          <th style="width:60px;">削除</th>
        </tr>
        <?php if (!empty($list_data)) : ?>
          <?php foreach ($list_data as $key => $val) : ?>
          <tr>
            <td style="text-align:center;">
              <?php echo Html::anchor(\Uri::create('master/mroster/edit').'?id='.$val['id'], $val['id']); ?>
            </td>
            <td style="text-align:left;"><?php echo $val['name']; ?></td>
            <td style="text-align:left;"><?php echo (isset($list[$val['committee_id']])) ? $list[$val['committee_id']]:'ー'; ?></td>
            <td style="text-align:center;">
              <?php echo Html::anchor(\Uri::create('master/mroster/del').'?id='.$val['id'], '<i class="fa fa-trash" style="font-size:24px;"></i>'); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="3" style="text-align:left;">該当するデータが見つかりませんでした</td>
          </tr>
        <?php endif; ?>

      </tbody>
    </table>
    <div class="floatleft" style="text-align:left;">
      検索結果：<?php echo $total; ?> 件
    </div>
    <div class="floatright">
      <?php echo $pager; ?>
    </div>
	</div>
</section>