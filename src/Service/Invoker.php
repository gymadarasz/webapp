<?php declare(strict_types = 1);

/**
 * Invoker
 *
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

use RuntimeException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Invoker
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Invoker
{
    const ERR_ARG_CANT_BE_INSTANTIATED = 'A method has a parameter '
            . 'which cannot be instantiated.';

    /**
     * Variable instances
     *
     * @var mixed[] $instances
     */
    protected array $instances;

    /**
     * Method invoke
     *
     * @param string[] $route           route
     * @param mixed[]  $constructorArgs constructorArgs
     * @param mixed[]  $methodArgs      methodArgs
     *
     * @return mixed
     */
    public function invoke(
        array $route,
        array $constructorArgs = [],
        $methodArgs = []
    ) {
        $ctrlr = $this->getInstance($route[0], $constructorArgs);
        $method = $ctrlr[1]->getMethod($route[1]);
        $args = $this->argsMerge(
            $method,
            $methodArgs,
            'Method ' . $route[0] . '::' . $route[1] .
                ' has one or more non-class typed parameters.'
        );
        return $ctrlr[0]->{$route[1]}(...$args);
    }

    /**
     * Method getInstance
     *
     * @param string  $class           class
     * @param mixed[] $constructorArgs constructorArgs
     *
     * @return mixed[]
     * @throws RuntimeException
     *
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    protected function getInstance(
        string $class,
        array $constructorArgs = []
    ): array {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }
        if (!class_exists($class)) {
            throw new RuntimeException('Class not exists: "' . $class . '"');
        }
        $refClass = new ReflectionClass($class);
        $constructor = ($refClass)->getConstructor();
        if (!$constructor) {
            return $this->instances[$class] = [new $class, $refClass];
        }
        $args = $this->argsMerge(
            $constructor,
            $constructorArgs,
            'Method ' . $class .
                '::__constructor() has an or more non-class typed parameters.'
        );
        return $this->instances[$class] = [new $class(...$args), $refClass];
    }

    /**
     * Method argsMerge
     *
     * @param ReflectionMethod $method         method
     * @param mixed[]          $preArgs        preArgs
     * @param string           $messageOnError messageOnError
     *
     * @return mixed[]
     */
    protected function argsMerge(
        ReflectionMethod $method,
        array $preArgs = [],
        string $messageOnError = self::ERR_ARG_CANT_BE_INSTANTIATED
    ): array {
        return array_merge(
            $preArgs,
            array_slice(
                $this->getArgs($method, $messageOnError),
                count($preArgs)
            )
        );
    }

    /**
     * Method getArgs
     *
     * @param ReflectionMethod $method         method
     * @param string           $messageOnError messageOnError
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    protected function getArgs(
        ReflectionMethod $method,
        string $messageOnError = self::ERR_ARG_CANT_BE_INSTANTIATED
    ): array {
        $params = $method->getParameters();
        $args = [];
        foreach ($params as $param) {
            $paramClass = $param->getClass();
            if (!$paramClass) {
                throw new RuntimeException($messageOnError);
            }
            $args[] = $this->getInstance($paramClass->name)[0];
        }
        return $args;
    }
}
