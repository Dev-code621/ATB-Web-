var TableDatatablesResponsive = function () {

    var initStarRating = function () {
        // $(".my-rating").starRating({
        //     starSize: 18,
        //     emptyColor:'#A6BFDE20',
        //     activeColor:'#A6BFDE',
        //     hoverColor:'#A6BFDE28',
        //     readOnly:true,
        //     initialRating: 2,
        //     useGradient:false,
        //     callback: function(currentRating, $el){
        //         // make a server call here
        //     }
        // });
    };

    return {
        //main function to initiate the module
        init: function () {
            initStarRating();
            if (!jQuery().dataTable) {
                return;
            }
        }
    };

}();

jQuery(document).ready(function() {
    TableDatatablesResponsive.init();
});