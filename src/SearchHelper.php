<?php

namespace Rokka\Client;

/**
 * Helper class for Search parameter building for Rokka.io service.
 */
class SearchHelper
{
    /**
     * Validates the field name.
     *
     * @param string $fieldName
     *
     * @return bool Returns true if the field name is valid, false otherwise
     */
    public static function validateFieldName($fieldName)
    {
        // Field names must be shorter than 54 chars, and match the given format.
        return 54 > \strlen($fieldName) && 1 === preg_match('/^(user:((str|array|date|latlon|int|double):)?)?[a-z0-9_]{1,54}$/', $fieldName);
    }

    /**
     * Builds the "sort" parameter for the source image listing API endpoint.
     *
     * The sort direction can either be: "asc", "desc" (or the boolean TRUE value, treated as "asc")
     *
     * @param array $sorts The sorting options, as an associative array "field => sort-direction"
     *
     * @return string
     */
    public static function buildSearchSortParameter(array $sorts)
    {
        if (empty($sorts)) {
            return '';
        }
        $sorting = [];
        foreach ($sorts as $sortField => $direction) {
            if (!self::validateFieldName((string) $sortField)) {
                throw new \LogicException(sprintf('Invalid field name "%s" for sorting field', $sortField));
            }
            if (!\in_array($direction, [true, 'desc', 'asc'], true)) {
                throw new \LogicException(sprintf('Wrong sorting direction "%s" for field "%s". Use either "desc", "asc"', $direction, $sortField));
            }

            // Only output the "desc" direction as "asc" is the default sorting
            $sorting[] = $sortField.('desc' === $direction ? ' '.$direction : '');
        }

        if (empty($sorting)) {
            return '';
        }

        return implode(',', $sorting);
    }
}
