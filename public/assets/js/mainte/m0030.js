// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
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
function onEdit(url_str, carrier_code) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '2';
    f.carrier_code.value = carrier_code;
    f.select_record.value = 1;
    f.method = "POST";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 削除ボタン押下時処理
function onDelete(carrier_code) {
	//削除確認
	var carrier_code_p = ('00000' + carrier_code).slice(-5);
	var flag = window.confirm(processing_msg1.replace('XXXXX', carrier_code_p));
	if (!flag)return false;
	
    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.carrier_code.value = carrier_code;
    f.method = "POST";
    f.submit();
    
    return true;
}
