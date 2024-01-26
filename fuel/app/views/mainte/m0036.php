<section id="banner" style="padding-top:20px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', 1);?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0036.js');?>
        <script>
            var clear_msg       = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0023'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_MI0024'); ?>';
        </script>
        <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>

        <p class="error-message-head"><?php echo $error_message; ?></p>
        <br />
            <table class="search-area" style="height: 90px; width: 780px">
                <tr>
                    <td style="width: 140px; height: 30px;">
                        保管場所倉庫名
                    </td>
                    <td style="width: 640px; height: 30px;">
                        <?php echo Form::input('storage_warehouse_name', (!empty($data['storage_warehouse_name'])) ? $data['storage_warehouse_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_warehouse_name', 'style' => 'width:620px;', 'minlength' => '1', 'maxlength' => '60', 'tabindex' => '1')); ?>
                    </td>
                </tr>
            </table>

            <br />
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '登　　録', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', 3);?>
        <?php echo Form::hidden('storage_warehouse_id', '');?>
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
                        <th style="width: 80px">ID</th>
                        <th style="width: 540px">名称</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php ($val['del_flg'] == 'NO') ? $mark = '無効':$mark = '有効'; ?>
                        <tr>
                            <td style="width: 100px; text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['storage_warehouse_id']; ?>, '<?php echo $val['del_flg']; ?>')" class="buttonA"><i class='fa fa-trash' style="font-size:15px;"></i><?php echo $mark; ?></button>
                            </td>
                            <td style="width: 80px"><?php echo $val['storage_warehouse_id']; ?></td>
                            <td style="width: 540px"><?php echo $val['storage_warehouse_name']; ?></td>
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
        <?php echo Form::close(); ?>
    </div>
</section>
