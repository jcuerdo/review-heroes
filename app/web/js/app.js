$(document).ready(function(){

    $(document).on('click', '.nav-sidebar > li', function(){
        $(this).addClass('active');
    });

    $('#datetimepickerstart').datetimepicker({format: 'YYYY-MM-DD'});
    $('#datetimepickerend').datetimepicker({format: 'YYYY-MM-DD'});

});