<?php declare(strict_types = 1);

/**
 * Tester
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\Test;

use Exception;
use GuzzleHttp\Client;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Invoker;
use GyMadarasz\WebApp\Service\Logger;
use RuntimeException;
use function count;
use function implode;

// TODO add coverage stat

/**
 * Tester
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Tester
{
    protected Config $config;
    protected Logger $logger;
    protected Assertor $assertor;
    protected Inspector $inspector;
    protected Client $client;

    /**
     * Variable errors
     *
     * @var string[]
     */
    protected array $errors;

    protected int $passes;

    /**
     * Method test
     *
     * @param Invoker   $invoker   invoker
     * @param Config    $config    config
     * @param Logger    $logger    logger
     * @param Assertor  $assertor  assertor
     * @param Inspector $inspector inspector
     * @param Client    $client    client
     * @param string[]  $tests     test
     *
     * @return Tester
     * @throws RuntimeException
     */
    public function test(
        Invoker $invoker,
        Config $config,
        Logger $logger,
        Assertor $assertor,
        Inspector $inspector,
        Client $client,
        array $tests
    ): self {
        if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Test can run only from command line.');
        }

        $this->config = $config;
        $this->logger = $logger;
        $this->assertor = $assertor;
        $this->assertor->setTester($this);
        $this->inspector = $inspector;
        $this->inspector->setTester($this);

        $env = $this->config->getEnv();
        if ($env !== 'test') {
            $this->logger->warning(
                'Environment should equals to "test". Currently it is "' . $env .
                    '" but changed to "test".'
            );
            $this->config->setEnv('test');
        }

        $this->client = $client;

        $this->errors = [];
        $this->passes = 0;

        try {
            foreach ($tests as $test) {
                echo $invoker->invoke([$test, 'test'], [$this], [$this]);
            }
        } catch (Exception $e) {
            $this->errors[] = '\nException (' . get_class($e) . '): ' .
                    $e->getMessage() . "\nTrace:\n" . $e->getTraceAsString();
            $this->logger->doLogException($e);
        }

        if ($env !== 'test') {
            $this->logger->info(
                "Reverting back the environment to: '$env'"
            );
            $this->config->setEnv($env);
            $this->logger->info(
                "Environment is '" . $this->config->getEnv() . "' now."
            );
        }
        if ($env === 'test') {
            $this->logger->info(
                "Environment is '" . $this->config->getEnv() .
                    "' don't forget to revert it back in your " . Config::ENV_FILE
            );
        }

        return $this;
    }

    /**
     * Method get
     *
     * @param string $url URL
     *
     * @return string
     */
    public function get(string $url): string
    {
        return $this->client->get($url)->getBody()->getContents();
    }

    /**
     * Method post
     *
     * @param string  $url  URL
     * @param mixed[] $data Data
     *
     * @return string
     */
    public function post(string $url, array $data): string
    {
        return $this->client->post(
            $url,
            [
            'form_params' => $data
            ]
        )->getBody()->getContents();
    }

    /**
     * Method stat
     *
     * @return int
     */
    public function stat(): int
    {
        echo "\nSuccess: " . $this->passes;
        $output = "\nAll tests are passed.";
        if ($this->errors) {
            $output = "\nFailure: " . count($this->errors) .
                implode("\n", $this->errors);
        }
        echo "$output\n";
        return count($this->errors);
    }

    /**
     * Method getAssertor
     *
     * @return Assertor
     */
    public function getAssertor(): Assertor
    {
        return $this->assertor;
    }
    
    /**
     * Method getLogger
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * Method addError
     *
     * @param string $error error
     *
     * @return void
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
    
    /**
     * Method incrementPasses
     *
     * @return int
     */
    public function incrementPasses(): int
    {
        return $this->passes++;
    }

    /**
     * Method setAssertor
     *
     * @param \GyMadarasz\Test\Assertor $assertor assertor
     *
     * @return void
     */
    public function setAssertor(Assertor $assertor): void
    {
        $this->assertor = $assertor;
    }
}
