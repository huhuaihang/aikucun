jQuery(function($) {
    table_check_all();
});
function table_check_all() {
    var active_class = 'active';
    $('table > thead > tr > th input[type=checkbox]').eq(0).on('click', function() {
        var th_checked = this.checked;
        $(this).closest('table').find('tbody > tr').each(function() {
            var row = this;
            if (th_checked) $(row).addClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', true);
            else $(row).removeClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', false);
        });
    });
    $('table').on('click', 'td input[type=checkbox]', function() {
        var row = $(this).closest('tr');
        if(this.checked) row.addClass(active_class);
        else row.removeClass(active_class);
    });
}
