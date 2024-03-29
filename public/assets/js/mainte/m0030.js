// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 新規登録ボタン押下時処理
function onSubReg(url_str) {
    return location = url_str;
}

// 新規登録ボタン押下時処理
function onAdd(url_str) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '1';
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 編集ボタン押下時処理
function onEdit(url_str, storage_location_id) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '2';
    f.storage_location_id.value = storage_location_id;
    f.select_record.value = 1;
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 削除ボタン押下時処理
function onDelete(storage_location_id, storage_location_name) {
	//削除確認
	var flag = window.confirm(processing_msg1.replace('XXXXX', storage_location_name));
	if (!flag)return false;

    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.storage_location_id.value = storage_location_id;
    f.method = "POST";
    f.submit();

    return true;
}

$(function(){

    // バーコードフラグ
    $('input[id^=form_barcode_flg]').on('change', function(e) {

        var prop                = $(this).prop('checked');
        var id                  = $(this).attr('id');
        var names               = id.split('_');
        var listno              = names[3];
        var storage_location_id = $('#storage_location_id_'+listno).val();

        if (!prop) {
            var barcode_flg = 'NO';
        } else {
            var barcode_flg = 'YES';
        }

        $.ajax({
            type: "POST",
            url: $('[name=update_url]').val(),
            data: {
                "storage_location_id":storage_location_id,
                "barcode_flg":barcode_flg
            },
            success: function(res){
                if (res.status == false) {
                    alert('バーコードフラグの変更に失敗しました');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
            }
        });

        return e.preventDefault();
    });

});

