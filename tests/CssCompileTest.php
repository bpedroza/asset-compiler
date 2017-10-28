<?php

/*
 * The MIT License
 *
 * Copyright 2017 Bryan Pedroza.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
use Bpedroza\AssetCompiler\Exceptions\ResourceMissingException;

/**
 * Description of CssCompileTest
 *
 * @author Bryan Pedroza
 */
class CssCompileTest extends AssetCompilerTest
{
    public function test_get_single_css_file()
    {
        $actual = $this->AssetCompiler->getStyle('test1.css');
        $fileTime = filemtime(__DIR__ . '/testresources/css/test1.css');
        $expected = '<link href="/css/test1.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_single_css_file_debug()
    {
        $this->AssetCompiler->config()->debug(true);
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
    
    public function test_get_missing_css_ignore_missing()
    {
        $this->AssetCompiler->config()->ignoreMissing(true);
        $actual = $this->AssetCompiler->getStyle('test123asd.css');
        $expected = '<link href="/css/test123asd.css?v=0" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
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
    
    public function test_get_multi_css_file_does_not_get_recreated()
    {
        $compiledName = 'compiledCss.css';
        $filePath = __DIR__ . '/testresources/css/test1.css';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $actual = $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);
        $expected = '<link href="/css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" rel="stylesheet" />';

        $this->assertFileExists($this->rootPath . 'css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
        // Sleep a second to make sure time is different
        sleep(1);
        $actual = $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);
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
}
