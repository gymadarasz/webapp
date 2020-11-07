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
        $oke = strpos($results, $expected) !== false;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = strpos($results, $expected) === false;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = count($results) === $expected;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = strlen($results) > $expected;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = $results === $expected;
        if (!$oke) {
            if (is_string($expected)) {
                $arr1 = explode(' ', $expected);
                $arr2 = explode(' ', $results);
                $message .= "\nDifferents between given values are:\n" .
                        join(' ', array_diff($arr1, $arr2)) . "\n";
            }
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = $results !== $expected;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
        $oke = $results === true;
        if (!$oke) {
            $this->fail($message);
            return;
        }
        $this->oke();
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
            $this->errors[] = "\nTest failed: " . $message .
            "\nTrace:\n" . $e->getTraceAsString();
            echo 'X';
        }
    }

    /**
     * Method oke
     *
     * @return void
     */
    protected function oke(): void
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
        $matches = [];
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
        return $matches[1] ?: [];
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
        $matches = [];
        if (!preg_match_all(
            '/<a href="(' . preg_quote($hrefStarts) . '[^"]*)"/',
            $contents,
            $matches
        )
        ) {
            return [];
        }
        return $matches[1] ?: [];
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
        $matches = [];
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)
        ) {
            // TODO check inner text??
            throw new RuntimeException('Unrecognised value in option: ' . $option);
        }
        return $matches[1] ?: [];
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
        $matches = [];
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            return [];
        }
        return $matches[0] ?: [];
    }


    /**
     * Method getSelectFieldValue
     *
     * @param string $name     Name
     * @param string $contents Contents
     *
     * @return array<int, string[]|string|null>
     * @throws RuntimeException
     */
    public function getSelectFieldValue(string $name, string $contents): array
    {
        $selects = $this->getSelectFieldContents($name, $contents);
        $values = [];
        foreach ($selects as $select) {
            $multiple = $this->isMultiSelectField($select);
            unset($value);
            $options = $this->getOptionFieldContents($select);
            if (!$options) {
                throw new RuntimeException(
                    'A select element has not any option: ' .
                    explode('\n', $select)[0] . '...'
                );
            }
            
            if ($multiple) {
                $value = $this->getSelectedOptionValueMultiple($options);
            }
            if (!$multiple) {
                $value = $this->getSelectedOptionValueSimple(
                    $options,
                    isset($value) ? $value : null
                );
                $values[] = $value;
            }
        }
        return $values;
    }
    
    /**
     * Method getSelectedOptionValueSimple
     *
     * @param string[] $options options
     * @param string[] $value   value
     *
     * @return string[]|string|null
     */
    protected function getSelectedOptionValueSimple(
        array $options,
        array $value = null
    ) {
        foreach ($options as $option) {
            if ($this->isOptionSelected($option) || null === $value) {
                $value = $this->getOptionFieldValue($option);
            }
        }
        return $value;
    }
    
    /**
     * Method getSelectedOptionValueMultiple
     *
     * @param string[] $options options
     *
     * @return string[]
     */
    protected function getSelectedOptionValueMultiple(array $options): array
    {
        $value = [];
        foreach ($options as $option) {
            if ($this->isOptionSelected($option)) {
                $value[] = $this->getOptionFieldValue($option);
            }
        }
        return $value;
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
        $matches = [];
        if (!preg_match('/<option\b.+?\bvalue\b\s*=\s*"(.+?)"/', $option, $matches)
        ) {
            throw new RuntimeException('Unrecognised value in option: ' . $option);
        }
        return $matches[1] ?: [];
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
        return (bool)preg_match('/<option\s[^>]*\bselected\b/', $option);
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
        return (bool)preg_match('/<select\s[^>]*\bmultiple\b/', $select);
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
        $matches = [];
        if (!preg_match_all('/<option(.+?)<\/option>/s', $select, $matches)) {
            throw new RuntimeException('Unrecognised options');
        }
        return $matches[0] ?: [];
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
        $matches = [];
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
        return $matches[0] ?: [];
    }
}
