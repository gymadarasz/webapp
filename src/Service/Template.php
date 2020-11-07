<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

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

/**
 * Template
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Template
{
    protected string $filename;

    protected bool $setAsItIs = false;

    /**
     * Variable data
     *
     * @var mixed[] $data
     */
    protected array $data = [];

    protected Config $config;

    /**
     * Method __construct
     *
     * @param Config $config config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Method create
     *
     * @param string  $filename filename
     * @param mixed[] $data     data
     *
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
     * Method getFullname
     *
     * @param string $filename filename
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getFullname(string $filename): string
    {
        $fullFilename = $this->config->get('templatesPathExt') . '/' . $filename;
        
        if (!file_exists($fullFilename)) {
            $fullFilename = $this->config->get('templatesPath') . '/' . $filename;
        }
        if (!file_exists($fullFilename)) {
            throw new RuntimeException(
                'Template file "' . $this->config->get('templatesPathExt') .
                    '/' . $filename . '" not found nor "' . $fullFilename . '".'
            );
        }
        
        return $fullFilename;
    }
    
    /**
     * Method getCacheFile
     *
     * @param string $filename filename
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getCacheFile(string $filename): string
    {
        $fullFilename = $this->getFullname($filename);
        $cacheFile = $this->config->get('templatesCachePath') .
        '/' . $filename . '.php';
        $cacheFileExists = file_exists($cacheFile);
        $cacheTime = $cacheFileExists ? filemtime($cacheFile) : false;
        
        if ($cacheFileExists && false === $cacheTime) {
            throw new RuntimeException(
                'Can not retrieve file modify time for template cache file: ' .
                    $cacheFile
            );
        }
        $tplTime = filemtime($fullFilename);
        if (false === $tplTime) {
            throw new RuntimeException(
                'Can not retrieve file modify time for template file: ' .
                    $fullFilename
            );
        }
        
        if (!$cacheFileExists || $tplTime > $cacheTime) {
            $this->createCache($fullFilename, $cacheFile);
        }
        
        return $cacheFile;
    }
    
    /**
     * Method createCache
     *
     * @param string $fullFilename fullFilename
     * @param string $cacheFile    cacheFile
     *
     * @return void
     * @throws RuntimeException
     */
    protected function createCache(string $fullFilename, string $cacheFile): void
    {
        $tplContents = file_get_contents($fullFilename);
        if ($tplContents === false) {
            throw new RuntimeException(
                'Error reading template file: ' . $fullFilename
            );
        }
        $tplTagLong = str_replace(
            '{{?php',
            '<?php ',
            $tplContents
        );
        $tplTagShort = str_replace(
            '{{?',
            '<?php ',
            $tplTagLong
        );
        $tplEcho = str_replace(
            '{{',
            '<?php echo ',
            $tplTagShort
        );
        $tplClosure = str_replace(
            '}}',
            '?>',
            $tplEcho
        );
        $tplCntsRplcd = '<?php if (!isset($this) || !($this instanceof ' .
                self::class .
                ')) throw new \\RuntimeException("Invalid entry"); ?>' .
                $tplClosure;
        
        $dirname = dirname($cacheFile);
        if (!is_dir($dirname)
            && !mkdir($dirname, $this->config->get('templatesCacheMode'), true)
        ) {
            throw new RuntimeException(
                'Template folder is not created for template file: ' . $cacheFile
            );
        }
        if (false === file_put_contents($cacheFile, $tplCntsRplcd)) {
            throw new RuntimeException(
                'Tempplate file is not created: ' . $cacheFile
            );
        }
    }
    
    /**
     * Method set
     *
     * @param string $name  name
     * @param mixed  $value value
     *
     * @return void
     */
    public function set(string $name, $value): void
    {
        $this->data[$name] = $this->setAsItIs ? $value : $this->encode($value);
    }

    /**
     * Method __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        ob_start();
        foreach ($this->data as $key => $value) {
            $$key = $value;
        }
        include $this->filename;
        $contents = (string)ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Method setAsItIs
     *
     * @param string $name  name
     * @param mixed  $value value
     *
     * @return void
     */
    public function setAsItIs(string $name, $value): void
    {
        $this->setAsItIs = true;
        $this->set($name, $value);
        $this->setAsItIs = false;
    }
    
    /**
     * Method encode
     *
     * @param mixed $value value
     *
     * @return mixed
     */
    protected function encode($value)
    {
        $ret = htmlentities($value);
        if (is_array($value)) {
            $ret = [];
            foreach ($value as $key => $value) {
                $ret[$key] = $this->encode($value);
            }
        } elseif (is_object($value)) {
            $ret = new stdClass();
            foreach ((array)$value as $key => $value) {
                $ret->$key = $this->encode($value);
            }
        }

        return $ret;
    }
}
