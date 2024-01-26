<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('location_id', (!empty($location_id)) ? $location_id:'');?>
        <?php echo Form::hidden('warehouse_id', (!empty($warehouse_id)) ? $warehouse_id:'');?>
        <?php echo Form::hidden('column_id',  (!empty($column_id)) ? $column_id:'');?>
        <?php echo Form::hidden('depth_id',  (!empty($depth_id)) ? $depth_id:'');?>
        <div style="width: 900px;">
            <div class="content-row" style="float: right">
                保管総数：<?php echo $total; ?> 件
            </div>
            <div class="content-row">
                <?php if (isset($storage_warehouse_list[$warehouse_id])) : ?>
                    <?php echo $storage_warehouse_list[$warehouse_id]; ?>
                <?php endif; ?>
                <?php if (isset($storage_column_list[$column_id])) : ?>
                    - <?php echo $storage_column_list[$column_id]; ?>
                <?php endif; ?>
                <?php if (isset($storage_depth_list[$depth_id])) : ?>
                    - <?php echo $storage_depth_list[$depth_id]; ?>
                <?php endif; ?>
            </div>
            <div class="content-row">
                <button type="button" onclick="onJump('<?php echo Uri::create('search/s0042'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $column_id; ?>', '')" class="buttonA">　奥行に戻る　</button>
            </div>
            <div class="content-row" style="float: left">
                <?php if (isset($storage_depth_list[($depth_id - 1)])) : ?>
                    <button type="button" onclick="onJump('<?php echo Uri::create('search/s0043'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $column_id; ?>', '<?php echo ($depth_id - 1); ?>')" class="buttonA">　<　</button>
                <?php else: ?>
                    <button type="button" onclick="onJump('<?php echo Uri::create('search/s0043'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $column_id; ?>', '<?php echo ($depth_id - 1); ?>')" class="buttonA" disabled>　<　</button>
                <?php endif; ?>
            </div>
            <div class="content-row" style="float: right">
                <?php if (isset($storage_depth_list[($depth_id + 1)])) : ?>
                    <button type="button" onclick="onJump('<?php echo Uri::create('search/s0043'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $column_id; ?>', '<?php echo ($depth_id + 1); ?>')" class="buttonA">　>　</button>
                <?php else: ?>
                    <button type="button" onclick="onJump('<?php echo Uri::create('search/s0043'); ?>', '<?php echo $location_id; ?>', '<?php echo $warehouse_id; ?>', '<?php echo $column_id; ?>', '<?php echo ($depth_id + 1); ?>')" class="buttonA" disabled>　>　</button>
                <?php endif; ?>
            </div>
        </div>
        <?php echo Form::close(); ?>
        <div class="table-wrap" style="clear: right">
            <table class="table-inq" style="width: 900px;">
                <tr>
                    <th style="width: 60px">選択</th>
                    <th style="width: 60px;">No</th>
                    <th style="width: 100px;">保管場所</th>
                    <th style="width: 160px;">車種</th>
                    <th style="width: 160px;">登録番号</th>
                    <th style="width: 240px;">お客様</th>
                </tr>
                <?php if (!empty($list_data)) : ?>
                  <?php foreach ($list_data as $key => $val) : ?>
                    <tr>
                        <td style="width: 60px; text-align: center;">
                            <?php if ($location_id == $val['location_id'] || (empty($val['car_name']) && empty($val['car_code']) && empty($val['customer_name']))) : ?>
                                <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                                <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                                <?php echo Form::hidden('location_id', (!empty($location_id)) ? $location_id:'');?>
                                <?php echo Form::hidden('warehouse_id', (!empty($warehouse_id)) ? $warehouse_id:'');?>
                                <?php echo Form::hidden('column_id',  (!empty($column_id)) ? $column_id:'');?>
                                <?php echo Form::hidden('depth_id',  (!empty($depth_id)) ? $depth_id:'');?>
                                <?php echo Form::hidden('storage_height_id', $val['storage_height_id']);?>
                                <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                                <?php echo Form::close(); ?>
                            <?php endif ; ?>
                        </td>
                        <td style="padding-left:10px;"><?php echo ($key + 1); ?></td>
                        <td style="padding-left:10px;"><?php echo $val['storage_height_name']; ?></td>
                        <td style="padding-left:10px;"><?php echo $val['car_name']; ?></td>
                        <td style="padding-left:10px;"><?php echo $val['car_code']; ?></td>
                        <td style="padding-left:10px;"><?php echo $val['customer_name']; ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif ; ?>
            </table>
        </div>
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('location_id', (!empty($location_id)) ? $location_id:'');?>
        <?php echo Form::hidden('warehouse_id', (!empty($warehouse_id)) ? $warehouse_id:'');?>
        <?php echo Form::hidden('column_id',  (!empty($column_id)) ? $column_id:'');?>
        <?php echo Form::hidden('depth_id',  (!empty($depth_id)) ? $depth_id:'');?>
        <div class="search-buttons">
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>