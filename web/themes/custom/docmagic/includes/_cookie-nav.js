/* ---------------------------------------------------------
 * Update login links with D6 login status cookie
 * ---------------------------------------------------------
 */

(function ($) {

    // URL and text for login links
    var Links = {
        dashboard: {
            url: '/webservices/admin',
            text: 'Dashboard'
        },
        logout: {
            url: '/webservices/logout',
            text: 'Log out'
        }
    };

    /* ---------------------------------------------------------
     * Update the top login button
     * ---------------------------------------------------------
     */

    function updateTopLoginButton() {

        var $header = $('#header');
        if (! $header.length) { return 0;}

        var $content = $header.find('.topbar-content');
        if (! $content.length) { return 0; }

        var $loginBox = $header.find('.topbar-login-box');
        if (! $loginBox.length) { return 0; }

        $content.addClass('topbar-logged-in');

        var html
            = '<div class="dashboard-links">'
            + '<a href="' + Links.dashboard.url + '">' + Links.dashboard.text + '</a>'
            + ' / '
            + '<a href="' + Links.logout.url + '">' + Links.logout.text + '</a>'
            + '</div>';

        // fade and replace
        $loginBox.find('> a').fadeOut(300, function() {
            $(this).remove();
            $loginBox.html( html );
        });
    }

    /* ---------------------------------------------------------
     * Update the primary nav login menu
     * ---------------------------------------------------------
     */

    function updatePrimaryNavLogin() {

        var $header = $('#header');
        if (! $header.length) { return 0; }

        var $headerMain = $header.find('.header-main');
        if (! $headerMain.length) { return 0; }

        var $link = $headerMain.find('.gva_menu > li.menu-item:last-of-type a');
        if (! $link.length) { return 0; }

        $headerMain.addClass('header-main-logged-in');

        $link.attr('href', Links.dashboard.url).text( Links.dashboard.text );
    }

    /* ---------------------------------------------------------
     * Update the mobile login button
     * ---------------------------------------------------------
     */

    function updateMobileLoginButton() {

        var $header = $('#header');
        if (! $header.length) { return 0; }

        var $mobileLogin = $header.find('.docmagic-offcanvas-login-box');
        if (! $mobileLogin.length) { return 0; }

        var html
            = '<div class="dashboard-links">'
            + '<a href="' + Links.dashboard.url + '">' + Links.dashboard.text + '</a>'
            + ' / '
            + '<a href="' + Links.logout.url + '">' + Links.logout.text + '</a>'
            + '</div>';

        // fade and replace
        $mobileLogin.find('> a').fadeOut(300, function() {
            $(this).remove();
            $mobileLogin.html( html );
        });
    }

    /* ---------------------------------------------------------
     * Trigger update functions at page load
     * ---------------------------------------------------------
     */

    $(document).ready(function() {

        var x = dmcheckd6; // variable generated in un-cached block

        if (x == "loggedin") {
            updateTopLoginButton();
            updatePrimaryNavLogin();
            updateMobileLoginButton();
        }
    });

})(jQuery);
