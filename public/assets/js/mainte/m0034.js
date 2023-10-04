
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

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}