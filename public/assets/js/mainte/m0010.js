// 項目の活性状態制御
window.onload = change;
var flag = null;

function change() {
	processing_division = document.entryForm.processing_division.value;
    flag = (processing_division == '1') ? false : true;

    document.entryForm.text_member_code.disabled = flag;
    if (processing_division == '3') {
    	document.entryForm.text_user_id.disabled = flag;
    } else {
    	document.entryForm.text_user_id.disabled = false;
    }
}

// 動的にhidden追加
function make_hidden(name, value, formname) {
    var q = document.createElement('input');
    q.type = 'hidden';
    q.name = name;
    q.value = value;
    if (formname) {
        if ( document.forms[formname] == undefined ){
            console.error( "ERROR: form " + formname + " is not exists." );
        }
        document.forms[formname].appendChild(q);
    } else {
        document.forms[0].appendChild(q);
    }
}

function submitChkBack() {
    change();
    make_hidden('back', '1');
    document.entryForm.submit();
}

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear() {
    var flag = window.confirm (clear_msg);
    return flag;
}

// 「確定」の確認ダイアログ表示
function submitChkExecution() {
    var value = document.entryForm.processing_division.value;
    var flag = false;

    // 処理区分によってメッセージを変える
    if (value == '1') {
        flag = window.confirm (processing_msg1);
    } else if(value == '2') {
        flag = window.confirm (processing_msg2);
    } else if(value == '3') {
        flag = window.confirm (processing_msg3);
    }
    // hiddenに社員コードコピー
    document.entryForm.member_code.value = document.entryForm.text_member_code.value;
    // hiddenにログインIDコピー
    document.entryForm.user_id.value = document.entryForm.text_user_id.value;

    return flag;
}

// 「ロック解除」の確認ダイアログ表示
function submitChkUnlock() {

    if(document.entryForm.user_id.value == ''){
        $('.error-message-head').text(error_msg1);
        return false;
    }

    if(window.confirm(processing_msg4)){
        document.entryForm.member_code.value    = document.entryForm.text_member_code.value;
        document.entryForm.user_id.value        = document.entryForm.text_user_id.value;
        return true;
    }
    return false;
}

// 「パスワード初期化」の確認ダイアログ表示
function submitChkPassInitialize() {

    if(document.entryForm.user_id.value == ''){
        $('.error-message-head').text(error_msg1);
        return false;
    }

    if(window.confirm(processing_msg5)){
        document.entryForm.member_code.value    = document.entryForm.text_member_code.value;
        document.entryForm.user_id.value        = document.entryForm.text_user_id.value;
        return true;
    }
    return false;
}

// 検索ボタン押下時処理
function carModelSearch(url_str) {
    var callback_id = 'callback_m0010'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(function() {

    //search condition on/off
    $('[id=execution]').click(function(e) {
        if(!submitChkExecution()){
            /*　キャンセルの時の処理 */
            return false;
        }else{
            $('#entryForm')
            .submit();
            return false;
        }
        return e.preventDefault();
    });

    /* ======== アップロード処理用 ======== */

    /* クリックでファイル選択を起動 */
    $('#csv_capture').click(function(e){

        var fileUpload = $(this).data('trigger');
        $(fileUpload).click();

        return e.preventDefault();

    });

    /* ======== アップロード・エクスポートdivの開閉 ======== */

    $('#fileUpload').change(function(){

        $('#upForm').submit();
        $(this).val('');
        return false;

    });

});