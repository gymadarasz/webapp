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
use function strlen;
use function strpos;

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
     * @param Invoker  $invoker invoker
     * @param Config   $config  config
     * @param Logger   $logger  logger
     * @param Client   $client  client
     * @param string[] $tests   test
     *
     * @return Tester
     * @throws RuntimeException
     */
    public function test(
        Invoker $invoker,
        Config $config,
        Logger $logger,
        Client $client,
        array $tests
    ): self {
        if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Test can run only from command line.');
        }

        $this->config = $config;
        $this->logger = $logger;

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
        } else {
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
     * Method assertContains
     *
     * @param string $expected Expected
     * @param string $results  Results
     * @param string $message  Message
     *
     * @return void
     */
    public function assertContains(
        string $expected,
        string $results,
        string $message = 'Results should contains the expected string.'
    ): void {
        $ok = strpos($results, $expected) !== false;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertNotContains
     *
     * @param string $expected Expected
     * @param string $results  Results
     * @param string $message  Message
     *
     * @return void
     */
    public function assertNotContains(
        string $expected,
        string $results,
        string $message = 'Results should not contains the expected string.'
    ): void {
        $ok = strpos($results, $expected) === false;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertCount
     *
     * @param int     $expected Expected
     * @param mixed[] $results  Results
     * @param string  $message  Message
     *
     * @return void
     */
    public function assertCount(
        int $expected,
        array $results,
        string $message = 'Results array should has expected count.'
    ): void {
        $ok = count($results) === $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertLongerThan
     *
     * @param int    $expected Expected
     * @param string $results  Results
     * @param string $message  Message
     *
     * @return void
     */
    public function assertLongerThan(
        int $expected,
        string $results,
        string $message = 'Results string should not long enough as expected.'
    ): void {
        $ok = strlen($results) > $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertEquals
     *
     * @param mixed  $expected Expected
     * @param mixed  $results  Results
     * @param string $message  Message
     *
     * @return void
     */
    public function assertEquals(
        $expected,
        $results,
        string $message = 'Results should equals to expected (type strict).'
    ): void {
        $ok = $results === $expected;
        if (!$ok) {
            if (is_string($expected)) {
                $a1 = explode(' ', $expected);
                $a2 = explode(' ', $results);
                $message .= "\nDifferents between given values are:\n" .
                        join(' ', array_diff($a1, $a2)) . "\n";
            }
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertNotEquals
     *
     * @param mixed  $expected Expected
     * @param mixed  $results  Results
     * @param string $message  Message
     *
     * @return void
     */
    public function assertNotEquals(
        $expected,
        $results,
        string $message = 'Results should equals to expected (type strict).'
    ): void {
        $ok = $results !== $expected;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method assertTrue
     *
     * @param bool   $results Results
     * @param string $message Message
     *
     * @return void
     */
    public function assertTrue(
        bool $results,
        string $message = 'Results should be true.'
    ): void {
        $ok = $results === true;
        if (!$ok) {
            $this->fail($message);
        } else {
            $this->ok();
        }
    }

    /**
     * Method stat
     *
     * @return int
     */
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

    /**
     * Method fail
     *
     * @param string $message Message
     *
     * @return void
     * @throws Exception
     */
    protected function fail(string $message): void
    {
        $this->logger->fail('Test failed: ' . $message);
        try {
            throw new Exception();
        } catch (Exception $e) {
        }
        $this->errors[] = "\nTest failed: " . $message .
            "\nTrace:\n" . $e->getTraceAsString();
        echo 'X';
    }

    /**
     * Method ok
     *
     * @return void
     */
    protected function ok(): void
    {
        $this->passes++;
        echo ".";
    }


    /**
     * Method getInputFieldValue
     *
     * @param string $type     Type
     * @param string $name     Name
     * @param string $contents Contents
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    public function getInputFieldValue(
        string $type,
        string $name,
        string $contents
    ): array {
        if (!preg_match_all(
            '/<input\s+type\s*=\s*"' . $type .
                '"\s*name\s*=\s*"' . preg_quote($name) .
            '"\s*value=\s*"([^"]*)"/',
            $contents,
            $matches
        )
        ) {
            throw new RuntimeException(
                'Input element not found:  <input type="' .
                $type . '" name="' . $name . '" value=...>'
            );
        }
        if (!isset($matches[1]) || !isset($matches[1][0])) {
            throw new RuntimeException(
                'Input element does not have a value: <input type="' .
                    $type . '" name="' . $name . '" value=...>'
            );
        }
        return $matches[1];
    }
    
    /**
     * Method getLinks
     *
     * @param string $hrefStarts HrefStarts
     * @param string $contents   Contents
     *
     * @return string[]
     */
    public function getLinks(string $hrefStarts, string $contents): array
    {
        if (!preg_match_all(
            '/<a href="(' . preg_quote($hrefStarts) . '[^"]*)"/',
            $contents,
            $matches
        )
        ) {
            return [];
        }
        return $matches[1];
    }

    /**
     * Method getSelectsValues
     *
     * @param string $name     Name
     * @param string $contents Contents
     *
     * @return string[][]
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
     * Method getOptionValue
     *
     * @param string $option Option
     *
     * @return string
     * @throws RuntimeException
     */
    public function getOptionValue(string $option): string
    {
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)
        ) {
            // TODO check inner text??
            throw new RuntimeException('Unrecognised value in option: ' . $option);
        }
        return $matches[1];
    }

    /**
     * Method getSelectOptions
     *
     * @param string $select Select
     *
     * @return string[]
     */
    public function getSelectOptions(string $select): array
    {
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            return [];
        }
        return $matches[0];
    }


    /**
     * Method getSelectFieldValue
     *
     * @param string $name     Name
     * @param string $contents Contents
     *
     * @return string[]
     * @throws RuntimeException
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
                throw new RuntimeException(
                    'A select element has not any option: ' .
                    explode('\n', $select)[0] . '...'
                );
            }
        }
        return $values;
    }

    /**
     * Method getOptionFieldValue
     *
     * @param string $option Option
     *
     * @return string
     * @throws RuntimeException
     */
    public function getOptionFieldValue(string $option): string
    {
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)
        ) {
            throw new RuntimeException('Unrecognised value in option: ' . $option);
        }
        return $matches[1];
    }

    /**
     * Method isOptionSelected
     *
     * @param string $option Option
     *
     * @return bool
     */
    public function isOptionSelected(string $option): bool
    {
        return (bool)preg_match('/<option\s[^>]*\bselected\b/', $option, $matches);
    }

    /**
     * Method isMultiSelectField
     *
     * @param string $select Select
     *
     * @return bool
     */
    public function isMultiSelectField(string $select): bool
    {
        return (bool)preg_match('/<select\s[^>]*\bmultiple\b/', $select, $matches);
    }

    /**
     * Method getOptionFieldContents
     *
     * @param string $select Select
     *
     * @return string[]
     * @throws RuntimeException
     */
    public function getOptionFieldContents(string $select): array
    {
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            throw new RuntimeException('Unrecognised options');
        }
        return $matches[0];
    }
    
    /**
     * Method getSelectFieldContents
     *
     * @param string $name     Name
     * @param string $contents Contents
     *
     * @return string[]
     * @throws RuntimeException
     */
    public function getSelectFieldContents(string $name, string $contents): array
    {
        if (!preg_match_all(
            '/<select\s+name\s*=\s*"' . preg_quote($name) .
                        '"(.+?)<\/select>/s',
            $contents,
            $matches
        )
        ) {
            throw new RuntimeException(
                'Select element not found: <select name="' . $name . '"...</select>'
            );
        }
        if (!isset($matches[0])) {
            throw new RuntimeException(
                'Select element does not have a value: <select name="' .
                    $name . '"...</select>'
            );
        }
        return $matches[0];
    }
}
