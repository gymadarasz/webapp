<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

use GyMadarasz\WebApp\Service\Config;
use RuntimeException;
use stdClass;
use function filemtime;
use function htmlentities;
use function is_array;
use function is_dir;
use function is_object;
use function mkdir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

class Template
{
    private string $filename;

    private bool $setAsItIs = false;

    /**
     * @var array<mixed> $data
     */
    private array $data = [];

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     *
     * @param  string       $filename
     * @param  array<mixed> $data
     * @return Template
     * @throws RuntimeException
     */
    public function create(string $filename, array $data = []): Template
    {
        $cacheFile = $this->getCacheFile($filename);
        
        $template = new Template($this->config);
        $template->filename = $cacheFile;
        if (!file_exists($template->filename)) {
            throw new RuntimeException('Template cache file "' . $cacheFile . '".');
        }
        $template->data = $data;
        
        return $template;
    }
    
    /**
     *
     * @param  string $filename
     * @return string
     * @throws RuntimeException
     */
    private function getFullname(string $filename): string
    {
        $fullFilename = $this->config->get('templatesPathExt') . '/' . $filename;
        
        if (!file_exists($fullFilename)) {
            $fullFilename = $this->config->get('templatesPath') . '/' . $filename;
        }
        if (!file_exists($fullFilename)) {
            throw new RuntimeException('Template file "' . $this->config->get('templatesPathExt') . '/' . $filename . '" not found nor "' . $fullFilename . '".');
        }
        
        return $fullFilename;
    }
    
    /**
     *
     * @param  string $filename
     * @return string
     * @throws RuntimeException
     */
    private function getCacheFile(string $filename): string
    {
        $fullFilename = $this->getFullname($filename);
        $cacheFile = $this->config->get('templatesCachePath') . '/' . $filename;
        $cacheFileExists = file_exists($cacheFile);
        $cacheTime = filemtime($cacheFile);
        
        if ($cacheFileExists && false === $cacheTime) {
            throw new RuntimeException('Can not retrieve file modify time for template cache file: ' . $cacheFile);
        }
        if (false === ($tplTime = filemtime($fullFilename))) {
            throw new RuntimeException('Can not retrieve file modify time for template file: ' . $fullFilename);
        }
        
        if (!$cacheFileExists || $tplTime > $cacheTime) {
            $this->createCache($fullFilename, $cacheFile);
        }
        
        return $cacheFile;
    }
    
    /**
     *
     * @param  string $fullFilename
     * @param  string $cacheFile
     * @return void
     * @throws RuntimeException
     */
    private function createCache(string $fullFilename, string $cacheFile): void
    {
        $tplContents = file_get_contents($fullFilename);
        if ($tplContents === false) {
            throw new RuntimeException('Error reading template file: ' . $fullFilename);
        }
        $tplContentsReplacedPhpTagLong = str_replace('{{?php', '<?php ', $tplContents);
        $tplContentsReplacedPhpTagShort = str_replace('{{?', '<?php ', $tplContentsReplacedPhpTagLong);
        $tplContentsReplacedPhpEcho = str_replace('{{', '<?php echo ', $tplContentsReplacedPhpTagShort);
        $tplContentsReplacedPhpClosure = str_replace('}}', '?>', $tplContentsReplacedPhpEcho);
        $tplContentsReplaced = '<?php if (!isset($this) || !($this instanceof ' . self::class . ')) throw new \\RuntimeException("Invalid entry"); ?>' . $tplContentsReplacedPhpClosure;
        
        $dirname = dirname($cacheFile);
        if (!is_dir($dirname) && !mkdir($dirname, $this->config->get('templatesCacheMode'), true)) {
            throw new RuntimeException('Template folder is not created for template file: ' . $cacheFile);
        }
        if (false === file_put_contents($cacheFile, $tplContentsReplaced)) {
            throw new RuntimeException('Tempplate file is not created: ' . $cacheFile);
        }
    }
    
    /**
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function set(string $name, $value): void
    {
        $this->data[$name] = $this->setAsItIs ? $value : $this->encode($value);
    }

    public function __toString()
    {
        ob_start();
        foreach ($this->data as $_key => $_value) {
            $$_key = $_value;
        }
        include $this->filename;
        $contents = (string)ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function setAsItIs(string $name, $value): void
    {
        $this->setAsItIs = true;
        $this->set($name, $value);
        $this->setAsItIs = false;
    }
    
    /**
     *
     * @param  mixed $value
     * @return mixed
     */
    private function encode($value)
    {
        if (is_array($value)) {
            $ret = [];
            foreach ($value as $_key => $_value) {
                $ret[$_key] = $this->encode($_value);
            }
        } elseif (is_object($value)) {
            $ret = new stdClass();
            foreach ((array)$value as $_key => $_value) {
                $ret->$_key = $this->encode($_value);
            }
        } else {
            $ret = htmlentities($value);
        }

        return $ret;
    }
}
