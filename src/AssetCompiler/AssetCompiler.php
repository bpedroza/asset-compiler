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
namespace Bpedroza\AssetCompiler;

use Bpedroza\AssetCompiler\Exceptions\ResourceMissingException;
/**
 * Use this tool to build js and css files compiled as one to avoid having too many assets to load
 * this tool will also put a cache buster string on all compiled assets
 *
 * @author Bryan
 */
class AssetCompiler
{

    private $cssPath = 'css';
    private $jsPath = 'js';
    private $rootPath, $httpRootPath;
    private $compiledFolder = 'compiled';
    private $debug = false;
    
    public function __construct($rootPath, $httpRootPath = null)
    {
        $this->rootPath = rtrim($rootPath,'\/');
        if(!is_dir($rootPath)) {
            throw new \Exception('Specified root directory does not exist.');
        }
        
        $this->httpRootPath = $httpRootPath === null ? '' : rtrim($httpRootPath, '\/');
    }

    /**
     * Path relative to root path where your css files live
     * @param string $path - the path
     * @return mixed - $this or the current cssPath
     */
    public function cssPath($path = null)
    {
        return $this->configValue('cssPath', trim($path,'\/'));
    }

    /**
     * Path relative to root path where your js files live
     * @param string $path - the path
     * @return mixed - $this or the current jsPath
     */
    public function jsPath($path = null)
    {
        return $this->configValue('jsPath', trim($path,'\/'));
    }

    /**
     * Name of folder that will be created in both js and css folders for compiled files
     * @param string $folder - the folder name
     * @return mixed - $this or the current compiled folder name
     */
    public function compiledFolder($folder = null)
    {
        return $this->configValue('compiledFolder', $folder);
    }
    
    /**
     * Set or get config value for debug. When in debug, we won't compile items.
     * @param bool $debug
     * @return bool
     */
    public function debug($debug = null)
    {
       return $this->configValue('debug', $debug); 
    }

    /**
     * Take an array of js file paths relative to the public/js folder
     * and combine them into a single file named with the last modified time of the 
     * most recent modified file to avoid caching.
     * @param array $files - an array of the file names we need to have
     * @param string $outFile - the name of the output file - eg. app-compiled.js
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScriptsMulti(array $files, string $outFile, $attrs = [])
    {
        $output = '';
        $outFilePath = $this->getCompiledPath() . $outFile;
        try {
            $outFileModTime = $this->filemtimeOrException($outFilePath);
        } catch (ResourceMissingException $e) {
            $outFileModTime = 0;
        }
        
        list($paths, $lastModTime) = $this->getLastModTimeAndPathsOfFiles($files);

        if (( $outFileModTime === 0 || $lastModTime > $outFileModTime ) && count($paths)) {
            $this->generateOutFile($outFilePath, $paths, "\n" . ';');
        }
        if ($this->debug) {
            foreach ($paths as $path) {
                $relPath = str_replace($this->rootPath . '/js/', '', $path);
                $output .= $this->getScript($relPath) . "\n";
            }
        } else {

            $output = '<script src="' . $this->getCompiledPath('js', true) . $outFile . '?v=' . $lastModTime . '" ' . $this->generateAttributesString($attrs) . '/>';
        }
        return $output;
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScript($file, $attrs = [])
    {
        $outFileModTime = $this->filemtimeOrException($this->rootPath . '/' . $this->jsPath . '/' . $file);
        $httpPath = $this->httpRootPath . '/' . $this->jsPath . '/' . $file;
        return '<script src="' . $httpPath . '?v=' . $outFileModTime . '"' . $this->generateAttributesString($attrs) . ' />';
    }

    /**
     * Function to take an array of css file paths relative to the public/css folder
     * and combine them into a single file named with the last modified time of the 
     * most recent modified file to avoid caching
     * @param array $files - an array of the file names we need to have
     * @param string $outFile - the name of the output file - eg. app-compiles.js
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStylesMulti(array $files, string $outFile, $attrs = [])
    {
        $output = '';
        $outFilePath = $this->getCompiledPath('css') . $outFile;
        try {
            $outFileModTime = $this->filemtimeOrException($outFilePath);
        } catch (ResourceMissingException $e) {
            $outFileModTime = 0;
        }
        list($paths, $lastModTime) = $this->getLastModTimeAndPathsOfFiles($files, 'css');

        if ($this->debug) {
            foreach ($paths as $path) {
                $relPath = str_replace($this->rootPath . '/css/', '', $path);
                $output .= $this->getStyle($relPath, $attrs) . "\n";
            }
        } else {
            if (( $outFileModTime === 0 || $lastModTime > $outFileModTime ) && count($paths)) {
                $this->generateOutFile($outFilePath, $paths, "\n" . ';');
            }
            $output = '<link href="' . $this->getCompiledPath('css', true) . $outFile . '?v=' . $lastModTime . '" ' . $this->generateAttributesString($attrs) . 'rel="stylesheet" />';
        }
        return $output;
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStyle($file, $attrs = [])
    {
        $outFileModTime = $this->filemtimeOrException($this->rootPath . '/' . $this->cssPath . '/' . $file);
        $httpPath = $this->httpRootPath . '/' . $this->cssPath . '/' . $file;
        return '<link href="' . $httpPath . '?v=' . $outFileModTime . '"' . $this->generateAttributesString($attrs) . ' rel="stylesheet" />';
    }

    /**
     * Method to generate attribute string from an array
     * @param array $attrs - an array of attributes where key is the attribute name and value is the value
     * @return string
     */
    private function generateAttributesString($attrs)
    {
        if (empty($attrs)) {
            return '';
        }
        $attrString = ' ';
        foreach ($attrs as $key => $val) {
            $attrString .= $key . '="' . $val . '" ';
        }

        return rtrim($attrString);
    }

