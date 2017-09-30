# Asset Compiler
This package will allow you to compile multiple assets into a single file. Currently supported types are css and javascript.


An additional benefit to this package is that is adds a cache buster. When using this package for single files, this is the only benefit.


Files are compiled and cache busted using the last modified time of the newest file. New files are not compiled every page load, only when 
a file changes in the compilation.

## Configuration

Start by creating a new instance of the asset compiled and give it paths it needs to build your files.
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
| ------------- |:-------------:| -----:|
| rootPath     | The absolute path (set in constructor) | `null` |
| httpPath     | The http path (set in constructor) | `null` |
| cssPath     | Path relative to root path where your css files live | `css` |
| jsPath      | Path relative to root path where your javascript files live     |   `js` |
| compiledFolder | Name of folder that will be created in both js and css folders for compiled files      |    `compiled` |
| debug | When in debug, we won't compile items to make it easier to debug.     |    `false` |
| ignoreMissing | When true, we won't throw exceptions for missing files.      |    `false` |

## Usage

Generate single, cache busted, files.

Single css file
```php
$this->AssetCompiler->getStyle('test1.css', ['attr1' => 'some value', 'type' => 'text/css']);
```
Output
```html
<link href="/css/test1.css?v=123456789" attr1="some value" type="text/css" rel="stylesheet" />
```

Single js file
```php
$this->AssetCompiler->getScript('test1.js', ['attr1' => 'some value', 'type' => 'text/javascript']);
```
Output
```html
<script src="/js/test1.js?v=123456789" attr1="some value" type="text/javascript" />
```

Multiple css files
```php
this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], 'mycompiledcssfile.css', $attributes = []);
```
Output
```html
<link href="/css/compiled/mycompiledcssfile.css?v=123456789" rel="stylesheet" />
```

Multiple js files
```php
this->AssetCompiler->getStylesMulti(['test1.js', 'test2.js'], 'mycompiledjsfile.js', $attributes = []);
```
Output
```html
<script src="/js/compiled/mycompiledjsfile.js?v=123456789" />
```