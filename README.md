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

----
## SVG
Load in SVG content with fallbacks for specific browsers.

| # | Parameter         | Type   | Default                       | Optional | Description
--- | ----------------- | ------ | ----------------------------- | -------- | -----------
| 1 | SVG file name     | string | null                          | No       | Enter the name of the svg file. You can omit the '.svg' extension, as this will be added automatically.
| 2 | Fallback image    | string | Same as first parameter       | Yes      | If a fallback image is not passed the svg filename will used. The image directory will be searched, as will the sprites directory, and also various formats will be checked. First match is returned. If a fallback is used, this will be returned in the usual ```<img>``` tag. However, if the fallback is missing, the svg will be used as the source of the ```<img>``` tag instead. You can also use the string 'disable', if you want absolutely no fallbacks.
| 3 | Browsers to check | string | false                         | Yes      | See [Browser](https://github.com/marknotton/craft-plugin-browser) for documentation. If the browser criteria is a match, a fallback image will be used instead of an SVG... if it exists.
| 4 | Image directory   | string | environmentVariables > images | Yes      | By default the image location in your general.php Environment Variables will be used by default. However, anything entered in this parameter will overwrite that.

You can change the default image and sprite directories in the SVG plugin settings.

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
## Sprite

Sprite allows you to reuse SVG elements by taking advantage of Symbols and Use tags with Gulp SVG Sprite.

##Installation

It is assumed you have a basic understanding of NPM and Gulp.

Install 'Replace':
```
npm install --save-dev gulp-notify
```
and 'Gulp SVG Sprite':
```
npm install --save-dev gulp-svg-sprite
```

Add the following to your gulpfile.js and make any directory amends where necessary. As it stands, this assumes you keep your Sass directory outside of your working project directory.

```js

var replace	  = require("gulp-replace");
var svgSprite = require('gulp-svg-sprite');

gulp.task('svg', function(){
	// SYMBOLS
  gulp.src('/assets/images/sprites/**/*.svg')
  .pipe(svgSprite({
    mode: {
      symbol: {
        sprite: 'sprite-symbols.svg',
        dest: '',
        example: false,
      }
    }
  }))
	.pipe(replace("<svg","<svg style='display:none !important;'"))
  .pipe(gulp.dest('/assets/images'))
  .pipe(notify({"icon": icon_yes, 'title': 'SVG Symbols', 'message': 'SVG Symbols generated Successfully'}));

	// VIEWS SPRITE
	gulp.src('/assets/images/sprites/**/*.svg')
  .pipe(svgSprite({
    mode: {
      view : {
        bust : false,
        render : {
          scss : {
            dest:'../../../sass/_svg-symbols.scss'
          }
        },
        sprite: 'sprite-views.svg',
        prefix: '.svg-%s',
        dest: '',
        dimensions : '-size',
        example: false,
      },
    }
  }))
	.pipe(replace('url("sprite-views.svg")','url($sprite-url + "sprite-views.svg")'))
  .pipe(gulp.dest('/assets/images'))
  .pipe(notify({"icon": icon_yes, 'title': 'SVG View', 'message': 'SVG View Sprite generated Successfully'}));
});
```

## What does this do?

Lets say you have these svg's in your sprites directory:

- facebook.svg
- logo.svg
- phone.svg
- twitter.svg
- youtube.svg

When you run the ```gulp svg``` task, three files will be generated. 'sprite-symbols.svg' and 'sprite-views.svg' will be placed in your images directory. ```_svg-symbols.scss``` will be placed in your sass directory. You''ll want to include this in your main global sass/scss file too, along with a variable to your sprites directory (this'll make sense later):

```
$sprite-url = "/assets/images/sprites";
@import "_svg-symbols";
```

#### sprite-symbols.svg
This is a collection of all the SVG's in your sprites directory combined into one single file. Each SVG will now exist as a [symbol](https://developer.mozilla.org/en-US/docs/Web/SVG/Element/symbol). When you use the symbol function, these symbols will be rendered with the [use](https://developer.mozilla.org/en-US/docs/Web/SVG/Element/use) tag.

The gulp task will also add a small CSS style to display none this SVG.

#### sprite-views.svg
This is a collection of all the SVG's in your sprites directory combined into one single file too. These are not hidden, but are laid out in a more conventional sprite sheet format. The positions and sizes of each sprite are defined in the ```_svg-symbols``` file.

The gulp task will add in your css ```$sprite-url``` variable directly into this file.

This 'sprite-view.svg' file will look something like this:

<img src="http://i.imgur.com/BMrBtyx.jpg" alt="SVG" align="left" height="151" />

Now you have an idea of what's actually happening. This is how you use it:

## Usage

Read more on how to use these

| # | Parameter   | Type                           | Default  | Optional | Description
--- | ----------- | ------------------------------ | -------- | -------- | -----------
| 1 | Sprite name | string                         | null     | No       | Your sprite names are defined by their original file name. So 'Logo.svg' becomes 'logo'.
| 2 | Special     | Boolean, String, Array, Object | null     | Yes      | You can quickly define either a fallback, size, or browser option here. If you would like to use multiple options, you can define an associative array instead. See bellow arguments table for more information.

| # | Arguments | Type              | Description
--- | --------- | ----------------- | -----------
| 1 | fallback  | string or boolean | If Boolean is ```true``` an attempt to find a fallback image if necessary will be made. The first parameter string will be used, and any matching images in any format will will be used. ```false``` and no fallback will be used. If a string is used, this string will be used, assuming it resides in the given images directory (this can be defined int he SVG plugin settings).
| 2 | directory | string            | environmentVariables > images + '/sprites' | Yes | By default the sprites location in your general.php Environment Variables will be used by default. However, anything entered in this parameter will overwrite that.
| 3 | size      | string            | Because sprites don't necessarily have predefined dimensions, you can pass in a string of one or two numbers too add width and height to the sprite. Examples: ```true```, ```false```, ```100 null```, ```null 100```, ```250 400```, ```[100, 200]```
| 1 | browser   | string            | See [Browser](https://github.com/marknotton/craft-plugin-browser) for documentation. If the browser criteria is a match, a fallback image will be used instead of an SVG... if it exists.
| 3 | class     | string            | Add a class directory onto the sprite.

#### Example 1

```
{{
 symbol('logo',
   {
     fallback : 'logo.png',
     directory : '/assets/images/svgs',
     browsers : 'ie 9',
     size : true
     class : 'test'
   }
 )
}}
```


{{ symbol('logo') }}             // Will simply use the logo symbol. By default the size class will be added automatically
{{ symbol('logo', true) }}       // Will automatically try to find a fallback image based of the first paramater string. And will fallback to d for ie 9 and 10
{{ symbol('logo', 'firefox') }}  // Will automatically try to find a fallback image for firefox browsers
