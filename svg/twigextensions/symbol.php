<?php
namespace Craft;

use Twig_Extension;

class symbol extends \Twig_Extension {

  public function getName() {
    return Craft::t('Symbol');
  }

  public function getFunctions() {
    return array(
      'icon' => new \Twig_Function_Method($this, 'symbol', array('is_safe' => array('html'))),
      'symbol' => new \Twig_Function_Method($this, 'symbol', array('is_safe' => array('html')))
    );
  }


  // {{ 
  //   symbol('logo',                 // The name of the symbol id (usually the name of the file)
  //     {
  //       fallback : 'logo.png',     // Fallback image, if a file extension isn't used, any files with the same filenmae and a png, jpg, or gif extensioon will be used
  //       directory : '',            // Directoary for where the fallback image lives
  //       browsers : 'ie 9',         // Fallback images will only apply to specific browsers that have been defined here
  //       size : true                // true | false | '100 null' | 'null 100' | '250 400' | [100, 200] - Define given dimensions in a variety of ways. true = size class. false = force no size class to be added
  //       class : 'test'             // additional class names to the svg element
  //     }
  //   ) 
  // }}
  // {{ symbol('logo') }}             // Will simply use the logo symbol. By default the size class will be added automatically
  // {{ symbol('logo', true) }}       // Will automatically try to find a fallback image based of the first paramater string. And will fallback to images for ie 9 and 10
  // {{ symbol('logo', 'firefox') }}  // Will automatically try to find a fallback image for firefox browsers

  public function symbol() {

    // Atleast one symbol sting arugment should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    // The first argument should be a string
    if (is_string(func_get_arg(0))) {
      $symbol = func_get_arg(0);
    } else { 
      return false;
    }

    // Remove the first argument
    $arguments = array_slice(func_get_args(), 1);

    if (!empty($arguments) && is_array($arguments[0])) {
      // If the second paramter is an array, assume these are settings and extract the keys as variable names, and the values as values
      extract($arguments[0]);
    } else if ( !empty($arguments)) {
      if ( is_bool($arguments[0]) && $arguments[0] == true ) {
        // If the second paramter is a 'true' boolean, assume you want to find and use a fallback image
        $fallback = true;
        $browser = 'ie 9 10';
      } else if ( is_string($arguments[0]) ) {
        // If the second parameter is a string, and contains one or two numbers only, assume you want to define the size
        $temp = explode(' ', $arguments[0]);
        if (count($temp) <= 2 && $temp == array_filter($temp, function($num) {
          // Returns true is argument is a number, or a string that contains a unit 
          return is_numeric($num) || preg_match('/'.implode('|', ['px', 'cm', 'mm', '%', 'ch', 'pc', 'in', 'em', 'rem', 'pt', 'pc', 'ex', 'vw', 'vh', 'vmin', 'vmax']).'$/', $num); 
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
    // Check if the global enviroment variable is defined 
    $defaultDirectory = craft()->config->get('environmentVariables')["images"];
    // If is is, use that, otherwise point to the commonly usedt assets/images direcoctory
    $defaultDirectory = isset($defaultDirectory) ? $defaultDirectory : '/assets/images/';
    // If a "directory" option has been defined, use this instead of the global enviroment variable defined in the genreal config
    $dir = !isset($directory) ?  $defaultDirectory : $directory;
    // Ensure the parsed directory only has one '/' at the end of the string
    $dir = rtrim($dir, '/') . '/';

    // Browser
    // Only revert to a fallback image if the given browser criteria is matched
    $unsupportedBrowsers = null;

    if (craft()->svg->plugin('browser')) {
      if (isset($browsers)) {
        $unsupportedBrowsers = craft()->browser->is($browsers);
      }
      if (isset($browser)) {
        $unsupportedBrowsers = craft()->browser->is($browser);
      }
    }
    
    // Fallback image
    // By default, fallback images are off (false). 
    // Define "true" if you want to attempt to find a relivent fallback aimge
    // Define a string if you want to use a specific image. Ommiting a file extension will result in autoatically using one if the file exists
    $imageFormats = array('png', 'jpg', 'gif');

    if (isset($fallback) && $fallback != false && $unsupportedBrowsers ) {       
      // Define the fallback if it's a string. if not, use the symbol string instead
      $fallback = is_string($fallback) ? $fallback : $symbol;

      // If there is an extension on the fallback string
      if (in_array(strtolower(pathinfo($fallback, PATHINFO_EXTENSION)), $imageFormats)) {
        // check if the file exists
        if (file_exists(getcwd().($dir.$fallback))) {
          // If it does, return the file name and it's directory
          $fallback = $dir.$fallback;
        } else {
          // Otherwise return false
          $fallback = false;
        }
      } else {
        // If the fallback string doesn't have an extension find a file that has one, and the same file name
        foreach (['png', 'jpg', 'gif'] as $format) {
          if (file_exists(getcwd().$dir.$fallback.'.'.$format)) {
            // Once a file extension with the appropriate file extension exists break this loop and define the $fallback variable
            $fallback = $dir.$fallback.'.'.$format;
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
    if (isset($size) && $size != false) {
      
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
    if ($unsupportedBrowsers && $fallback) {
      return '<img '.$classes.$dimensions.' src="'.$fallback.'" alt="'.$symbol.'">';
    } else {
      return '<svg '.$classes.$dimensions.'><use xlink:href="#'.$symbol.'"></use></svg>';
    }

  }
 
}