<?php
/**
 * Check DM cookie
 * - checks for the presence of the D6 logged in cookie
 * - args: debug boolean - set to true to read the D8 cookie instead
 */
function checkCookie() {
  if (isset($_COOKIE['DMLOGGEDIN']) && $_COOKIE['DMLOGGEDIN'] == 'loggedin') {
      return true;
  }

  return false;
}