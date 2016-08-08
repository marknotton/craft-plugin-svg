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

  // Usage example 1 - {{ svg('logo', 'logo-dark.png', 'ie 9') }}
  // Outputs SVG content, except for then on IE9 the fallback image is used

  // Usage example 2 - {{ svg('logo', 'logo-dark.png', 'ie < 11') }}
  // Outputs SVG content, except for browsers that are IE11 and below... the fallback image is used

  // Usage example 3 - {{ svg('logo.svg', 'logo-dark.png') }}
  // Outputs SVG content, fallsback to .png image when on the unsupported browsers (ie and edge by default)

  // Usage example 4 - {{ svg('logo', null, 'edge') }}
  // Outputs SVG content, fallsback to a standard <img> element using the .svg file. Unless on Edge
  
  // Usage example 5 - {{ svg('logo', 'disable', 'chrome') }}
  // Outputs SVG content, when 'disabled is passed no fallback will be generated at all'

  public function svg($file, $fallback = null, $browsers = null, $dir = null) {

    // Default Directory
    $dir = is_null($dir) ? craft()->config->get('environmentVariables')["images"] : $dir;

    // Ensure the parsed directory only has one '/' at the end of the string
    $dir = rtrim($dir, '/') . '/';

    // Check file extension is svg
    $file = (strlen($file) > 4 && substr($file, -4) == '.svg') ? $file : $file.'.svg';

    // Set full file url
    $svgUrl = getcwd().$dir.$file;
    $spriteUrl = getcwd().$dir.'sprites/'.$file;

    // If svg can't be found, try looking in the sprites directory.
    $fileUrl = file_exists($svgUrl) ? $svgUrl : (file_exists($spriteUrl) ? $spriteUrl : null);

    // Check for supported browsers using the Browser plugin
    $unsupportedBrowsers = false;

    if (craft()->svg->plugin('browser') && isset($browsers)) {
      if (gettype($browsers) == 'array') {
        $unsupportedBrowsers = call_user_func_array(array(craft()->browser, 'is'), $browsers);
      } else {
        $unsupportedBrowsers = craft()->browser->is($browsers);
      }
    }

    // If this browser is supported and the file exists load in all the SVG content directly into the HTML
    if (!$unsupportedBrowsers && !is_null($fileUrl)) {
      // echo gettype(file_get_contents($svgUrl));
      return file_get_contents($fileUrl);
    } else {      
      // Create an 'id' based on the filename. Slugify and remove extension
      $id = ElementHelper::createSlug(preg_replace('/.svg$/', '', $file));

      // Fallbacks
      if ( $fallback != 'disable' ) {
        if (is_string($fallback) && isset($fallback)) {
          // Use the fallback image
          return "<img id='".$id."' src='".$dir.$fallback."' alt='".$id."'>";
        } else {
          // If there is no fallback image, use the .svg as an image.
          return "<img id='".$id."' src='".$dir.$file."' alt='".$id."'>";
        }
      }
    }
  }

  public function loadsvg($file = 'sprite-symbols', $id = null, $dir = null) {
    return $this->svg($file, null, null, $dir);
  }
}