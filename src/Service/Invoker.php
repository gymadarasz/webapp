<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

use RuntimeException;
use ReflectionClass;
use ReflectionMethod;

class Invoker
{
    const ERR_ARG_CANT_BE_INSTANTIATED = 'A method has a parameter which cannot be instantiated.';

    /** @var array<mixed> $instances */
    private array $instances;

    /**
     * @param array<string> $route
     * @param array<mixed> $constructorArgs
     * @param array<mixed> $methodArgs
     * @return mixed
     */
    public function invoke(array $route, array $constructorArgs = [], $methodArgs = [])
    {
        $ctrlr = $this->getInstance($route[0], $constructorArgs);
        $method = $ctrlr[1]->getMethod($route[1]);
        $args = $this->argsMerge($method, $methodArgs, 'Method ' . $route[0] . '::' . $route[1] . ' has an or more non-class typed parameters.');
        return $ctrlr[0]->{$route[1]}(...$args);
    }

    /**
     * @param array<mixed> $constructorArgs
     * @return array<mixed>
     */
    private function getInstance(string $class, array $constructorArgs = []): array
    {
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
        $args = $this->argsMerge($constructor, $constructorArgs, 'Method ' . $class . '::__constructor() has an or more non-class typed parameters.');
        return $this->instances[$class] = [new $class(...$args), $refClass];
    }

    /**
     * @param array<mixed> $preArgs
     * @return array<mixed>
     */
    private function argsMerge(ReflectionMethod $method, array $preArgs = [], string $messageOnError = self::ERR_ARG_CANT_BE_INSTANTIATED): array
    {
        return array_merge(
            $preArgs,
            array_slice(
                $this->getArgs($method, $messageOnError),
                count($preArgs)
            )
        );
    }

    /**
     * @return array<mixed>
     */
    private function getArgs(ReflectionMethod $method, string $messageOnError = self::ERR_ARG_CANT_BE_INSTANTIATED): array
    {
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
