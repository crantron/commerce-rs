<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftCardAccount\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftCardAccount\Model\EmailManagement;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GiftcardaccountTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Giftcardaccount
     */
    private $model;

    /**
     * @var EmailManagement|MockObject
     */
    private $emailManagement;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * Initialize testable object
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->emailManagement = $this->getMockBuilder(EmailManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->createMock(TimezoneInterface::class);
        $this->model = $this->objectManager->getObject(
            Giftcardaccount::class,
            [
                'emailManagement' => $this->emailManagement,
                'localeDate' => $this->localeDate
            ]
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testBeforeSaveBalanceException(): void
    {
        $this->localeDate->expects($this->once())->method('getConfigTimezone')->willReturn('Europe/Bucharest');
        $this->model->setData(['id' => 1]);
        $this->model->addData(
            [
                'giftcardaccount_id' => 1,
                'status' => 1,
                'is_redeemable' => 1,
                'website_id' => 1,
                'balance' => 1 . '<script type="text/x-magento-init">alert(1)</script>',
                'recipient_store' => 1,
                'send_action' => 0,
                'limit' => 20,
                'page' => 1
            ]
        );
        $this->expectException(LocalizedException::class);
        $this->model->beforeSave();
    }

    /**
     * @dataProvider sendEmailDataProvider
     * @param bool $sendEmail
     */
    public function testSendEmail($sendEmail)
    {
        $this->emailManagement->expects($this->atLeastOnce())->method('sendEmail')->with($this->model)
            ->willReturn($sendEmail);
        $this->model->sendEmail();
        $this->assertEquals($sendEmail, $this->model->getEmailSent());
    }

    /**
     * @return array
     */
    public function sendEmailDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
