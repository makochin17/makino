window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//ラジオボタン操作
	var departmentRs = document.getElementsByName("department_radio");
	for ( var i=0, i=departmentRs.length; i--; ) {
		if ( departmentRs[i].checked ) {
			var r_value = departmentRs[i].value ;
			break;
		}
	}
	
	if(r_value == 1 || r_value == 3) {
		document.getElementById('department_name').disabled = true;
	}
	if(r_value == 2){
		document.getElementById('department_name').disabled = false;
	}
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}