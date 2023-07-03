window.onload = function(){
	change();
}

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

// 項目の活性状態制御
function change() {
	
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