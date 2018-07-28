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

			  public function svg($file, $classes = null, $id = false) {

			    $imageDir = craft()->svg->directory('imagesDirectory','images','/assets/images');
			    $spritesDir = craft()->svg->directory('spritesDirectory','sprites','/assets/images/sprites');

			    $systemPath = craft()->plugins->getPlugin('svg')->getSettings()->relativeLocaleDirectories ? getcwd() : $_SERVER['DOCUMENT_ROOT'];

			    // Check file extension is svg, otherwise add the '.svg' string.
			    $file = (strlen($file) > 4 && substr($file, -4) == '.svg') ? $file : $file.'.svg';

			    // Checks to see if the file exists
					if (file_exists($file)) {
						$fileUrl = $file;
			    } else if (file_exists($systemPath.$file)) {
						$fileUrl = $systemPath.$file;
			    } else if (file_exists($systemPath.$imageDir.$file)) {
			      // If the svg file exits, use this;
			      $fileUrl = $systemPath.$imageDir.$file;
			    } else if (file_exists($systemPath.$spritesDir.$file)) {
			      $fileUrl = $systemPath.$spritesDir.$file;
			    } else if ( craft()->svg->checkFileExists($file)) { // <- Not nice. Need to find a way to define the content to a variable whilst checking it's valid
					// } else if (false !== ($file = @file_get_contents('...'))) {
			      $fileUrl = $file;
			    } else {
			      $fileUrl = null;
			    }

					if (is_null($fileUrl)) { return false; }

					// TODO: Use the new checkFileExists() function in services/SvgServices/php
					$svgContent = @file_get_contents($fileUrl);

					if ( empty($classes) ) {
						// $svgContent = preg_replace('!^[^>]+>(\r\n|\n)!', '', $svgContent);
						$dom = new \DomDocument();
						$dom->loadXML($svgContent);
						$xml = substr($dom->saveXML(), strpos($dom->saveXML(), '?'.'>') + 2);

						return $xml;
						// return $id !== false ? $svgContent : preg_replace('#\s(id)="[^"]+"#', '', $svgContent);
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

							// Defined XML and remove XML Tag
							$xml = substr($dom->saveXML(), strpos($dom->saveXML(), '?'.'>') + 2);
							// $xml = $dom->saveXML();

					    return $id !== false ? $xml : preg_replace('#\s(id)="[^"]+"#', '', $xml);
					    exit;
						}
					}

			  }

        public function loadsvg($file = 'sprite-symbols', $class = null, $id = true) {
			    return $this->svg($file, $class, $id);
			  }
			}
