(function ($) {


    // main nav dropdwown buttons
    $('#navigation .dropdown-toggle').click(function(){
        var label = $(this).attr('id');
        label = label.replace('-links', '');
        label = label.replace('-', ' ');
        label = label.replace('-', ' ');
        ga('send', 'event', 'nav', 'Open Dropdown', label);
    });

    // main nav dropdwown links
    $('#navigation .dropdown-menu a').click(function(){
        var label = $(this).text();
        var category = $(this).attr('data-menu') + ' Nav';
        category = category.replace('-', ' ');
        category = category.replace('-', ' ');
        ga('send', 'event', category, 'Click', label);
    });



    // Static footer 
    $('.fixed-footer a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Static Footer', 'Click', label);
    });


    // Tools 
    $('#block-toollist a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'External Links', 'Click', label);
    });

    // Social 
    $('.social-networks a').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Social', 'Click', label);
    });

    // homepage gallery
    $('#block-homegridboxacademics a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Homepage Gallery', 'Click', label);
    });
    $('#block-homegridboxacademics h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Homepage Gallery', 'Click', label);
    });

    // homepage tuition
    $('#block-homepageaffordability .video-block a').click(function(){
        ga('send', 'event', 'Homepage Quicklink', 'Click', 'Tution');
    });

    // Home yellow menu 
    $('#block-lifeatwhittierhome a.top-level').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Homepage Yellow Menu', 'Open', label);
    });
    $('#block-lifeatwhittierhome .sub-menu li a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Homepage Yellow Menu', 'Click', label);
    });

    // Homepage News
    $('#block-views-block-news-blocks-block-1 a').click(function(){
        var label = $(this).find('h3').text();
        ga('send', 'event', 'News', 'Click', label);
    });

    // homepage join the campaign
        $('#block-homepagenewscallout .video-block a').click(function(){
            ga('send', 'event', 'Homepage Quicklink', 'Click', 'Join the Campaign');
        });

    // Admission purple menu 
    $('#block-admissionpurplemenu .purple-menu a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Admission Subnav', 'Click', label);
    });

    // Admission Gallery
    $('#block-deeperdive a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Admission Gallery', 'Click', label);
    });
    $('#block-deeperdive h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Admission Gallery', 'Click', label);
    });

    // admission image 
    $('#block-studentprofile1 a.lightbox').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Video', 'Play', 'First Year at Whittier');
    });

    // yellow sidebar menus 
    // $('#block-views-block-department-sidebar-left-menus-block-1 .ico-plus').click(function(){
    //     var label = $(this).parent().find('> a').text();
    //     var department = $('#block-views-block-department-sidebar-left-menus-block-1 .first-link a').text();
    //     ga('send', 'event', department + ' Sidebar Menu', 'Open', label);
    // });
    // $('#block-views-block-department-sidebar-left-menus-block-1 .menu-item a').click(function(){
    //     var label = $(this).text();
    //     var department = $('#block-views-block-department-sidebar-left-menus-block-1 .first-link a').text();
    //     ga('send', 'event', department + ' Sidebar Menu', 'Click', label);
    // });

    // Admission Quick link
    $('#block-campustouradmission .video-block a').click(function(){
        ga('send', 'event', 'Admission Quicklink', 'Click', 'Visit Whittier');
    });

    // admission slider menu 
    $('#block-studentpathways a.nav-link').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Admission Yellow Menu', 'Open', label);
    });
    $('#block-studentpathways a.learn-more').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Admission Yellow Menu', 'Click', label);
    });

    // about purple menu 
    $('#block-aboutpurple2 .purple-menu a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'About Subnav', 'Click', label);
    });

    // about slider menu 
    $('#block-explorethearea a.nav-link').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'About Yellow Menu', 'Open', label);
    });
    $('#block-explorethearea a.learn-more').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'About Yellow Menu', 'Click', label);
    });

    // About Gallery
    $('#block-aboutgrid3column a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'About Gallery', 'Click', label);
    });
    $('#block-aboutgrid3column h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'About Gallery', 'Click', label);
    });

    // About Quick link
    $('#block-aboutfactsfigures .video-block a').click(function(){
        ga('send', 'event', 'About Quicklink', 'Click', 'Facts and Figures');
    });
    
    // academics purple menu 
    $('#block-academicspurplemenu .purple-menu a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Academics Subnav', 'Click', label);
    });

    // academics Gallery
    $('#block-academicexperience-3 a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Academics Gallery', 'Click', label);
    });
    $('#block-academicexperience-3 h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Academics Gallery', 'Click', label);
    });

    // academics slider menu 
    $('#block-academicsyellowmenu a.nav-link').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Academics Yellow Menu', 'Open', label);
    });
    $('#block-academicsyellowmenu a.learn-more').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Academics Yellow Menu', 'Click', label);
    });

    // academic video 
    $('#block-learningoutsidetheclassroom a.lightbox').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Video', 'Play', 'Learning While Doing');
    });

    // life at whittier purple menu 
    $('#block-campuslifepurplemenu .purple-menu a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Life at Whittier Subnav', 'Click', label);
    });

    // Life at Whitter Gallery
    $('#block-campuslifesixboxgrid a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Life at Whittier Gallery', 'Click', label);
    });
    $('#block-campuslifesixboxgrid h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Life at Whittier Gallery', 'Click', label);
    });
    //life at whittier video 
    $('#block-studentprofile a.lightbox').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Video', 'Play', 'Whittier First Hand');
    });

    //Life at Whittier Quick link
    $('#block-traditionstherock .video-block a').click(function(){
        ga('send', 'event', 'Life at Whittier Quicklink', 'Click', 'Traditions');
    });

    // alumni Gallery
    $('#block-alumnisixboxgrid a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Alumni Gallery', 'Click', label);
    });
    $('#block-alumnisixboxgrid h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Alumni Gallery', 'Click', label);
    });

    // giving Gallery
    $('#block-givingsixgridbox a.slider-click').click(function(){
        var label = $(this).attr('aria-label');
        ga('send', 'event', 'Giving Gallery', 'Click', label);
    });
    $('#block-givingsixgridbox h3 a').click(function(){
        var label = $(this).text();
        ga('send', 'event', 'Giving Gallery', 'Click', label);
    });


})(jQuery);
