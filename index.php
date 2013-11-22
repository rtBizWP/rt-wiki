<?php

/*
  Plugin Name: rtWiki
  Description: Creates a Wiki CPT. Check for pages inside it. If not found will create it. Post filtering for different user groups
  Version: 1.0
  Author: Prannoy Tank
 */

require_once dirname(__FILE__) . '/lib/wiki-CPT.php';
require_once dirname(__FILE__) . '/lib/user-groups.php';
require_once dirname(__FILE__) . '/lib/wiki-single-custom-template.php';
require_once dirname(__FILE__) . '/lib/wiki-404-redirect.php';
require_once dirname(__FILE__) . '/lib/wiki-post-filtering.php';
require_once dirname(__FILE__) . '/lib/wiki-post-subscribe.php';
require_once dirname(__FILE__) . '/lib/wiki-singlepost-content.php';
require_once dirname(__FILE__) . '/lib/class-daily-changes.php';
require_once dirname(__FILE__) . '/lib/wiki-sidebar.php';
require_once dirname(__FILE__) . '/lib/wiki-widgets.php';


wp_register_script('rtwiki-custom-script', plugins_url('/js/rtwiki-custom-script.js', __FILE__), array('jquery'));
wp_enqueue_script('rtwiki-custom-script');

if (!defined('RC_TC_BASE_FILE'))
    define('RC_TC_BASE_FILE', __FILE__);
if (!defined('RC_TC_BASE_DIR'))
    define('RC_TC_BASE_DIR', dirname(RC_TC_BASE_FILE));
if (!defined('RC_TC_PLUGIN_URL'))
    define('RC_TC_PLUGIN_URL', plugin_dir_url(__FILE__));


