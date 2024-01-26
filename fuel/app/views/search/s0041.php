<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('location_id', (!empty($location_id)) ? $location_id:'');?>
        <?php echo Form::hidden('warehouse_id', (!empty($warehouse_id)) ? $warehouse_id:'');?>
        <?php echo Form::hidden('column_id', '');?>
        <?php echo Form::hidden('depth_id', '');?>
        <div style="width: 900px;">
            <div class="content-row" style="float: right">
                保管総数：<?php echo $total; ?> 件
            </div>
            <div class="content-row">
                <?php if (isset($storage_warehouse_list[$warehouse_id])) : ?>
                    <?php echo $storage_warehouse_list[$warehouse_id]; ?>
                <?php endif; ?>
            </div>
            <div class="content-row">
                <button type="button" onclick="onJump('<?php echo Uri::create('search/s0040'); ?>', '<?php echo $location_id; ?>', '', '', '')" class="buttonA">　倉庫に戻る　</button>
            </div>
        </div>
        <div class="table-wrap" style="clear: right">
            <table class="table-inq" style="width: 900px;">
                <tr>
                    <th style="width: 140px;">保管場所</th>
                    <th style="width: 160px;">収納可能台数</th>
                    <th style="width: 160px;">保管台数</th>
                    <th style="width: 160px;">空き台数</th>
                </tr>
                <?php if (!empty($list_data)) : ?>
                  <?php foreach ($list_data as $key => $val) : ?>
                    <tr>
                        <td style="text-align:left;padding-left:10px;" onclick="onJump('<?php echo Uri::create('search/s0042'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $val['storage_column_id']; ?>', '')">
                            <?php echo $val['storage_column_name']; ?>
                        </td>
                        <td style="padding-left:10px;"><?php echo $val['column_cnt']; ?></td>
                        <td style="padding-left:10px;"><?php echo $val['stock_cnt']; ?></td>
                        <td style="padding-left:10px;"><?php echo ($val['column_cnt'] - $val['stock_cnt']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif ; ?>
            </table>
        </div>
        <div class="search-buttons">
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>