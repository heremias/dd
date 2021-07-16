(function ($) {
var controller = new ScrollMagic.Controller();


// accent 

var scene = new ScrollMagic.Scene({
    triggerElement: "#footer-tower",
    duration: $('#footer-tower').height(),
    triggerHook: 1,
})
.setTween("#footer-accent", {right: 0, bottom:0})
.addTo(controller);


// background
var scene = new ScrollMagic.Scene({
    triggerElement: "#footer-tower",
    duration: $('#footer-tower').height(),
    triggerHook: 1,
})
.setTween("#footer-tower .bg", {right: '-250px', left: '-250px', bottom: '-250px', top: '-250px', opacity: 1})
.addTo(controller);




// seal
var scene = new ScrollMagic.Scene({
    triggerElement: "#footer-tower",
    duration: $('#footer-tower').height(),
    triggerHook: 1,
})
.setTween("#footer-tower #seal", {opacity: 1})
.addTo(controller);
   
})(jQuery);