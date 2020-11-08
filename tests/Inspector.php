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

use RuntimeException;

/**
 * Inspector
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Inspector extends Helper
{
    
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
     * @return mixed[]
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
                    $value ?? null
                );
            }
            $values[] = $value ?? null;
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
