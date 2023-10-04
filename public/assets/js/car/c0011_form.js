// フォームデータ処理
$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // =================================================
    // 車両情報入力
    // =================================================
    // 残溝数
    $('input[id^=summer_tire_remaining_groove], input[id^=winter_tire_remaining_groove]').on('focus input',function () {
        if ($(this).val().length > 0) {
            var num = $(this).val().replace(/[^\d.]/g, '');
            $(this).val(num);
        }
    }).on('blur',function (e) {
        var num     = $(this).val();
        var float   = parseFloat(num).toFixed(6);
        var res     = float.split('.');
        if (float.length > 6) {
            if (res[1] == '00') {
                $(this).val(Number(float).toLocaleString() + '.00');
            } else {
                $(this).val(Number(float).toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        } else {
            if (num.length > 0) {
                $(this).val(float.toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        }
        return e.preventDefault();
    });

    // ページスーパーリロード
    var sp_reload = function() {

        if (document.URL.indexOf("#")==-1) {
            url = document.URL+"&"+(new Date()).getTime();
            window.location.href = url;
            // location = "#";
            // window.location.href = window.location.href;
        }

    }

    $('[id^=img_url]').click(function(e){
        window.open($(this).attr('data-url'), "WindowName","width=900,height=700,resizable=yes,scrollbars=yes");
        // window.open($(this).data('url'), "WindowName","width=900,height=700,resizable=yes,scrollbars=yes");
        return e.preventDefault();
    });

    /* ======== アップロード処理用 ======== */
    /* クリックでファイル選択を起動 */
    $('a[id=btnUpload]').click(function(e){

        var car_id   = $('[name=car_id]').val();
        // 未入力チェック
        if (!car_id) {
            return confirm("車両IDが存在していません");
        }
        // 重複チェック
        var file_data = $('[name=fileUpload]').val();
        $('span#file_data_err').html('');

        if (car_id) {
            // 更新
            $.ajax({
                type: "POST",
                url: $('[name=check_url]').val(),
                data: {
                    "car_id":car_id
                },
                success: function(res){
                    if (res == 0) {
                        $('span#file_data_err').html('車両情報が存在しません');
                    } else {
                        if (file_data) {
                            if ($('#member_img').length > 0) {
                                var src         = $('#member_img').attr('src');
                                var extension   = file_data.split('.');
                                src             = src.replace('no_img.png', member_id+'.'+extension[1]);
                            }
                            $('#fileform').submit();
                            if ($('#member_img').length > 0) { $("#member_img").attr("src",src+'?t='+new Date().getTime()); }

                            $('span#file_data_err').html('ファイルをアップロードしました');

                            // 2秒後にリロード
                            setTimeout(function(){
                                sp_reload();
                            }, 2000);

                        } else {
                            $('span#file_data_err').html('ファイルが選択されていません');
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                }
            });
        } else {
            // 新規登録
            $.ajax({
                type: "POST",
                url: $('[name=check_url]').val(),
                data: {
                    "car_id":car_id
                },
                success: function(res){
                    if (res > 0) {
                        $('span#member_code_err').html('会員コードが既に存在しています');
                    } else {
                        if (file_data) {
                            if ($('#member_img').length > 0) {
                                var src         = $('#member_img').attr('src');
                                var extension   = file_data.split('.');
                                src             = src.replace('no_img.png', member_code+'.'+extension[1]);
                            }
                            $('#fileform').submit();
                            if ($('#member_img').length > 0) { $("#member_img").attr("src",src+'?t='+new Date().getTime()); }

                            $('span#file_data_err').html('ファイルをアップロードしました');
                        } else {
                            $('span#file_data_err').html('ファイルが選択されていません');
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                }
            });
        }

        e.preventDefault();
        return false;
    });

    /* ======== アップロード・エクスポートdivの開閉 ======== */
    $('#fileUpload').change(function(e){

        var car_id = $('[name=car_id]').val();
        // 未入力チェック
        if (!car_id) {
            return confirm("車両IDを入力してください");
        }
        // 重複チェック
        if (check_id(car_id)) {
            return confirm("車両IDが既に存在しています");
        }

        $('[name=file_id]').val(car_id);

        $('#fileform').submit();
        $(this).val('');
        // return e.preventDefault();

    });

});
