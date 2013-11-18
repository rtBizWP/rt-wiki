<?php

/*
  Plugin Name: rtWiki
  Description: Creates a Wiki CPT. Check for pages inside it. If not will create it. Post filtering for different user groups
  Version: 1.0
  Author: Prannoy Tank
 */

require_once dirname(__FILE__) . '/lib/wiki-CPT.php';
require_once dirname(__FILE__) . '/lib/user-groups.php';
require_once dirname(__FILE__) . '/lib/wiki-single-custom-template.php';
require_once dirname(__FILE__) . '/lib/wiki-404-redirect.php';
require_once dirname(__FILE__) . '/lib/wiki-post-filtering.php';
require_once dirname(__FILE__) . '/lib/wiki-post-subscribe.php';


wp_register_script('rtwiki-custom-script', plugins_url('/js/rtwiki-custom-script.js', __FILE__), array('jquery'));
wp_enqueue_script('rtwiki-custom-script');