    /**
     * Method to get the file paths and last modified time of a set of files
     * @param array $files - an array of the relative file paths
     * @param string $type - the type (and path) (js | css)
     * @return array - an array with the paths array and the last modified time
     */
    private function getLastModTimeAndPathsOfFiles($files, $type = 'js')
    {
        $lastModTime = 0;
        $paths = [];
        foreach ($files as $k => $file) {
            $paths[$k] = $this->rootPath . '/' . $this->configValue($type . 'Path') . '/' . $file;
            $mTime = $this->filemtimeOrException($paths[$k]);
            $lastModTime = $mTime > $lastModTime ? $mTime : $lastModTime;
            if ($mTime === 0) {
                unset($paths[$k]);
            }
        }

        return [$paths, $lastModTime];
    }

    /**
     * Method to generate the output file
     * @param string $outFilePath - the full path to the output file
     * @param array $paths - an array of full paths to files to compile
     * @param string $separator - optional string to separate files with
     */
    private function generateOutFile($outFilePath, $paths, $separator)
    {
        file_put_contents($outFilePath, '');
        foreach ($paths as $path) {
            file_put_contents($outFilePath, $separator . file_get_contents($path), FILE_APPEND);
        }
    }

    /**
     * Method to return the compiled path - if the full path is requested, we will try to make folders.
     * @param string $type - (js | css)
     * @param bool $relative - get the relative or absolute path
     * @return the full compiled output path
     */
    private function getCompiledPath($type = 'js', $relative = false)
    {
        $compiledPath = ($relative ? '' : $this->rootPath ) . '/' . $this->configValue($type . 'Path') . '/';
        if (!strlen($this->compiledFolder)) {
            return $compiledPath;
        }
        // Make sure there's no leading or trailing slashes.
        $compiledFolder = trim($this->compiledFolder, '\/');
        // Get all directories, account for accidental double slashes.
        $folders = array_filter(explode('/', str_replace('\\', '/', $compiledFolder)));
        // Loop though and create directories if we need to.
        foreach ($folders as $folder) {
            $compiledPath .= $folder . '/';
            if (!$relative && !is_dir($compiledPath)) {
                mkdir($compiledPath);
            }
        }

        return $compiledPath;
    }

    /**
     * function to return the modified time of a file or 0 on failure
     * @param type $path
     * @return int
     */
    private function filemtimeOrException($path)
    {
        if (file_exists($path)) {
            return filemtime($path);
        }
        throw new ResourceMissingException($path);
    }

    /**
     * Set/Get configuration values 
     * @param string $variable - the name of the instance variable
     * @param mixed $value - the new value
     * @return mixed - the current value or $this
     */
    private function configValue($variable, $value = null)
    {
        if ($value === null) {
            return $this->{$variable};
        }

        $this->{$variable} = $value;
        return $this;
    }

}
