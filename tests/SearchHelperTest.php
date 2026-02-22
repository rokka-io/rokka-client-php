<?php

namespace Rokka\Client\Tests;

use Rokka\Client\SearchHelper;

class SearchHelperTest extends \PHPUnit\Framework\TestCase
{
    public static function provideValidFieldNames(): array
    {
        return [
            'simple field' => ['name'],
            'with underscores' => ['my_field'],
            'with numbers' => ['field123'],
            'user prefixed' => ['user:my_field'],
            'user typed str' => ['user:str:my_field'],
            'user typed int' => ['user:int:my_field'],
            'user typed date' => ['user:date:my_field'],
            'user typed double' => ['user:double:my_field'],
            'user typed array' => ['user:array:my_field'],
            'user typed latlon' => ['user:latlon:my_field'],
            'dynamic prefixed' => ['dynamic:str:some_module:my_field'],
            'static prefixed' => ['static:int:some_module:my_field'],
            'special deletedDate' => ['deletedDate'],
        ];
    }

    /**
     * @dataProvider provideValidFieldNames
     */
    public function testValidateFieldNameAcceptsValid(string $fieldName): void
    {
        $this->assertTrue(SearchHelper::validateFieldName($fieldName));
    }

    public static function provideInvalidFieldNames(): array
    {
        return [
            'uppercase' => ['MyField'],
            'spaces' => ['my field'],
            'too long' => [str_repeat('a', 55)],
            'special chars' => ['field@name'],
            'empty' => [''],
            'dots' => ['field.name'],
        ];
    }

    /**
     * @dataProvider provideInvalidFieldNames
     */
    public function testValidateFieldNameRejectsInvalid(string $fieldName): void
    {
        $this->assertFalse(SearchHelper::validateFieldName($fieldName));
    }

    public function testBuildSearchSortParameterEmpty(): void
    {
        $this->assertSame('', SearchHelper::buildSearchSortParameter([]));
    }

    public function testBuildSearchSortParameterSingleAsc(): void
    {
        $this->assertSame('name', SearchHelper::buildSearchSortParameter(['name' => 'asc']));
    }

    public function testBuildSearchSortParameterSingleDesc(): void
    {
        $this->assertSame('name desc', SearchHelper::buildSearchSortParameter(['name' => 'desc']));
    }

    public function testBuildSearchSortParameterBoolTrue(): void
    {
        $this->assertSame('name', SearchHelper::buildSearchSortParameter(['name' => true]));
    }

    public function testBuildSearchSortParameterMultiple(): void
    {
        $this->assertSame(
            'name desc,created',
            SearchHelper::buildSearchSortParameter(['name' => 'desc', 'created' => 'asc'])
        );
    }

    public function testBuildSearchSortParameterThrowsOnInvalidField(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid field name');
        SearchHelper::buildSearchSortParameter(['Invalid Field!' => 'asc']);
    }

    public function testBuildSearchSortParameterThrowsOnInvalidDirection(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Wrong sorting direction');
        SearchHelper::buildSearchSortParameter(['name' => 'invalid']);
    }

    public function testBuildSearchSortParameterThrowsOnFalseBool(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Wrong sorting direction');
        SearchHelper::buildSearchSortParameter(['name' => false]);
    }
}
