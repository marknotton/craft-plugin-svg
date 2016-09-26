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

    $symbol  = is_string(func_get_arg(0)) ? func_get_arg(0) : false;
    $fallback = true;
    $useFallbackImage = true;
    $size = true;
    $browser = false;
    $imageDir = craft()->svg->directory('imagesDirectory','images','/assets/images');
    $spritesDir = craft()->svg->directory('spritesDirectory','sprites','/assets/images/sprites');
    $symbolExists = file_exists(getcwd().($spritesDir.$symbol.'.svg'));

    // Remove the first argument
    if ($settings = func_num_args() == 2 ? func_get_arg(1) : false && !empty($settings)) {
      if (is_array($settings)) {
        // SETTINGS
        // If the second paramter is an associative array; extract the keys as variable names, and the values as values
        extract($settings);
      } else if ( craft()->svg->isSize($settings) === true || $settings === true ) {
        // SIZE
        // If settings contains one or two numbers only, assume you want to define the size
        $size = $settings;
      } else if (is_string($settings) || $settings === false ) {
        // BROWSER SETTINGS
        $fallback = true;
        $browser = $settings;
      }
    }

    // Only do a Browser check is symbol exists and browser is defined
    $unsupportedBrowsers = $symbolExists && $browser ? craft()->svg->browserCheck($browser) : false;

    // Define a fallback image
    if ($symbolExists !== false || $fallback !== false && $unsupportedBrowsers === true ) {
      // Check the fallback is a string, otherwise use the symbol string as the image reference
      $fallback = is_string($fallback) ? $fallback : $symbol;
      $fallback = craft()->svg->fallbackImage($fallback, $imageDir);
    }

    // Classes
    $classes = "class='".$symbol;

    if (isset($class)) {
      $classes .= " ".$class;
    }

    // Size
    $dimensions = '';


    if ($size !== false) {
      $size = is_string($size) ? explode(' ', $size) : $size;

      if (is_array($size)) {
        $width = $size[0];
        $height = count($size) == 1 ? $size[0] : $size[1];
      } else {
        $width = $height = $size;
      }

      // Dimensions
      if ($width !== 'null') {
        $dimensions .= ' width="'.$width.'"';
      }

      if ($height !== 'null') {
        $dimensions .= ' height="'.$height.'"';
      }

      if ($size === true) {
        $classes .= " svg-".$symbol."-size'";
      }
    }

    $classes .= "'";

    // Final checks
    if (!$symbolExists || $unsupportedBrowsers && $fallback !== false) {
      return '<img '.$classes.$dimensions.' src="'.$fallback.'" alt="'.$symbol.'">';
    } else if ($symbolExists && $fallback !== false){
      return '<svg '.$classes.$dimensions.'><use xlink:href="#'.$symbol.'"></use></svg>';
    }

  }

}
