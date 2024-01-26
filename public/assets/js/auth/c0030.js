$(function(){

    $(document).ready( function(){
        $('div[id=modalArea]').fadeIn();
    });

    $('button[id=sendModal]').click(function(e){
        location.href = $(this).attr('data-href');
        return e.preventDefault();
    });
    $('button[id=closeModal]').click(function(e){
        $('div[id=modalArea]').fadeOut();
        return e.preventDefault();
    });

});
