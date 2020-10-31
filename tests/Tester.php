<?php declare(strict_types = 1);

namespace Madsoft\Test;

use function xdebug_start_code_coverage;
use function strpos;
use function strlen;
use function implode;
use function count;
use Exception;
use RuntimeException;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Logger;
use GuzzleHttp\Client;

// TODO add coverage stat

class Tester
{
    private Config $config;
    private Logger $logger;
    private Client $client;

    /** @var array<string> */
    private array $errors;

    private int $passes;

    /**
     * @param array<Test> $tests
     */
    public function __construct(Config $config, Logger $logger, Client $client, array $tests)
    {
        if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Test can run only from command line.');
        }

        $this->config = $config;
        $this->logger = $logger;

        $env = $this->config->getEnv();
        if ($env !== 'test') {
            $this->logger->warning('Environment should equals to "test". Currently it is "' . $env . '" but changed to "test".');
            $this->config->setEnv('test');
        }

        $this->client = $client;

        $this->errors = [];
        $this->passes = 0;

        try {
            foreach ($tests as $test) {
                $test->run($this);
            }
        } catch (Exception $e) {
            $this->logger->doLogException($e);
        }

        if ($env !== 'test') {
            $this->logger->info("Reverting back the environment to: '$env'");
            $this->config->setEnv($env);
            $this->logger->info("Environment is '" . $this->config->getEnv() . "' now.");
        } else {
            $this->logger->info("Environment is '" . $this->config->getEnv() . "' don't forget to revert it back in your " . Config::ENV_FILE);
        }
    }

    public function get(string $url): string
    {
        return $this->client->get($url)->getBody()->getContents();
    }

    /**
     * @param array<mixed> $data
     */
    public function post(string $url, array $data): string
    {
        return $this->client->post($url, ['form_params' => $data])->getBody()->getContents();
    }

    public function assertContains(string $expected, string $results, string $message = 'Results should contains the expected string.'): void
    {
        $ok = strpos($results, $expected) !== false;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    public function assertNotContains(string $expected, string $results, string $message = 'Results should not contains the expected string.'): void
    {
        $ok = strpos($results, $expected) === false;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * @param array<mixed> $results
     */
    public function assertCount(int $expected, array $results, string $message = 'Results array should has expected count.'): void
    {
        $ok = count($results) === $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    public function assertLongerThan(int $expected, string $results, string $message = 'Results string should not long enough as expected.'): void
    {
        $ok = strlen($results) > $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $results
     */
    public function assertEquals($expected, $results, string $message = 'Results should equals to expected (type strict).'): void
    {
        $ok = $results === $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    public function stat(): int
    {
        echo "\nSuccess: " . $this->passes;
        if ($this->errors) {
            echo "\nFailure: " . count($this->errors);
            echo implode("\n", $this->errors);
        } else {
            echo "\nAll tests are passed.";
        }
        echo "\n";
        return count($this->errors);
    }

    private function fail(string $message): void
    {
        $this->logger->error('Test failed: ' . $message);
        try {
            throw new Exception();
        } catch (Exception $e) {
        }
        $this->errors[] =
            "\nTest failed: " . $message .
            "\nTrace:\n" . $e->getTraceAsString();
        echo 'X';
    }

    private function ok(): void
    {
        $this->passes++;
        echo ".";
    }
}
