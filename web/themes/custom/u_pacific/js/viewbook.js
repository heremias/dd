(function ($) {

if($('body').hasClass('path-viewbook')){
    $(document).ready(function(){
        $('.path-viewbook header').addClass('animated fadeIn');
        setTimeout(function(){
            $(".path-viewbook header").addClass("located");
        }, 1800);
        $('.path-viewbook header p').addClass('animated fadeIn');
        setTimeout(function(){
            $(".path-viewbook .col-1").addClass("animated");
        }, 2000);
        setTimeout(function(){
            $(".path-viewbook .col-2").addClass("animated");
        }, 2000);
    });
}

if($('body').hasClass('page-node-29450') || $('body').hasClass('page-node-29640')){

    $(window).bind("load", function () {
        setTimeout(preloader, 600);
        setTimeout(loadlanding, 800);
    });

    function preloader(){
        $('body').addClass('viewbook-loaded');
    }

    function loadlanding(){
        $('.intro-section').addClass('loaded');
        $('#intro-header').addClass('loaded');
    }

    // global controller 
    var controller = new ScrollMagic.Controller();

    if($(window).width() >= 960){
    // pin
    var scene = new ScrollMagic.Scene({
        triggerElement: ".intro-wrap",
        duration: $(window).height(), 
        })
    .setPin("#intro-section-1")
    .addTo(controller);

    // pin fadeout 
    var scene = new ScrollMagic.Scene({
        triggerElement: "#intro-section-2",
        triggerHook: 1,
        duration: $('#intro-section-2').height()/2, 
        })
    .setTween(".intro-1-wrap .dark", {'opacity': '.6', ease: Linear.easeNone})
    .addTo(controller);

    // lax background
    var scene = new ScrollMagic.Scene({
        triggerElement: ".intro-wrap",
        duration: $('.intro-1-wrap').height(), 
        })
    .setTween(".lag-bg", {'background-size': "120%", ease: Linear.easeNone})
    .addTo(controller);

    // student 1 lax
    var scene = new ScrollMagic.Scene({
        triggerElement: '#intro-section-1', 
        triggerHook: 0,
        duration: $('.intro-1-wrap').height(), 
    })
    .setTween('#student-lax-1', { bottom:0, width: '25%', ease: Linear.easeNone})
    .addTo(controller);

    // circles lax 
    var scene = new ScrollMagic.Scene({
        triggerElement: '#intro-section-1', 
        triggerHook: 0,
        duration: $('.intro-1-wrap').height(), 
    })
    .setTween('#full-circles', { width:'1200', ease: Linear.easeNone})
    .addTo(controller);

    // student 2 lax 
    var scene = new ScrollMagic.Scene({
        triggerElement: '#intro-section-1', 
        triggerHook: 0,
        duration: $('.intro-1-wrap').height(), 
    })
    .setTween('#student-lax-2', { bottom:0, width: '25%', ease: Linear.easeNone})
    .addTo(controller);

    // bottom pin 
    var scene = new ScrollMagic.Scene({
        triggerElement: "#intro-section-2",
        duration: $(window).height(), 
        triggerHook:'0',
        offset: -100,

        })
    .setPin("#intro-section-1")
    .addTo(controller);

    var scene = new ScrollMagic.Scene({
        triggerElement: "#intro-section-2",
        duration: $(window).height(), 
        triggerHook:'0',
        offset: -100,

        })
    .setPin("#intro-section-2")
    .addTo(controller);

    // bottom bg

    var scene = new ScrollMagic.Scene({
        triggerElement: '#intro-section-2', 
        triggerHook: 0,
        duration: $('#intro-section-2').height(), 
        offset: $(window).height()
    })
    .setTween('#intro-section-2 .bg', {top:0, right:0, left:0, bottom: 0, ease: Linear.easeNone})
    .addTo(controller);
    }

}

if($('body').hasClass('page-node-type-viewbook-')){

var jswrapperController = new ScrollMagic.Controller({vertical: false, container: "#js-wrapper"});


  new ScrollMagic.Scene({
    triggerElement: "#section-2",
    triggerHook: "onEnter",
    duration: '200%',
  })
  .setTween(".field--name-field-quote-1-background img", {left: "-20%", ease: Linear.easeNone})
  .addTo(jswrapperController);


new ScrollMagic.Scene({
    triggerElement: "#section-4",
    triggerHook: "onEnter",
    duration: '200%',
})
.setTween(".field--name-field-academic-quote-background img", {left: "-20%", ease: Linear.easeNone})
.addTo(jswrapperController);


new ScrollMagic.Scene({
    triggerElement: "#section-7",
    triggerHook: "onEnter",
    duration: '200%',
})
.setTween(".field--name-field-community-involvement-back img", {left: "-20%", ease: Linear.easeNone})
.addTo(jswrapperController);



new ScrollMagic.Scene({
  triggerElement: "#section-9",
  triggerHook: "onEnter",
  duration: '200%',
})
.setTween(".field--name-field-quote-2-background img", {left: "-20%", ease: Linear.easeNone})
.addTo(jswrapperController);


// your pacific peers 

$(window).on('focus', function() { 
    landscapeCheck();
});
$(window).resize(function(){
    landscapeCheck();
});

$(document).ready(function(){

    startsly();

    // landscape check 
    landscapeCheck();
    $(window).resize(function(){
        landscapeCheck();
    });

    // scroll to top on spine click 
    $("#pagetop").click(function(e) {
        e.preventDefault();
        // $("#js-slideContainer").css('transform','translateZ(0) translateX(0)');
        $('body').removeClass('share-open');
        $('body').removeClass('profiles-open');
        sly.toStart();
        return false;
      });

    //   tabbing functionality 
      $('section.section a').on('focus', function(){ 
        sly.toStart($(this));
      });



    // audio player 
    if($('#mp3player').length){
        var audioElement = document.createElement('audio');
        var file = $('#mp3player').attr('data-audio')
        audioElement.setAttribute('src', file);

        audioElement.addEventListener('ended', function() {
            this.play();
        }, false);
        
        audioElement.addEventListener("canplay",function(){
            $("#totaltime").text(formatTime(audioElement.duration));
        });
        
        audioElement.addEventListener("timeupdate",function(){
            $("#currentTime").text(formatTime(audioElement.currentTime));
            var progress = audioElement.currentTime/audioElement.duration;
            $('#mp3player .status span').css('width', progress * 100);
        });
        
        $('#play').click(function() {
            audioElement.play();
            $('#pause').show();
            $('#play').hide();
        });
        
        $('#pause').click(function() {
            audioElement.pause();
            $('#pause').hide();
            $('#play').show();
        });

        $('#next').click(function() {
            audioElement.currentTime += 10;
        });

        $('#prev').click(function() {
            audioElement.currentTime = audioElement.currentTime  - 10;
        });
    }

    // profiles nav buttons 
    $('.profile-menu').click(function(e){
        e.preventDefault();
        $('.col-lg-4').toggleClass('animated fadeIn');
        $('body').toggleClass('profiles-open');
        $('body').removeClass('share-open');
        $('.viewbook-share .inner').removeClass('animated fadeIn');
    });

    // profiles share 
    $('.share').click(function(e){
        e.preventDefault();
        $('body').removeClass('profiles-open');
        $('body').toggleClass('share-open');
        $('.viewbook-share .inner').toggleClass('animated fadeIn');
        $('.col-lg-4').removeClass('animated fadeIn');
    });
});

}

// format time function 

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    return [h, m > 9 ? m : h ? '0' + m : m || '0', s > 9 ? s : '0' + s].filter(a => a).join(':');
}

function landscapeCheck(){
    if(window.innerHeight > window.innerWidth){
        $('body.page-node-type-viewbook-').append('<div id="portrait"><div class="container"><div class="inner"><p>Please turn your device sideways.</p><img src="../../themes/custom/u_pacific/images/rotate1-min.png" alt="Turn your device sideways"><p>If you are on a desktop, please increase the size of your browser window.</p></div></div></div>')
    }
    else{
        $('#portrait').remove();
        $('#js-wrapper').sly('reload');
    }
}
var sly;
function startsly(){
sly = new Sly('#js-wrapper', {

    slidee:     $('#js-slideContainer'),  // Selector, DOM element, or jQuery object with DOM element representing SLIDEE.
	horizontal: true, // Switch to horizontal mode.

	// Item based navigation
	itemNav:        null,  // Item navigation type. Can be: 'basic', 'centered', 'forceCentered'.
	itemSelector:   null,  // Select only items that match this selector.
	smart:          false, // Repositions the activated item to help with further navigation.
	activateOn:     null,  // Activate an item on this event. Can be: 'click', 'mouseenter', ...
	activateMiddle: false, // Always activate the item in the middle of the FRAME. forceCentered only.

	// Scrolling
	scrollSource: $('#js-wrapper'),  // Element for catching the mouse wheel scrolling. Default is FRAME.
	scrollBy:     100,     // Pixels or items to move per one mouse scroll. 0 to disable scrolling.
	scrollHijack: 300,   // Milliseconds since last wheel event after which it is acceptable to hijack global scroll.
	scrollTrap:   true, // Don't bubble scrolling when hitting scrolling limits.

	// Dragging
	// dragSource:    null,  // Selector or DOM element for catching dragging events. Default is FRAME.
	mouseDragging: true, // Enable navigation by dragging the SLIDEE with mouse cursor.
	touchDragging: true, // Enable navigation by dragging the SLIDEE with touch events.
	releaseSwing:  true, // Ease out on dragging swing release.
	swingSpeed:    0.2,   // Swing synchronization speed, where: 1 = instant, 0 = infinite.
	elasticBounds: false, // Stretch SLIDEE position limits when dragging past FRAME boundaries.
	interactive:   null,  // Selector for special interactive elements.

	// Mixed options
	moveBy:        300,     // Speed in pixels per second used by forward and backward buttons.
	// speed:         300,       // Animations speed in milliseconds. 0 to disable animations.
	// easing:        'easeOutQuint', // Easing for duration based (tweening) animations.
	startAt:       null,    // Starting offset in pixels or items.
	keyboardNavBy: 'pages',    // Enable keyboard navigation by 'items' or 'pages'.

	// Classes
	draggedClass:  'dragged', // Class for dragged elements (like SLIDEE or scrollbar handle).
	activeClass:   'active',  // Class for active items and pages.
	disabledClass: 'disabled' // Class for disabled navigation elements.
    });

    sly.init();
}

// Version of viewbook 

$(document).ready(function(){
        // set version 
        if($('body').hasClass('page-node-29450')){
            Cookies.remove('viewbook_version');
        }
        if($('body').hasClass('page-node-29640')){
        Cookies.set('viewbook_version', 'future');
        }
        
        // if future - change links 
        if(Cookies.get('viewbook_version')){
            if($('#start-over-link').length){
                $('#start-over-link').attr('href', '../../viewbook-futuretigers');
            }
            if($('.share-viewbook .a2a_kit').length){
                var link = $('.share-viewbook .a2a_kit').attr('data-a2a-url') + '-futuretigers';
            $('.share-viewbook .a2a_kit').attr('data-a2a-url', link);
            }
            if($('.bottom-buttons').length){
                $('.bottom-buttons').html('<a target="_blank" class="view-majors" href="../../academics/majors-minors">Explore all Majors & Minors <i class="fas fa-external-link-alt"></i></a><a target="_blank" class="view-majors apply" href="../../admission/undergraduate">Apply Now or Finish Your Application<i class="fas fa-external-link-alt"></i></a>');
            }
        }
});

})(jQuery);