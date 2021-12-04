var TableDatatablesResponsive = function () {

    var initPanelToggle = function() {
        $(".panel-heading").on('click', function(){
            //do stuff here
            var currentItem = $(this);
            var arrHeadings = $(".panel-heading");
            $.each(arrHeadings, function(idx, obj) {
                if(currentItem.children('.item-head').attr('id') !== $(obj).children('.item-head').attr('id')) {
                    if($(obj).find('a.btn-circle').children('i').hasClass('fa-chevron-down')) {
                        $(obj).find('a.btn-circle').children('i').removeClass('fa-chevron-down');
                        $(obj).find('a.btn-circle').children('i').addClass('fa-chevron-right');
                    }
                }
            });

            var iTag = $(this).find('a.btn-circle').children('i');
            if(iTag.hasClass('fa-chevron-right')) {
                iTag.removeClass('fa-chevron-right');
                iTag.addClass('fa-chevron-down');
            }
            else {
                iTag.removeClass('fa-chevron-down');
                iTag.addClass('fa-chevron-right');
            }
        });
    };

    return {
        //main function to initiate the module
        init: function () {
            initPanelToggle();
        }
    };

}();

jQuery(document).ready(function() {
    TableDatatablesResponsive.init();
});