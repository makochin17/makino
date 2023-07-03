// フォームデータ処理
$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // =================================================
    // 入力
    // =================================================

    // 配車番号
    $('input[id^=dispatch_number]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        $('input[id^=dispatch_number_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 売上確定
    $('input[id^=form_sales_status]').on('change', function(e) {

        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var tabno   = names[3];
        var listno  = names[4];

        if (!prop) {
            $('#hidden_list_'+listno+'_sales_status').val('1');
            $('#form_sales_status_0_'+listno).val('1').prop("checked", false);
            $('#form_sales_status_1_'+listno).val('1').prop("checked", false);
        } else {
            $('#hidden_list_'+listno+'_sales_status').val('2');
            $('#form_sales_status_0_'+listno).val('2').prop("checked", true);
            $('#form_sales_status_1_'+listno).val('2').prop("checked", true);
        }

        return e.preventDefault();
    });

    // 配送区分
    $('select[id^=delivery_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('select[id^=delivery_code_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 地区
    $('select[id^=area_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('select[id^=area_code_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 運行日
    $('input[id^=destination_date]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('input[id=destination_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 運行先
    $('input[id^=destination]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[1];

        $('input[id=destination_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 得意先No
    $('input[id^=client_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }

        $('input[id^=client_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 得意先名
    $('input[id^=client_name]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('input[id^=client_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車先No
    $('input[id^=carrier_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }

        $('input[id^=carrier_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 傭車先名
    $('input[id^=carrier_name]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('input[id^=carrier_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

});
