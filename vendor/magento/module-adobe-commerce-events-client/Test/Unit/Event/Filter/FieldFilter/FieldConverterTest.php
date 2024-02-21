<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeCommerceEventsClient\Test\Unit\Event\Filter\FieldFilter;

use Magento\AdobeCommerceEventsClient\Event\EventField;
use Magento\AdobeCommerceEventsClient\Event\Filter\FieldFilter\FieldConverter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see FieldConverter class
 */
class FieldConverterTest extends TestCase
{
    /**
     * @var FieldConverter
     */
    private FieldConverter $fieldConvertor;

    protected function setUp(): void
    {
        $this->fieldConvertor = new FieldConverter();
    }

    /**
     * @return void
     */
    public function testConvert()
    {
        $fieldsData = [
            'entity_id',
            'product.sku',
            'product.qty'
        ];

        $eventFields = $this->createFieldObjects($fieldsData);
        $fields = $this->fieldConvertor->convert($eventFields);

        self::assertEquals(2, count($fields));
        self::assertEquals('entity_id', $fields[0]->getName());
        self::assertEquals('product', $fields[1]->getName());
        self::assertFalse($fields[1]->isArray());
        self::assertEquals(2, count($fields[1]->getChildren()));
    }

    /**
     * @return void
     */
    public function testConvertMissingNameField()
    {
        $fieldsData = [
            ['converter' => 'testConverter'],
            'product[].sku',
            'product[].qty',
            'product[].name'
        ];

        $eventFields = $this->createFieldObjects($fieldsData);
        $fields = $this->fieldConvertor->convert($eventFields);

        self::assertEquals(1, count($fields));
        self::assertEquals('product', $fields[0]->getName());
        self::assertTrue($fields[0]->isArray());
        self::assertEquals(3, count($fields[0]->getChildren()));
    }

    /**
     * @return void
     */
    public function testConvertArrayFieldsNested()
    {
        $fieldsData = [
            'product[].sku',
            'product[].qty',
            'product[].name.test',
            'order.item[].id',
            'order.item[].sku'
        ];

        $eventFields = $this->createFieldObjects($fieldsData);
        $fields = $this->fieldConvertor->convert($eventFields);
        $fields = array_values($fields);

        self::assertEquals(2, count($fields));
        self::assertEquals('product', $fields[0]->getName());
        self::assertTrue($fields[0]->isArray());
        self::assertEquals(3, count($fields[0]->getChildren()));
        self::assertEquals(1, count($fields[1]->getChildren()));
        $orderFields = $fields[1]->getChildren();
        self::assertEquals(2, count($orderFields[0]->getChildren()));
    }

    /**
     * @return void
     */
    public function testConverterFieldClasses()
    {
        $fieldsData = [
            ['name'=>'product[].sku', 'converter'=>'testConverter1'],
            'product[].qty',
            ['name'=>'order.item[].id', 'converter'=>'testConverter2'],
        ];

        $eventFields = $this->createFieldObjects($fieldsData);
        $fields = $this->fieldConvertor->convert($eventFields);
        $fields = array_values($fields);

        self::assertEquals(2, count($fields));
        self::assertEquals('product', $fields[0]->getName());
        self::assertEquals('order', $fields[1]->getName());
        self::assertEquals(2, count($fields[0]->getChildren()));
        self::assertEquals('testConverter1', $fields[0]->getChildren()[0]->getConverterClass());
        self::assertEquals(null, $fields[0]->getChildren()[1]->getConverterClass());
        self::assertEquals(1, count($fields[1]->getChildren()));
        self::assertEquals('testConverter2', $fields[1]->getConverterClass());
    }

    /**
     * Creates EventField object
     *
     * @param array $fieldsData
     * @return array
     */
    private function createFieldObjects(array $fieldsData) : array
    {
        $eventFields = [];
        foreach ($fieldsData as $fieldData) {
            if (is_string($fieldData)) {
                $fieldData = [EventField::NAME => $fieldData];
            }
            $eventFields[] = new EventField($fieldData);
        }
        return $eventFields;
    }
}
