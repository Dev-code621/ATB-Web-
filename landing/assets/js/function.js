function sliderIndicatorPosition() {
   var windowHeight = window.innerHeight;
   var bottomNavHeight = $('.navigation').outerHeight();
   var positionIndicators = windowHeight - bottomNavHeight - 50;
   var sliderIndicator = $('.index-content #sliders .carousel-indicators');
   sliderIndicator.css({'top': `${positionIndicators}px`, 'opacity': '1'});
}
sliderIndicatorPosition();

$(window).scroll(function() {    
   var scroll = $(window).scrollTop();
   if (scroll >= 150) {
         $(".top-header").addClass("scrolled");
         $(".btn-bottom").addClass("scrolled");
         $(".fixed-elements").addClass("scrolled");
   } else {
         $(".top-header").removeClass("scrolled");
         $(".btn-bottom").removeClass("scrolled");
         $(".fixed-elements").removeClass("scrolled");
   }
}); 

$('#openNav').on('click', function(e) {
   e.preventDefault();
   $('.navigation-options').addClass('open')
});
$('#closeNav').on('click', function(e) {
   $('.navigation-options').removeClass('open')
});

$(document).on('show.bs.modal', '#exampleModal', function (e) {
   $('html').addClass("overflow")
});
$(document).on('hidden.bs.modal', '#exampleModal', function (e) {
   $('html').removeClass("overflow")
});

// Category Select
$(document).mouseup(function(e) {
   var menuSelect = $("#selectCategory .select-show");
   if (!menuSelect.is(e.target) && menuSelect.has(e.target).length === 0) 
   {
       $('#selectCategory .select-list').removeClass('open');
       $('#selectCategory .select-show i').removeClass('open');
   } else {
       $('#selectCategory .select-list').toggleClass('open');
       $('#selectCategory .select-show i').toggleClass('open');
   }
});
$('#selectCategory input[name="businessCategory"]').on('change', function(){
   $('#selectCategory .select-list label').css('display', 'block');
   let inputValue = $(this).val();
   console.log(inputValue);
});

// Access Select
$(document).mouseup(function(e) {
   var menuSelect = $("#selectAccess .select-show");
   if (!menuSelect.is(e.target) && menuSelect.has(e.target).length === 0) 
   {
       $('#selectAccess .select-list').removeClass('open');
       $('#selectAccess .select-show i').removeClass('open');
   } else {
       $('#selectAccess .select-list').toggleClass('open');
       $('#selectAccess .select-show i').toggleClass('open');
   }
});
$('#selectAccess input[name="accessType"]').on('change', function(){
   $('#selectAccess .select-list label').css('display', 'block');
   let inputValue = $(this).val();
   console.log(inputValue);
});
