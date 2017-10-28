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
 * Description of JsCompileClass
 *
 * @author Bryan Pedroza
 */
class JsCompileTest extends AssetCompilerTest
{
    // Start JS
    public function test_get_single_js_file()
    {
        $actual = $this->AssetCompiler->getScript('test1.js');
        $fileTime = filemtime(__DIR__ . '/testresources/js/test1.js');
        $expected = '<script src="/js/test1.js?v=' . $fileTime . '" ></script>';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_js_file_alternate_folder()
    {
        $this->AssetCompiler->config()->jsPath('alternatejsfoldername');
        $actual = $this->AssetCompiler->getScript('test3.js');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatejsfoldername/test3.js');
        $expected = '<script src="/alternatejsfoldername/test3.js?v=' . $fileTime . '" ></script>';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_single_js_file_alternate_folder_cleans_slashes()
    {
        $this->AssetCompiler->config()->jsPath('/alternatejsfoldername\\');
        $actual = $this->AssetCompiler->getScript('test3.js');
        $fileTime = filemtime(__DIR__ . '/testresources/alternatejsfoldername/test3.js');
        $expected = '<script src="/alternatejsfoldername/test3.js?v=' . $fileTime . '" ></script>';
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
        $expected = '<script src="/js/test1.js?v=' . $fileTime . '" attr1="some value" type="text/javascript" ></script>';
        $this->assertEquals($expected, $actual);
    }

    public function test_get_multi_js_file()
    {
        $compiledName = 'compiledJs.js';
        $filePath = __DIR__ . '/testresources/js/test1.js';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $actual = $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);
        $expected = '<script src="/js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" ></script>';

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
        $expected = '<script src="/js/alternateCompiledFolder/' . $compiledName . '?v=' . $fileTime . '" ></script>';

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
        $expected = '<script src="/alternatejsfoldername/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" ></script>';

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
            $expected .= '<script src="/js/' . $file . '?v=' . $fileTime . '" ></script>' . "\n";
        }

        $this->assertEquals($expected, $actual);
    }
}
