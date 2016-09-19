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

  public function svg($file, $fallback = null, $browsers = null, $imageDir = null) {

    $settings = craft()->plugins->getPlugin('svg')->getSettings();

    // Default Directory
    if ( empty($imageDir) ) {
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
    }

    // Ensure the image directory only has one '/' at the end of the string
    $imageDir = rtrim($imageDir, '/') . '/';

    // Check file extension is svg, otherwise add the '.svg' string.
    $file = (strlen($file) > 4 && substr($file, -4) == '.svg') ? $file : $file.'.svg';

    // Set full file url
    $svgUrl = getcwd().$imageDir.$file;

    $fileUrl = null;

    // Checks to see if the file exists
    if (file_exists($svgUrl)) {
      // If the svg file exits, use this;
      $fileUrl = $svgUrl;
    } else {
      // Otherwise check the sprites directory
      $spriteDir = '/assets/images/sprites';


      if ( !empty($settings['spritesDirectory']) ) {
        // Check settings is defined and set this as the image directory
        $spriteDir = $settings['spritesDirectory'];
      } else if ( !empty(craft()->config->get('environmentVariables')["images"])) {
        // Check images Environment Variables is defined and set this as the iamge direcotry
        $spriteDir = craft()->config->get('environmentVariables')["images"].'/sprites';
      }

      // Ensure the sprites image directory only has one '/' at the end of the string
      $spriteDir = rtrim($spriteDir, '/') . '/';

      if (file_exists(getcwd().$spriteDir.$file)) {
        $fileUrl = getcwd().$spriteDir.$file;
      }
    }

    // When false, no browsers will be checked.
    $unsupportedBrowsers = false;

    // Define $browserPlugin as 'true' if the Browser Plugin is installed and enabled.
    if ($browserPlugin = craft()->plugins->getPlugin('browser', false)) {
      $browserPlugin = $browserPlugin->isInstalled && $browserPlugin->isEnabled;
    }

    // If browser plugin is installed and browsers have been passed, do a browser check
    if ($browserPlugin && isset($browsers)) {
      if (gettype($browsers) == 'array') {
        $unsupportedBrowsers = call_user_func_array(array(craft()->browser, 'is'), $browsers);
      } else {
        $unsupportedBrowsers = craft()->browser->is($browsers);
      }
    }

    // If this browser is supported and the file exists load in all the SVG content directly into the HTML
    if (!$unsupportedBrowsers && !is_null($fileUrl)) {
      return file_get_contents($fileUrl);
    } else {
      // Create an 'id' based on the filename. Slugify and remove extension
      $id = ElementHelper::createSlug(preg_replace('/.svg$/', '', $file));

      // Fallbacks
      if ( $fallback != 'disable' ) {
        if (is_string($fallback) && isset($fallback) && file_exists(getcwd().$imageDir.$fallback)) {
          // Use the fallback image
          return "<img id='".$id."' src='".$imageDir.$fallback."' alt='".$id."'>";
        } else if (file_exists(getcwd().$imageDir.$file)) {
          // If there is no fallback image, use the .svg as an image source.
          return "<img id='".$id."' src='".$imageDir.$file."' alt='".$id."'>";
        } else {
          // If the fallback image doesn't exists, nore does the svg, use the SVG filename and search for any image format matching the name.
          foreach (['png', 'jpg', 'gif'] as $format) {
            $fallbackUrl = $imageDir.$id.'.'.$format;
            if (file_exists(getcwd().$fallbackUrl)) {
              return "<img id='".$id."' src='".$fallbackUrl."' alt='".$id."'>";
              break;
            }
          }
        }
      }
    }
  }

  public function loadsvg($file = 'sprite-symbols', $imageDir = null) {
    return $this->svg($file, 'disable', false, $imageDir);
  }
}
