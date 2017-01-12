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
    $fallback = false;
    $setSize = true;
    $browserCriteria = false;
    $imageDir = craft()->svg->directory('imagesDirectory','images','/assets/images');
    $spritesDir = craft()->svg->directory('spritesDirectory','sprites','/assets/images/sprites');
    $systemPath = craft()->plugins->getPlugin('svg')->getSettings()->relativeLocaleDirectories ? getcwd() : $_SERVER['DOCUMENT_ROOT'];

    $symbolExists = file_exists($systemPath.($spritesDir.$symbol.'.svg'));

    // Remove the first argument
    if ($settings = func_num_args() == 2 ? func_get_arg(1) : false && !empty($settings)) {
      if (is_array($settings)) {
        // SETTINGS
        // If the second paramter is an associative array; extract the keys as variable names, and the values as values
        extract($settings);
      } else if ( craft()->svg->isSize($settings) === true || $settings === true ) {
        // SIZE
        // If settings contains one or two numbers only, assume you want to define the size
        $setSize = $settings;
      } else if (is_string($settings)) {
        // BROWSER SETTINGS
        $fallback = true;
        $browserCriteria = $settings;
      } else if ($settings === false) {
        // NO FALLBACK
        $fallback = false;
      }
    }

    // Define a fallback image
    if ($symbolExists && $fallback !== false && is_string($browserCriteria) && craft()->svg->browserCheck($browserCriteria) ) {
      // Check the fallback is a string, otherwise use the symbol string as the image reference
      $fallbackString = is_string($fallback) ? $fallback : $symbol;
      $fallback = craft()->svg->fallbackImage($fallbackString, $imageDir);
    } else {
      $fallback = false;
    }

    // Classes
    $classes = "class='".$symbol;

    if (isset($class)) {
      $classes .= " ".$class;
    }

    // Size
    $dimensions = '';

    if ($setSize !== false ) {
      $size = is_string($setSize) ? explode(' ', $setSize) : $setSize;

      if (is_array($size)) {
        $width = $size[0];
        $height = count($size) == 1 ? $size[0] : $size[1];
      } else if ($size !== true ) {
        $width = $height = $size;
      }

      // Dimensions
      if (isset($width) && $width !== 'null') {
        $dimensions .= ' width="'.$width.'"';
      }

      if (isset($height) && $height !== 'null') {
        $dimensions .= ' height="'.$height.'"';
      }

      if ($setSize === true ) {
        $classes .= " svg-".$symbol."-size";
      }
    }

    if (!empty($dimensions)) { $dimensions .= ' '; }

    $classes .= "'";

    // Final checks
    if (!$symbolExists && $fallback === false) {
      return false;
    } else if ( $fallback !== false) {
      return '<img '.$classes.$dimensions.' src="'.$fallback.'" alt="'.$symbol.'">';
    } else if ($symbolExists){
      return '<svg '.$classes.$dimensions.'><use xlink:href="#'.$symbol.'"></use></svg>';
    }

  }

}
