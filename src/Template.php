<?php

namespace Madsoft\App;

use function ob_start;
use function ob_get_contents;
use function ob_end_clean;
use function is_array;
use function is_object;
use function htmlentities;
use stdClass;

class Template
{
    private string $basepath = __DIR__ . '/templates/';

    private string $filename;

    private bool $setAsItIs = false;

    /** @var array<mixed> $data */
    private array $data = [];

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param mixed $value
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
        include $this->basepath . $this->filename;
        $contents = (string)ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * @param mixed $value
     */
    public function setAsItIs(string $name, $value): void
    {
        $this->setAsItIs = true;
        $this->set($name, $value);
        $this->setAsItIs = false;
    }

    /**
     * @param mixed $value
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
