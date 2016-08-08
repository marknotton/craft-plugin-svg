<?php
namespace Craft;

class SvgPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('SVG');
  }

  public function getVersion() {
    return '0.1';
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

  public function addTwigExtension() {
    Craft::import('plugins.svg.twigextensions.svg');
    Craft::import('plugins.svg.twigextensions.symbol');
    return array(
      new svg(),
      new symbol()
    );
  }
}
