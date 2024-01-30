// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear() {
    var flag = window.confirm (clear_msg);
    return flag;
}

// 画面遷移ボタン押下時処理
function onJump(url_str) {
    var f = document.forms["searchForm"];
    f.method = "GET";
    f.action = url_str;
    f.submit();

    return true;
}

// 編集ボタン押下時処理
function onEdit(url_str, member_code, user_id) {
    var f = document.forms["selectForm"];
    f.list.value = '1';
    f.member_code.value = member_code;
    f.user_id.value = user_id;
    f.select_record.value = 1;
    f.processing_division.value = 2;
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 削除ボタン押下時処理
function onDelete(member_code) {
    //削除確認
    var flag = window.confirm(processing_msg1.replace('XXXXX', member_code));
    if (!flag)return false;

    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.member_code.value = member_code;
    f.user_id.value = user_id;
    f.method = "POST";
    f.submit();

    return true;
}

// 検索ボタン押下時処理
function carModelSearch(url_str) {
    var callback_id = 'callback_m0011'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// お客様検索ボタン押下時処理
function onCustomerSearch(url_str) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}
