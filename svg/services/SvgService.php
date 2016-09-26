<?php
namespace Craft;

class SvgService extends BaseApplicationComponent {
  // For conditions that are dependant on other plugins,
  // perform a quick check to see if a plugin is installed and enabled
  public function plugin($name) {
    $plugin = craft()->plugins->getPlugin($name, false);
    return $plugin->isInstalled && $plugin->isEnabled;
  }

  public function directory($setting, $envVar, $default) {

    $settings = craft()->plugins->getPlugin('svg')->getSettings();
    
    // Default Directory
    if ( !empty($settings[$setting]) ) {
      // Check settings is defined and set this as the image directory
      $directory = $settings[$setting];
    } else if ( !empty(craft()->config->get('environmentVariables')[$envVar])) {
      // Check images Environment Variables is defined and set this as the iamge direcotry
      $directory = craft()->config->get('environmentVariables')[$envVar];
    } else {
      // Fallback to this image directory
      $directory = $default;
    }

    // Ensure the image directory only has one '/' at the end of the string
    $directory = rtrim($directory, '/') . '/';

    return $directory;
  }

  // Check is the setting is a number and or a unit
  public function isSize($setting) {
    $sizes = explode(' ', $setting);
    return $sizes == array_filter($sizes, function($num) {
      foreach (['px', 'cm', 'mm', '%', 'ch', 'pc', 'in', 'em', 'rem', 'pt', 'pc', 'ex', 'vw', 'vh', 'vmin', 'vmax'] as $try) {
        if (is_numeric($num) || substr($num, -1*strlen($try))===$try) {
          return true;
        }
      }
    });
  }

  public function browserCheck($browser) {
    // Check to see if the browser plugin is installed and enabled
    if ($browserPlugin = craft()->plugins->getPlugin('browser', false)) {
      $browserPlugin = $browserPlugin->isInstalled && $browserPlugin->isEnabled;
    }

    // Return the results of the browser criteria check
    return $browserPlugin ? craft()->browser->is($browser) : false;
  }

  public function fallbackImage($fallback, $directory) {
    // Fallback image
    $imageFormats = array('png', 'jpg', 'gif');
    $imageHasExtension = in_array(strtolower(pathinfo($fallback, PATHINFO_EXTENSION)), $imageFormats);

    if ($imageHasExtension) {
      if ( file_exists(getcwd().$directory.$fallback) ) {
        // If the file has an extension and exists, return the image url
        return $directory.$fallback;
      } else {
        return false;
      }
    } else {
      // If the fallback string doesn't have an extension find a file that has the same filename and any extension
      foreach ($imageFormats as $format) {
        if (file_exists(getcwd().$directory.$fallback.'.'.$format)) {
          return $directory.$fallback.'.'.$format;
        }
      }
      return false;
    }
  }
}
