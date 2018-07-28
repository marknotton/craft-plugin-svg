<img src="http://i.imgur.com/7s9DQMt.png" alt="SVG" align="left" height="60" />

# SVG *for Craft CMS*

> This plugin is no longer maintained. I'm committing to Craft 3 development only. Feel free to use the source code as you like. If you're looking for a Craft 3 version of this plugin, it's likely I've merged parts or all of this plugin into my [Helpers module.](https://github.com/marknotton/craft-module-helpers)

Implement SVG files and symbols quickly, with browser checks and fallback images.

## Table of Contents

- [SVG](#svg)
- [Load SVG](#load-svg)
- [Sprite](#sprite)
- [Installation](#installation)

## Optional tools to increase functionality

- [Browser](https://github.com/marknotton/craft-plugin-browser)
- [Gulp SVG Sprite](https://github.com/jkphl/gulp-svg-sprite)

----
## SVG
Load in SVG content with fallbacks for specific browsers.

| # | Parameter         | Type   | Default                       | Optional | Description
--- | ----------------- | ------ | ----------------------------- | -------- | -----------
| 1 | SVG file name     | string | null                          | No       | Enter the name of the svg file. You can omit the '.svg' extension, as this will be added automatically.
| 2 | Classes           | string | null                          | Yes      | Add class attributes directly to the SVG element tags

You can change the default image and sprite directories in the SVG plugin settings.

#### Example 1:
Outputs SVG content
```
{{ svg('logo') }}
```

#### Example 2:
Outputs SVG content, with the class "blue"
```
{{ svg('logo', 'blue') }}
```

----
## Load SVG
This is essentially just an alias of the above [SVG](#svg) function. Only two parameters can be used though. SVG name and image directory. No fallback will be used, and no browser checks will be made. This simply just outputs the contents of an SVG if it exists.
```
{{ loadsvg('sprite-symbols') }}
```

----
## Sprite

Sprite allows you to reuse SVG elements by taking advantage of 'Symbols' and 'Use' tags with Gulp SVG Sprite.

##Installation

It is assumed you have a basic understanding of NPM and Gulp.

Install 'Gulp SVG Sprite':
```
npm install --save-dev gulp-svg-sprite
```
and 'Replace':
```
npm install --save-dev gulp-notify
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
  .pipe(notify({'title': 'SVG Symbols', 'message': 'SVG Symbols generated Successfully'}));

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
  .pipe(notify({'title': 'SVG View', 'message': 'SVG View Sprite generated Successfully'}));
});
```

## What does this do?

Lets say you have these svg's in your sprites directory:

- facebook.svg
- logo.svg
- phone.svg
- twitter.svg
- youtube.svg

When you run the ```gulp svg``` task, three files will be generated. 'sprite-symbols.svg' and 'sprite-views.svg' will be placed in your images directory. _svg-symbols.scss will be placed in your sass directory. You''ll want to import this in your main global sass/scss file too, along with a variable to your sprites directory (this'll make sense later):

```
$sprite-url : "/assets/images/sprites";
@import "_svg-symbols";
```

#### sprite-symbols.svg
This is a collection of all the SVG's in your sprites directory combined into one single file. Each SVG will now exist as a [symbol](https://developer.mozilla.org/en-US/docs/Web/SVG/Element/symbol). When you use the sprite function, these symbols will be rendered with the [use](https://developer.mozilla.org/en-US/docs/Web/SVG/Element/use) tag.

The gulp task will also add the CSS style 'display:none' to the most parent SVG tag.

#### sprite-views.svg
This is a collection of all the SVG's in your sprites directory combined into one single file too. These are not hidden, but are laid out in a more conventional sprite sheet format. The positions and sizes of each sprite are defined in the '_svg-symbols' file.

The gulp task will add in your css ```$sprite-url``` variable directly into this file.

This 'sprite-view.svg' file will now look something like this:

<img src="http://i.imgur.com/BMrBtyx.jpg" alt="SVG" height="151" />

Now you have an idea of what's actually happening. This is how you use it:

## How to use it

First you need to have your 'sprite-symbol' loaded somewhere in your HTML. Generally I tend to add it just before the ```</body>``` close tag using the [loadsvg](#load-svg) function:

```
{{ loadsvg('sprite-symbols') }}
```

The Twig function called 'sprite' can have up to 2 parameters passed into is.

| # | Parameter   | Type                    | Default  | Optional | Description
--- | ----------- | ----------------------- | -------- | -------- | -----------
| 1 | Sprite name | string                  | null     | No       | Your sprite names are defined by their original file name. So 'Logo.svg' becomes 'logo'.
| 2 | Special     | Boolean, String, Array  | null     | Yes      | You can quickly define either a fallback, size (space delimited string only), or browser option here. If you would like to use multiple options, you can define an associative array instead. See bellow arguments table for more information.

If the second parameter is an associative array, these are to settings you can use:

| # | Arguments | Type              | Description
--- | --------- | ----------------- | -----------
| 1 | fallback  | string or boolean | If Boolean is ```true``` an attempt to find a fallback image if necessary will be made. The first parameter string will be used to match any images in any format. ```false``` and no fallback will be used. If a string is passed, this string will be used, assuming it resides in the given images directory (this can be defined int he SVG plugin settings).
| 2 | directory | string            | By default the sprites location in your general.php Environment Variables will be used by default. However, anything entered in this parameter will override that.
| 3 | size      | string, array     | Because sprites don't necessarily need explicitly defined dimensions, you can pass in a string of one or an array of two numbers; which will add width and height to the sprite. ```true``` is the default and will add the given dimensions defined from the '_sprites-symbol' file, ```false``` no dimensions will be added. ```100``` and the width and height will be set to 100px. ```[null, 100]```, only the height will be added. ```[250, 400]``` width will be 250px and height will be 400px
| 1 | browser   | string            | See [Browser](https://github.com/marknotton/craft-plugin-browser) for documentation. If the browser criteria is a match, a fallback image will be used instead of an SVG... if it exists.
| 3 | class     | string            | Add a class directly onto the sprite.

#### Example 1

This will look for the logo svg symbol. If it can't be found a fallback will be used. If no fallbacks are found, nothing get output
```
{{ sprite('logo') }}
```

#### Example 2

No fallback image will be used if the logo symbol can't be found
```
{{ sprite('logo', false) }}
```

#### Example 3

Will automatically try to find a fallback image for Firefox browsers
```
{{ sprite('logo', 'firefox') }}
```

#### Example 4

Will output at 100px in width and height
```
{{ sprite('logo', '100') }}
```

#### Example 5

Will output at 20px in width and 50% height
```
{{ sprite('logo', '20px 50%') }}
```

#### Example 6
```
{{
 sprite('logo',
   {
     fallback : 'logo.png',
     browsers : 'ie 9',
     size : true
     class : 'test'
   }
 )
}}
```
#### Example 6-A Output

If the browser criteria is ok, and the '_spite-symbol' was imported correctly; this will be output:

```
<svg class="logo test svg-logo-size">
	<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use>
</svg>
```

#### Example 6-B Output

If the browser criteria is not ok, a fallback image will be used instead (if one can be found)

```
<img class="logo test svg-logo-size" src="/assets/images/logo.png" alt="logo">
```

#### Example 7

To use the same data in Sass, use a Mixin like this:

```
@mixin sprite($class) {
  @extend .svg-#{$class}-size, .svg-#{$class};
}
```
and pass in the sprite name:

```
@include sprite('logo');
```
