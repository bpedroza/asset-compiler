# Asset Compiler
This package will allow you to compile multiple assets into a single file and minify single or compiled assets. Currently supported types are css and javascript.


An additional benefit to this package is that is adds a cache buster. 


Files are compiled, minified, and cache busted using the last modified time of the newest file. New files are not compiled every page load, only when 
a file changes in the compilation.
## Installation
Add `"bpedroza/asset-compiler": "1.1.*"` to your composer.json require array

## Configuration

Start by creating a new instance of the `AssetCcompiler` and give it paths it needs to build your files.
The constructor takes arguments for the absolute path to the assets and an http path. The http path can be relative or include the full web address.
```php
$AssetCompiler = new \Bpedroza\AssetCompiler\AssetCompiler('/path/to/my/assets/', '/assets/');
```
OR
```php
$AssetCompiler = new \Bpedroza\AssetCompiler\AssetCompiler('/path/to/my/assets/', 'http://www.example.com');
```

Next you can configure the paths to specific assets, debug, and exception behavior. 

By default the css folder is assumed to be /css and the javascript folder is assumed to be /js

All configuration options are getters and setters and can be chained when setting. When the parameter is missing, it will return the current configuration value.

```php
// As Setters
$AssetCompiler->config()
        ->cssPath('mycssfolder')
        ->jsPath('myjsfolder');

// As getter
$compiledFolderName = $AssetCompiler->config()->compiledFolder();
```

### Available configuration options

| Method        | Description   | Default  |
| ------------- |-------------| -----:|
| rootPath     | The absolute path (set in constructor) | `null` |
| httpPath     | The http path (set in constructor) | `null` |
| cssPath     | Path relative to root path where your css files live | `'css'` |
| jsPath      | Path relative to root path where your javascript files live     |   `'js'` |
| compiledFolder | Name of folder that will be created in both js and css folders for compiled files      |    `'compiled'` |
| debug | When in debug, we won't compile or minify items to make it easier to debug.     |    `false` |
| ignoreMissing | When true, we won't throw exceptions for missing files.      |    `false` |
| minify | When true, we will minify file contents.      |    `true` |

## Usage

Generate single, cache busted, files.


```php
// Single css file
$AssetCompiler->getStyle('test1.css', ['attr1' => 'some value', 'type' => 'text/css']);

// Single js file
$AssetCompiler->getScript('test1.js', ['attr1' => 'some value', 'type' => 'text/javascript']);

// Compiled css file
$AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], 'mycompiledcssfile.css', $attributes = []);

// Compiled javascript file
$AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], 'mycompiledjsfile.js', $attributes = []);

```

Output
```html
<link href="/css/test1.css?v=123456789" attr1="some value" type="text/css" rel="stylesheet" />

<script src="/js/test1.js?v=123456789" attr1="some value" type="text/javascript" ></script>

<link href="/css/compiled/mycompiledcssfile.css?v=123456789" rel="stylesheet" />

<script src="/js/compiled/mycompiledjsfile.js?v=123456789" ></script>
```

Minified Output
```html
<link href="/css/compiled/test1.min.css?v=123456789" attr1="some value" type="text/css" rel="stylesheet" />

<script src="/js/compiled/test1.min.js?v=123456789" attr1="some value" type="text/javascript" ></script>

<link href="/css/compiled/mycompiledcssfile.css?v=123456789" rel="stylesheet" />

<script src="/js/compiled/mycompiledjsfile.js?v=123456789" ></script>
```
