<?php
namespace Craft;

use Twig_Extension;

class svg extends \Twig_Extension {

  public function getName() {
    return Craft::t('SVG');
  }

  public function getFunctions() {
    return array(
      'svg' => new \Twig_Function_Method($this, 'svg', array('is_safe' => array('html'))),
      'loadsvg' => new \Twig_Function_Method($this, 'loadsvg', array('is_safe' => array('html')))
    );
  }

  public function svg($file, $fallback = null, $browser = null, $imageDir = null) {

    $imageDir = craft()->svg->directory('imagesDirectory','images','/assets/images');
    $spritesDir = craft()->svg->directory('spritesDirectory','sprites','/assets/images/sprites');
    $sytemRoot = getcwd();

    // Check file extension is svg, otherwise add the '.svg' string.
    $file = (strlen($file) > 4 && substr($file, -4) == '.svg') ? $file : $file.'.svg';

    // Checks to see if the file exists
    if (file_exists($sytemRoot.$imageDir.$file)) {
      // If the svg file exits, use this;
      $fileUrl = $sytemRoot.$imageDir.$file;
    } else if (file_exists($sytemRoot.$spritesDir.$file)) {
      $fileUrl = $sytemRoot.$spritesDir.$file;
    } else {
      $fileUrl = null;
    }

    $unsupportedBrowsers = $browser ? craft()->svg->browserCheck($browser) : false;

    // If this browser is supported and the file exists load in all the SVG content directly into the HTML
    if (!$unsupportedBrowsers && !is_null($fileUrl)) {
      return file_get_contents($fileUrl);
    } else if ( $fallback != 'disable' && $unsupportedBrowsers === true) {
      // Create an 'id' based on the filename. Slugify and remove extension
      $id = ElementHelper::createSlug(preg_replace('/.svg$/', '', $file));

      // Check the fallback is a string, otherwise use the symbol string as the image reference
      $fallback = is_string($fallback) ? $fallback : $id;
      $fallback = craft()->svg->fallbackImage($fallback, $imageDir);

      if ($fallback !== false) {
        // Use the fallback image
        return "<img id='".$id."' src='".$fallback."' alt='".$id."'>";
      }
    }
  }

  public function loadsvg($file = 'sprite-symbols', $id = null, $imageDir = null) {
    return $this->svg($file, null, null, $imageDir);
  }
}
