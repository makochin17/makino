<?php use \Model\Common\closingdate; ?>
<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('mainte/m0020.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0022'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">ユニット名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('unit_name', (!empty($data['unit_name'])) ? $data['unit_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'unit_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '2')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('mainte/m0021').'\')', 'tabindex' => '902')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('unit_code', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
            <div class="table-wrap">
                <table class="table-inq" style="width: 720px">
                    <tr>
                        <th style="width: 100px">操作</th>
                        <th style="width: 80px">タイプ</th>
                        <th style="width: 160px">ユニット名</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('mainte/m0025'); ?>', '<?php echo $val['unit_code']; ?>')" class="buttonA"><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                                <button type="button" onclick="onDelete(<?php echo $val['unit_code']; ?>, '<?php echo $val['unit_name']; ?>')" class="buttonA"><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td style="width: 160px"><?php echo (isset($schedule_type_list[$val['schedule_type']])) ? $schedule_type_list[$val['schedule_type']]:'不明'; ?></td>
                            <td style="width: 160px"><?php echo $val['unit_name']; ?></td>
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
        <?php endif ; ?>
	</div>
</section>
