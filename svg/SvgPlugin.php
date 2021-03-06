<?php
namespace Craft;

class SvgPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('SVG');
  }

  public function getVersion() {
    return '0.1';
  }

  public function getSchemaVersion() {
    return '0.1';
  }

  public function getDescription() {
    return 'Impliment SVG files and symbols quickly, with browser checks and fallback images.';
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }

  public function getDocumentationUrl() {
    return 'https://github.com/marknotton/craft-plugin-svg';
  }

  public function getReleaseFeedUrl() {
    return 'https://raw.githubusercontent.com/marknotton/craft-plugin-svg/master/svg/releases.json';
  }

  public function getSettingsHtml() {
    return craft()->templates->render('svg/settings', array(
      'settings' => $this->getSettings()
    ));
  }

  protected function defineSettings() {
    return array(
      'imagesDirectory' => array(AttributeType::String, 'default' => ''),
      'spritesDirectory' => array(AttributeType::String, 'default' => ''),
      'relativeLocaleDirectories' => array(AttributeType::Bool, 'default' => false)
    );
  }

  public function addTwigExtension() {
    Craft::import('plugins.svg.twigextensions.svg');
    Craft::import('plugins.svg.twigextensions.sprite');
    return array(
      new svg(),
      new sprite()
    );
  }
}
