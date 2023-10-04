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
