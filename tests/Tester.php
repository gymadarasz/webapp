<?php declare(strict_types = 1);

namespace GyMadarasz\Test;

use function xdebug_start_code_coverage;
use function strpos;
use function strlen;
use function implode;
use function count;
use Exception;
use RuntimeException;
use GyMadarasz\WebApp\Service\Invoker;
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
     * @param array<string> $tests
     */
    public function test(Invoker $invoker, Config $config, Logger $logger, Client $client, array $tests): self
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
                echo $invoker->invoke([$test, 'test'], [$this]);
                // $test->run($this);
            }
        } catch (Exception $e) {
            $this->errors[] = '\nException (' . get_class($e) . '): ' . $e->getMessage() . "\nTrace:\n" . $e->getTraceAsString();
            $this->logger->doLogException($e);
        }

        if ($env !== 'test') {
            $this->logger->info("Reverting back the environment to: '$env'");
            $this->config->setEnv($env);
            $this->logger->info("Environment is '" . $this->config->getEnv() . "' now.");
        } else {
            $this->logger->info("Environment is '" . $this->config->getEnv() . "' don't forget to revert it back in your " . Config::ENV_FILE);
        }

        return $this;
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

    /**
     * @param mixed $expected
     * @param mixed $results
     */
    public function assertNotEquals($expected, $results, string $message = 'Results should equals to expected (type strict).'): void
    {
        $ok = $results !== $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    public function assertTrue(bool $results, string $message = 'Results should be true.'): void
    {
        $ok = $results === true;
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
        $this->logger->fail('Test failed: ' . $message);
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


    /**
     * @return array<mixed>
     */
    public function getInputFieldValue(string $type, string $name, string $contents): array
    {
        if (!preg_match_all('/<input\s+type\s*=\s*"' . $type . '"\s*name\s*=\s*"' . preg_quote($name) . '"\s*value=\s*"([^"]*)"/', $contents, $matches)) {
            throw new RuntimeException('Input element not found:  <input type="' . $type . '" name="' . $name . '" value=...>');
        }
        if (!isset($matches[1]) || !isset($matches[1][0])) {
            throw new RuntimeException('Input element does not have a value: <input type="' . $type . '" name="' . $name . '" value=...>');
        }
        return $matches[1];
    }
    /** @return array<string> */
    public function getLinks(string $hrefStarts, string $contents): array
    {
        if (!preg_match_all('/<a href="(' . preg_quote($hrefStarts) . '[^"]*)"/', $contents, $matches)) {
            return [];
        }
        return $matches[1];
    }


    /**
     * @return array<array<string>>
     */
    public function getSelectsValues(string $name, string $contents): array
    {
        $selects = $this->getSelectFieldContents($name, $contents);
        $selectsValues = [];
        foreach ($selects as $select) {
            $options = $this->getSelectOptions($select);
            $values = [];
            foreach ($options as $option) {
                $values[] = $this->getOptionValue($option);
            }
            $selectsValues[] = $values;
        }
        return $selectsValues;
    }

    /**
     * @return string
     */
    public function getOptionValue(string $option): string
    {
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)) {
            throw new RuntimeException('Unrecognised value in option: ' . $option); // TODO check inner text??
        }
        return $matches[1];
    }

    /**
     * @return array<string>
     */
    public function getSelectOptions(string $select): array
    {
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            return [];
        }
        return $matches[0];
    }


    /**
     * @return array<string>
     */
    public function getSelectFieldValue(string $name, string $contents): array
    {
        $selects = $this->getSelectFieldContents($name, $contents);
        $values = [];
        foreach ($selects as $select) {
            $multiple = $this->isMultiSelectField($select);
            unset($value);
            if ($options = $this->getOptionFieldContents($select)) {
                if ($multiple) {
                    $value = [];
                    foreach ($options as $option) {
                        if ($this->isOptionSelected($option)) {
                            $value[] = $this->getOptionFieldValue($option);
                        }
                    }
                } else {
                    foreach ($options as $option) {
                        if ($this->isOptionSelected($option) || !isset($value)) {
                            $value = $this->getOptionFieldValue($option);
                        }
                    }
                    $values[] = $value;
                }
            } else {
                throw new RuntimeException('A select element has not any option: ' . explode('\n', $select)[0] . '...');
            }
        }
        return $values;
    }

    public function getOptionFieldValue(string $option): string
    {
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)) {
            throw new RuntimeException('Unrecognised value in option: ' . $option);
        }
        return $matches[1];
    }

    public function isOptionSelected(string $option): bool
    {
        return (bool)preg_match('/<option\s[^>]*\bselected\b/', $option, $matches);
    }

    public function isMultiSelectField(string $select): bool
    {
        return (bool)preg_match('/<select\s[^>]*\bmultiple\b/', $select, $matches);
    }

    /**
     * @return array<string>
     */
    public function getOptionFieldContents(string $select): array
    {
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            throw new RuntimeException('Unrecognised options');
        }
        return $matches[0];
    }
    
    /**
     * @return array<string>
     */
    public function getSelectFieldContents(string $name, string $contents): array
    {
        if (!preg_match_all('/<select\s+name\s*=\s*"' . preg_quote($name) . '"(.+?)<\/select>/s', $contents, $matches)) {
            throw new RuntimeException('Select element not found: <select name="' . $name . '"...</select>');
        }
        if (!isset($matches[0])) {
            throw new RuntimeException('Select element does not have a value: <select name="' . $name . '"...</select>');
        }
        return $matches[0];
    }
}
