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

			  public function svg($file, $classes = null) {

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

					if (is_null($fileUrl)) { return false; }

					$svgContent = file_get_contents($fileUrl);

					if ( empty($classes) ) {
						return $svgContent;
					} else {
						$dom = new \DomDocument();
						$dom->loadXML($svgContent);

						foreach ($dom->getElementsByTagName('svg') as $element) {
							if ($element->hasAttribute('class') ) {

								// Array of classes to add
								$newClasses = explode(" ", $classes);

								// Array of existing classes
								$existingClasses = explode(" ", $element->getAttribute('class'));

								// Set the class attribute, whilst excluding any duplicates
								$element->setAttribute('class', rtrim(implode(' ', array_unique(array_merge($newClasses,$existingClasses), SORT_REGULAR))));

							} else {

						    $element->setAttribute('class', rtrim($classes));
							}
					    return $dom->saveXML();
					    exit;
						}
					}

			  }

			  public function loadsvg($file = 'sprite-symbols', $class = null) {
			    return $this->svg($file, $class);
			  }
			}
