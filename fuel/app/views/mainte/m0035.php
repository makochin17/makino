<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('storage_location_id', (!empty($data['storage_location_id'])) ? $data['storage_location_id']:'');?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0035.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>
        <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>

        <p class="error-message-head"><?php echo $error_message; ?></p>
        <br />
			<table class="search-area" style="height: 90px; width: 780px">
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所倉庫<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_warehouse_id', $data['storage_warehouse_id'], $storage_warehouse_list,
				            array('class' => 'select-item', 'id' => 'storage_warehouse_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所列<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_column_id', $data['storage_column_id'], $storage_column_list,
				            array('class' => 'select-item', 'id' => 'storage_column_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所奥行<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_depth_id', $data['storage_depth_id'], $storage_depth_list,
				            array('class' => 'select-item', 'id' => 'storage_depth_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所高さ<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_height_id', $data['storage_height_id'], $storage_height_list,
				            array('class' => 'select-item', 'id' => 'storage_height_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
			</table>

			<br />
        <div class="search-buttons">
            <?php echo Form::submit('update', '更　　新', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkUpdate()', 'tabindex' => '900')); ?>
            <?php echo Form::submit('delete', '削　　除', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkDelete()', 'tabindex' => '901')); ?>
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'tabindex' => '902')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
