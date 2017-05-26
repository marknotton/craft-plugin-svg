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

    $systemPath = craft()->plugins->getPlugin('svg')->getSettings()->relativeLocaleDirectories ? getcwd() : $_SERVER['DOCUMENT_ROOT'];

    // Check file extension is svg, otherwise add the '.svg' string.
    $file = (strlen($file) > 4 && substr($file, -4) == '.svg') ? $file : $file.'.svg';
    // Checks to see if the file exists
    if (file_exists($systemPath.$imageDir.$file)) {
      // If the svg file exits, use this;
      $fileUrl = $systemPath.$imageDir.$file;
    } else if (file_exists($systemPath.$spritesDir.$file)) {
      $fileUrl = $systemPath.$spritesDir.$file;
    } else {
      $fileUrl = null;
    }

    $unsupportedBrowsers = $browser ? craft()->svg->browserCheck($browser) : false;

    // If this browser is supported and the file exists load in all the SVG content directly into the HTML
    if (!$unsupportedBrowsers && !is_null($fileUrl)) {
      $content = file_get_contents($fileUrl);
      $content = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $content);
      $content = preg_replace('/<!--(.*)-->/Uis', '', $content);
      return $content;
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
