<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/**
 * Sprout Lists config.php
 *
 * This file exists only as a template for the Sprout Lists settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'sprout-lists.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // The name to display in the control panel in place of the plugin name
    'pluginNameOverride' => 'Sprout Lists',

    // Create a relationship between a Sprout Subscriber and a matching
    // Craft User, based on the email address
    'enableUserSync' => false,

    // Subscriber Lists will be automatically created when a subscribe form
    // is submitted and no matching List is found.
    'enableAutoList' => false
];
