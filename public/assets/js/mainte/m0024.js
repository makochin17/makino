window.onload = function(){
	change();
}

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

// 項目の活性状態制御
function change() {
	
	//得意先ラジオボタン操作
	var clientRs = document.getElementsByName("client_radio");
	if(clientRs[1].checked) {
		document.getElementById('text_client_code').disabled = false;
	} else {
		document.getElementById('text_client_code').disabled = true;
	}
	
	//締日プルダウン操作
	var closingCategory = document.getElementById("closing_category").value;
	switch(closingCategory){
		case "1":
			document.getElementById('closing_date_1').disabled = false;
			document.getElementById('closing_date_2').disabled = true;
			document.getElementById('closing_date_3').disabled = true;
			break;
		case "2":
			document.getElementById('closing_date_1').disabled = false;
			document.getElementById('closing_date_2').disabled = false;
			document.getElementById('closing_date_3').disabled = true;
			break;
		case "3":
			document.getElementById('closing_date_1').disabled = false;
			document.getElementById('closing_date_2').disabled = false;
			document.getElementById('closing_date_3').disabled = false;
			break;
		case "4":
			document.getElementById('closing_date_1').disabled = true;
			document.getElementById('closing_date_2').disabled = true;
			document.getElementById('closing_date_3').disabled = true;
			break;
	}
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}