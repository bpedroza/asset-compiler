<?php

use PHPUnit\Framework\TestCase;
use Bpedroza\AssetCompiler\AssetCompiler;
use Bpedroza\AssetCompiler\Exceptions\ResourceMissingException;

class AssetCompilerTest extends TestCase
{

    private $AssetCompiler;
    private $rootPath;

    public function setUp()
    {
        parent::setUp();
        $this->rootPath = __DIR__ . '/testresources/';
        $this->AssetCompiler = new AssetCompiler($this->rootPath);
        $this->removeFolders();
    }

    public function tearDown()
    {
        $this->removeFolders();
        parent::tearDown();
    }

    public function test_get_single_css_file()
    {
        $actual = $this->AssetCompiler->getStyle('test1.css');
        $fileTime = filemtime(__DIR__ . '/testresources/css/test1.css');
        $expected = '<link href="/css/test1.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_css_file_alternate_folder()
    {
        $this->AssetCompiler->config()->cssPath('alternatecssfoldername');
        $actual = $this->AssetCompiler->getStyle('test3.css');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatecssfoldername/test3.css');
        $expected = '<link href="/alternatecssfoldername/test3.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_css_file_alternate_folder_cleans_slashes()
    {
        $this->AssetCompiler->config()->cssPath('/alternatecssfoldername\\');
        $actual = $this->AssetCompiler->getStyle('test3.css');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatecssfoldername/test3.css');
        $expected = '<link href="/alternatecssfoldername/test3.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_missing_css()
    {
        $this->expectException(ResourceMissingException::class);
        $actual = $this->AssetCompiler->getStyle('test123asd.css');
    }

    public function test_get_single_css_file_with_attributes()
    {
        $actual = $this->AssetCompiler->getStyle('test1.css', ['attr1' => 'some value', 'type' => 'text/css']);
        $fileTime = filemtime(__DIR__ . '/testresources/css/test1.css');
        $expected = '<link href="/css/test1.css?v=' . $fileTime . '" attr1="some value" type="text/css" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_css_file()
    {
        $compiledName = 'compiledCss.css';
        $filePath = __DIR__ . '/testresources/css/test1.css';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $actual = $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);
        $expected = '<link href="/css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" rel="stylesheet" />';

        $this->assertFileExists($this->rootPath . 'css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_css_file_alternate_compiled_path()
    {
        $compiledName = 'compiledCss.css';
        $filePath = __DIR__ . '/testresources/css/test1.css';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $this->AssetCompiler->config()->compiledFolder('alternateCompiledFolder');
        $actual = $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);
        $expected = '<link href="/css/alternateCompiledFolder/' . $compiledName . '?v=' . $fileTime . '" rel="stylesheet" />';

        $this->assertFileExists($this->rootPath . 'css/alternateCompiledFolder/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_css_file_alternate_css_path()
    {
        $compiledName = 'compiledCss.css';
        $filePath = __DIR__ . '/testresources/alternatecssfoldername/test3.css';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $this->AssetCompiler->config()->cssPath('alternatecssfoldername');
        $actual = $this->AssetCompiler->getStylesMulti(['test3.css', 'test4.css'], $compiledName);
        $expected = '<link href="/alternatecssfoldername/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" rel="stylesheet" />';

        $this->assertFileExists($this->rootPath . 'alternatecssfoldername/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_css_file_contents()
    {
        $compiledName = 'compiledCss.css';
        $filePath = $this->rootPath . 'css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName;
        $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);

        $this->assertFileExists($filePath);

        $actual = file_get_contents($filePath);
        $expected = ' body {background:#fff;} div.full {width:100%;}';

        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_css_file_missing_item()
    {
        $this->expectException(ResourceMissingException::class);
        $compiledName = 'compiledCss.css';
        $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css', 'asdf.css'], $compiledName);
    }

    public function test_get_multi_css_file_debug_on()
    {
        $compiledName = 'compiledCss.css';
        $files = ['test1.css', 'test2.css'];
        $this->AssetCompiler->config()->debug(true);
        $actual = $this->AssetCompiler->getStylesMulti($files, $compiledName);

        $expected = '';
        foreach ($files as $file) {
            $fileTime = filemtime(__DIR__ . '/testresources/css/' . $file);
            $expected .= '<link href="/css/' . $file . '?v=' . $fileTime . '" rel="stylesheet" />' . "\n";
        }

        $this->assertEquals($expected, $actual);
    }

    // Start JS
    public function test_get_single_js_file()
    {
        $actual = $this->AssetCompiler->getScript('test1.js');
        $fileTime = filemtime(__DIR__ . '/testresources/js/test1.js');
        $expected = '<script src="/js/test1.js?v=' . $fileTime . '" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_js_file_alternate_folder()
    {
        $this->AssetCompiler->config()->jsPath('alternatejsfoldername');
        $actual = $this->AssetCompiler->getScript('test3.js');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatejsfoldername/test3.js');
        $expected = '<script src="/alternatejsfoldername/test3.js?v=' . $fileTime . '" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_js_file_alternate_folder_cleans_slashes()
    {
        $this->AssetCompiler->config()->jsPath('/alternatejsfoldername\\');
        $actual = $this->AssetCompiler->getScript('test3.js');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatejsfoldername/test3.js');
        $expected = '<script src="/alternatejsfoldername/test3.js?v=' . $fileTime . '" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_missing_js()
    {
        $this->expectException(ResourceMissingException::class);
        $actual = $this->AssetCompiler->getScript('test123asd.js');
    }

    public function test_get_single_js_file_with_attributes()
    {
        $actual = $this->AssetCompiler->getScript('test1.js', ['attr1' => 'some value', 'type' => 'text/javascript']);
        $fileTime = filemtime(__DIR__ . '/testresources/js/test1.js');
        $expected = '<script src="/js/test1.js?v=' . $fileTime . '" attr1="some value" type="text/javascript" />';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file()
    {
        $compiledName = 'compiledJs.js';
        $filePath = __DIR__ . '/testresources/js/test1.js';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $actual = $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);
        $expected = '<script src="/js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" />';

        $this->assertFileExists($this->rootPath . 'js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file_alternate_compiled_path()
    {
        $compiledName = 'compiledJs.js';
        $filePath = __DIR__ . '/testresources/js/test1.js';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $this->AssetCompiler->config()->compiledFolder('alternateCompiledFolder');
        $actual = $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);
        $expected = '<script src="/js/alternateCompiledFolder/' . $compiledName . '?v=' . $fileTime . '" />';

        $this->assertFileExists($this->rootPath . 'js/alternateCompiledFolder/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file_alternate_js_path()
    {
        $compiledName = 'compiledJs.js';
        $filePath = __DIR__ . '/testresources/alternatejsfoldername/test3.js';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $this->AssetCompiler->config()->jsPath('alternatejsfoldername');
        $actual = $this->AssetCompiler->getScriptsMulti(['test3.js', 'test4.js'], $compiledName);
        $expected = '<script src="/alternatejsfoldername/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" />';

        $this->assertFileExists($this->rootPath . 'alternatejsfoldername/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file_contents()
    {
        $compiledName = 'compiledJs.js';
        $filePath = $this->rootPath . 'js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName;
        $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);

        $this->assertFileExists($filePath);

        $actual = file_get_contents($filePath);
        $expected = "\n" . ';' . 'var message = "Hello World";' . "\n" . ';' . 'function doSomething() { return 2+2; }';

        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file_missing_item()
    {
        $this->expectException(ResourceMissingException::class);
        $compiledName = 'compiledJs.js';
        $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js', 'asdf.js'], $compiledName);
    }

    public function test_get_multi_js_file_debug_on()
    {
        $compiledName = 'compiledJs.js';
        $files = ['test1.js', 'test2.js'];
        $this->AssetCompiler->config()->debug(true);
        $actual = $this->AssetCompiler->getScriptsMulti($files, $compiledName);

        $expected = '';
        foreach ($files as $file) {
            $fileTime = filemtime(__DIR__ . '/testresources/js/' . $file);
            $expected .= '<script src="/js/' . $file . '?v=' . $fileTime . '" />' . "\n";
        }

        $this->assertEquals($expected, $actual);
    }

    // Remove folders created by the test
    private function removeFolders()
    {
        $directories = [
            'css/compiled',
            'js/compiled',
            'alternatecssfoldername/compiled',
            'alternatejsfoldername/compiled',
            'css/alternatecompiledfolder',
            'js/alternatecompiledfolder',
            'alternatecssfoldername/alternatecompiledfolder',
            'alternatejsfoldername/alternatecompiledfolder',
        ];

        foreach ($directories as $directory) {
            $dir = $this->rootPath . $directory;
            if (!is_dir($dir)) {
                continue;
            }
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                unlink("$dir/$file");
            }
            rmdir($dir);
        }
    }

}
