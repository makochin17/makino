// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear() {
    var flag = window.confirm (clear_msg);
    return flag;
}

// 「確定」の確認ダイアログ表示
function submitChkExecution() {
    var flag = window.confirm (processing_msg1);
    return flag;
}

// 「削除」の確認ダイアログ表示
function onDelete(storage_depth_id, del_flg) {
    if (del_flg == 'NO') {
        var flag = window.confirm (processing_msg2);
    } else {
        var flag = window.confirm (processing_msg3);
    }
    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.storage_depth_id.value = storage_depth_id;
    f.method = "POST";
    f.submit();

    return flag;
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}