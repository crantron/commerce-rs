<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Framework\Module;

use Magento\Framework\Event\ManagerInterface;

/**
 * Custom class with different
 */
class CustomClass
{
    /** @var ManagerInterface  */
    private ManagerInterface $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function dispatchSingleQuotesSingleLine()
    {
        $this->eventManager->dispatch('event_single_quotes', []);
    }

    public function dispatchDoubleQuotesSingleLine()
    {
        $this->eventManager->dispatch("event_double_quotes", []);
    }

    public function dispatchSingleQuotesMultipleLine()
    {
        $this->eventManager->dispatch(
            'event_single_quotes_multiple_lines',
            []
        );
    }

    public function dispatchDoubleQuotesMultipleLine()
    {
        $this->eventManager->dispatch(
            "event_double_quotes_multiple_lines",
            []
        );
    }

    public function dispatchSingleDynamicEvent()
    {
        $value = 'test';
        $this->eventManager->dispatch('event_single_quotes_dynamic_' . $value, []);
    }

    public function dispatchSingleDynamicEventMultipleLine()
    {
        $value = 'test';
        $this->eventManager->dispatch(
            'event_single_quotes_dynamic_multiple_lines_' . $value,
            []
        );
    }
}
