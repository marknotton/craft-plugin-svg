<?php
namespace Craft;

use Twig_Extension;

class sprite extends \Twig_Extension {

  public function getName() {
    return Craft::t('Sprite');
  }

  public function getFunctions() {
    return array(
      'sprite' => new \Twig_Function_Method($this, 'sprite', array('is_safe' => array('html'))),
      'symbol' => new \Twig_Function_Method($this, 'sprite', array('is_safe' => array('html')))
    );
  }

  public function sprite() {

    // Atleast one symbol string arugment should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    // The first argument should be a string
    if (is_string(func_get_arg(0))) {
      $symbol = func_get_arg(0);
    } else {
      return false;
    }

    $fallback = true;
    $symbolExists = true;
    $size = true;


    // Default Directory
    if ( !empty($settings['imagesDirectory']) ) {
      // Check settings is defined and set this as the image directory
      $imageDir = $settings['imagesDirectory'];
    } else if ( !empty(craft()->config->get('environmentVariables')["images"])) {
      // Check images Environment Variables is defined and set this as the iamge direcotry
      $imageDir = craft()->config->get('environmentVariables')["images"];
    } else {
      // Fallback to this image directory
      $imageDir = '/assets/images';
    }

    // Ensure the image directory only has one '/' at the end of the string
    $imageDir = rtrim($imageDir, '/') . '/';

    // Remove the first argument
    $arguments = array_slice(func_get_args(), 1);

    if (!empty($arguments) && is_array($arguments[0])) {
      // If the second paramter is an array, assume these are settings and extract the keys as variable names, and the values as values
      extract($arguments[0]);
    } else if ( !empty($arguments)) {

      if ( is_bool($arguments[0]) && $arguments[0] == true ) {
        // If the second paramter is a 'true' boolean, assume you want to find and use a fallback image
        $fallback = true;
        $browser = false;
      } else if ( is_string($arguments[0]) || is_bool($arguments[0])) {
        // If the second parameter is a string, and contains one or two numbers only, assume you want to define the size
        $temp = explode(' ', $arguments[0]);

        if (is_bool($arguments[0]) || count($temp) <= 2 && $temp == array_filter($temp, function($num) {
          foreach (['px', 'cm', 'mm', '%', 'ch', 'pc', 'in', 'em', 'rem', 'pt', 'pc', 'ex', 'vw', 'vh', 'vmin', 'vmax'] as $try) {
            if (is_numeric($num) || substr($num, -1*strlen($try))===$try) {
              return true;
              break;
            }
          }
        })) {
          $size = $arguments[0];
        } else {
          // Otherwise assume the string are browser settings
          $fallback = true;
          $browser = $arguments[0];
        }
      }
    }

    // Directory
    if ( empty($spritesDir) ) {
      if ( !empty($settings['spritesDirectory']) ) {
        // Check settings is defined and set this as the image directory
        $spritesDir = $settings['spritesDirectory'];
      } else if ( !empty(craft()->config->get('environmentVariables')["images"])) {
        // Check images Environment Variables is defined and set this as the iamge direcotry
        $spritesDir = craft()->config->get('environmentVariables')["images"].'/sprites';
      }
    } else {
      $spritesDir = '/assets/images/sprites';
    }
    // Ensure the sprites image directory only has one '/' at the end of the string
    $spritesDir = rtrim($spritesDir, '/') . '/';

    // Check to see if the symbol file even exists
    if (!file_exists(getcwd().($spritesDir.$symbol.'.svg'))) {
      $symbolExists = false;
    }

    // Browser
    // Only revert to a fallback image if the given browser criteria is matched
    $unsupportedBrowsers = false;

    // Don't bother doing a browser check if the symbol doesn't exist
    if ($symbolExists) {

      // Define $browserPlugin as 'true' if the Browser Plugin is installed and enabled.
      if ($browserPlugin = craft()->plugins->getPlugin('browser', false)) {
        $browserPlugin = $browserPlugin->isInstalled && $browserPlugin->isEnabled;
      }

      if ($browserPlugin && isset($browser)) {
        $unsupportedBrowsers = craft()->browser->is($browser);
      }
    }

    // Fallback image
    $imageFormats = array('png', 'jpg', 'gif');

    if (!$symbolExists || $fallback !== false && $unsupportedBrowsers ) {
      // Define the fallback if it's a string. if not, use the symbol string instead
      $fallback = is_string($fallback) ? $fallback : $symbol;
      // If there is an extension on the fallback string
      if (in_array(strtolower(pathinfo($fallback, PATHINFO_EXTENSION)), $imageFormats)) {
        // check if the file exists
        if (file_exists(getcwd().($spritesDir.$fallback))) {
          // If it does, return the file name and it's directory
          $fallback = $spritesDir.$fallback;
        } else {
          // Otherwise return false
          $fallback = false;
        }
      } else {

        // If the fallback string doesn't have an extension find a file that has one, and the same file name
        foreach (['png', 'jpg', 'gif'] as $format) {
          if (file_exists(getcwd().$imageDir.$fallback.'.'.$format)) {
            // Once a file extension with the appropriate file extension exists break this loop and define the $fallback variable
            $fallback = $imageDir.$fallback.'.'.$format;
            break;
          }
        }
        // If a specific fallback image, without an exntesion is passed, and one suitable image can't be found... return false
        if (!in_array(strtolower(pathinfo($fallback, PATHINFO_EXTENSION)), $imageFormats)) {
          $fallback = false;
        }
      }
    } else {
      $fallback = false;
    }


    // Size
    if ($size !== false) {

      if (is_string($size)){
        $size = explode(' ', $size);
      }

      if (is_array($size)) {
        $width = $size[0];
        $height = count($size) == 1 ? $size[0] : $size[1];
      } else {
        $width = $size;
        $height = $size;
      }

    } else {
      // Default size option
      $size = false;
    }


    // Classes
    $classes = "class='".$symbol."";

      if (isset($class) && is_bool($size) && $size == true) {
        $classes .= " ".$class." svg-".$symbol."-size";
      } else if (isset($class)) {
        $classes .= " ".$class;
      } else if ($size == 'auto') {
        $classes .= " svg-".$symbol."-size";
      }

    $classes .= "'";

    // Dimensions
    $dimensions = '';
    // If width is defined and not null, add it to the dimensioons string
    if (isset($width) && $width != 'null') {
      $dimensions .= ' width="'.$width.'"';
    }
    // If height is defined and not null, add it to the dimensioons string
    if (isset($height) && $height != 'null') {
      $dimensions .= ' height="'.$height.'"';
    }

    // Final checks
    if (!$symbolExists && $unsupportedBrowsers && $fallback !== false) {
      return '<img '.$classes.$dimensions.' src="'.$fallback.'" alt="'.$symbol.'">';
    } else if ($symbolExists){
      return '<svg '.$classes.$dimensions.'><use xlink:href="#'.$symbol.'"></use></svg>';
    }

  }

}
