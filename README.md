<img src="http://i.imgur.com/7s9DQMt.png" alt="SVG" align="left" height="60" />

# SVG *for Craft CMS*

Implement SVG files and symbols quickly, with browser checks and fallback images.

##Table of Contents

- [Installation](#installation)
- [SVG](#svg)
- [Load SVG](#load-svg)
- [Symbol](#symbol)

##Optional tools to increase functionality

- [Browser](https://github.com/marknotton/craft-plugin-browser)
- [Gulp SVG Sprite](https://github.com/jkphl/gulp-svg-sprite)

##Installation

If you choose to use Gulp SVG Sprite (which I highly recommend), here is an installation guide to get you started:

> Note: It is assumed you have a basic understanding of NPM and Gulp.

Install 'Replace':
```
npm install --save-dev gulp-notify
```
and 'Gulp SVG Sprite':
```
npm install --save-dev gulp-svg-sprite
```

Add this to your gulpfile.js:
```js

var replace	  	= require("gulp-replace");
var svgSprite   = require('gulp-svg-sprite');

gulp.task('svg', function(){
	gulp.src('/assets/images/sprites/**/*.svg')
	.pipe(svgSprite({
		mode: {
			symbol: {
				sprite: 'sprite-symbols.svg',
				dest: '',
				example: false,
			},
			view : {
				bust : false,
				render : {
					scss : {
						dest:'../../../sass/base/_svg-symbols.scss' // amend where necessary
					},
				},
				sprite: 'sprite-views.svg',
				prefix: '.svg-%s',
				dest: '',
				dimensions : '-size',
				example: false,
			}
		}
	}))
	.pipe(replace("<svg","<svg style='display:none !important;'"))
	.pipe(replace('url("sprite-views.svg")','url($sprite-url + "sprite-views.svg")'))
	.pipe(gulp.dest('/assets/images'))
});
```

----
## SVG
Load in SVG content with fallbacks for specific browsers.

| # | Parameter         | Type   | Default                       | Optional | Description
--- | ----------------- | ------ | ----------------------------- | -------- | -----------
| 1 | SVG file name     | string | null                          | No       | Enter the name of the svg file. You can omit the '.svg' extension, as this will be added automatically.
| 2 | Fallback image    | string | Same as first parameter       | Yes      | If a fallback image is not passed the svg filename will used. The image directory will be searched, and various formats will be checked. First match is returned. If a fallback is used, this will be returned in the usual ```<img>``` tag. However, if the fallback is missing, the svg will be used as the source of the ```<img>``` tag instead. You can also use the string 'disable', if you want absolutely no fallbacks.
| 3 | Browsers to check | string | false                         | Yes      | See [Browser](https://github.com/marknotton/craft-plugin-browser) for documentation
| 4 | Image directory   | string | environmentVariables > images | Yes      | By default the image location in your general.php Environment Variables will be used by default. However, anything entered in this parameter will overwrite that.

####Example 1:
Outputs SVG content
```
{{ svg('logo') }}
```

####Example 2:
Outputs SVG content, the fallback image is used for IE9. Otherwise all other browsers will display the SVG as normal.
```
{{ svg('logo', 'logo-dark.png', 'ie 9') }}
```

####Example 3:
Outputs SVG content, except for browsers that are IE11 and below where the fallback image is used.
```
{{ svg('logo', 'logo-dark.png', 'ie < 11') }}
```

####Example 4:
Outputs SVG content, if the svg isn't found... falls back to any image that is named 'logo' with the extension '.png', '.jpg', or '.gif'. First match is returned. This only applies to Firefox browsers in this example.
```
{{ svg('logo.svg', false, 'firefox') }}
```

####Example 5:
Outputs SVG content, falls back to a standard <img> element using the .svg file. Unless on Edge.
```
{{ svg('logo', false, 'edge') }}
```

####Example 6:
Outputs SVG content, when 'disable' is passed no fallback will be generated at all.
```
{{ svg('logo', 'disable', 'chrome') }}
```

----
## Load SVG
This is essentially just an alias of the above [SVG](#svg) function. Only two parameters can be used though. SVG name and image directory. No fallback will be used, and no browser checks will be made. This simply just outputs the contents of an SVG if it exists.
```
{{ loadsvg('sprite-symbols') }}
```

----
## Symbol

Coming Soon
