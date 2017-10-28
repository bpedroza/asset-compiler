<?php

use PHPUnit\Framework\TestCase;
use Bpedroza\AssetCompiler\AssetCompiler;
use Bpedroza\AssetCompiler\Exceptions\DirectoryDoesNotExistException;

class AssetCompilerTest extends TestCase
{
    /**
     * Asset compiler instance
     * @var \Bpedroza\AssetCompiler\AssetCompiler 
     */
    protected $AssetCompiler;
    
    /**
     * Full path to our test root
     * @var string 
     */
    protected $rootPath;

    public function setUp()
    {
        parent::setUp();
        $this->rootPath = __DIR__ . '/testresources/';
        $this->AssetCompiler = new AssetCompiler($this->rootPath);
        $this->AssetCompiler->config()->minify(false);
        $this->removeFolders();
    }

    public function tearDown()
    {
        $this->removeFolders();
        parent::tearDown();
    }
    
    public function test_root_path_exception_when_directory_missing()
    {
        $this->expectException(DirectoryDoesNotExistException::class);
        $this->AssetCompiler->config()->rootPath($this->rootPath . '/afakefolder');
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
