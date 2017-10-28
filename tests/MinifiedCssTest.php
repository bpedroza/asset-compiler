<?php

/*
 * The MIT License
 *
 * Copyright 2017 Bryan.
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

/**
 * Description of MinifiedCssTest
 *
 * @author Bryan
 */
class MinifiedCssTest extends AssetCompilerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->AssetCompiler->config()->minify(true);
    }
    
    public function test_get_single_minified_css_file()
    {
        $actual = $this->AssetCompiler->getStyle('test1.css');
        $fileTime = filemtime(__DIR__ . '/testresources/css/test1.css');
        $expected = '<link href="/css/compiled/test1.min.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_single_minified_css_file_contents()
    {
        $fileName = 'test1.css';
        $minifiedName = 'test1.min.css';
        $minifiedPath = $this->rootPath . 'css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $minifiedName;
        $this->AssetCompiler->getStyle($fileName);

        $this->assertFileExists($minifiedPath);

        $actual = file_get_contents($minifiedPath);
        $expected = 'body{background:#fff}';

        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_single_minified_css_file_contents_not_minified_in_debug_mode()
    {
        $this->AssetCompiler->config()->debug(true);
        
        $actual = $this->AssetCompiler->getStyle('test1.css');
        $fileTime = filemtime(__DIR__ . '/testresources/css/test1.css');
        $expected = '<link href="/css/test1.css?v=' . $fileTime . '" rel="stylesheet" />';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_multi_minified_css_file()
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
    
    public function test_get_multi_minified_css_file_contents()
    {
        $compiledName = 'compiledCss.css';
        $filePath = $this->rootPath . 'css/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName;
        $this->AssetCompiler->getStylesMulti(['test1.css', 'test2.css'], $compiledName);

        $this->assertFileExists($filePath);

        $actual = file_get_contents($filePath);
        $expected = 'body{background:#fff}div.full{width:100%}';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_multi_minified_css_file_contents_not_minified_in_debug_mode()
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
