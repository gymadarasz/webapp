<?php declare(strict_types = 1);

/**
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
use function count;

/**
 * Assertor
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Assertor extends Helper
{
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
            $arr1 = explode(' ', (string)$expected);
            $arr2 = explode(' ', (string)$results);
            $message .= "\nDifferents between given values are:\n" .
                        join(' ', array_diff($arr1, $arr2)) . "\n";
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
     * Method fail
     *
     * @param string $message Message
     *
     * @return void
     * @throws Exception
     */
    protected function fail(string $message): void
    {
        $this->tester->getLogger()->fail('Test failed: ' . $message);
        try {
            throw new Exception();
        } catch (Exception $e) {
            $this->tester->addError(
                "\nTest failed: " . $message . "\nTrace:\n" . $e->getTraceAsString()
            );
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
        $this->tester->incrementPasses();
        echo ".";
    }
}
