// 「更新」の確認ダイアログ表示
function submitChkUpdate() {
    var flag = window.confirm (processing_msg1);
    return flag;
}

// 「削除」の確認ダイアログ表示
function submitChkDelete() {
    var flag = window.confirm (processing_msg2);
    return flag;
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}