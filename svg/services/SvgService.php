<?php
namespace Craft;

class SvgService extends BaseApplicationComponent {
  // For conditions that are dependant on other plugins, 
  // perform a quick check to see if a plugin is installed and enabled
  public function plugin($name) {
    $plugin = craft()->plugins->getPlugin($name, false);
    return $plugin->isInstalled && $plugin->isEnabled;
  }
}