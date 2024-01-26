// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 行押下時処理
function onJump(url_str, location_id, warehouse_id, column_id, depth_id) {
    var f = document.forms["selectForm"];
    // f.processing_division.value = '2';
    f.location_id.value     = location_id;
    f.warehouse_id.value    = warehouse_id;
    f.column_id.value       = column_id;
    f.depth_id.value        = depth_id;
    f.method                = "POST";
    f.action                = url_str;
    f.submit();

    return true;
}
