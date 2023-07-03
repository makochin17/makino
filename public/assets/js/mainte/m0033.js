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
	
	if(r_value == 1) {
		document.getElementById('department_name').disabled = false;
	}
	if(r_value == 2){
		document.getElementById('department_name').disabled = true;
	}
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}