<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftWrappingGraphQl\Model\Resolver\Cart\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftWrapping\Api\WrappingRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class gets data about gift wrapping for cart
 */
class GiftWrapping implements ResolverInterface
{
    /**
     * @var WrappingRepositoryInterface
     */
    private $wrappingRepository;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param WrappingRepositoryInterface $giftWrappingRepository
     * @param Uid                         $uidEncoder
     */
    public function __construct(
        WrappingRepositoryInterface $giftWrappingRepository,
        Uid $uidEncoder
    ) {
        $this->wrappingRepository = $giftWrappingRepository;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Get gift wrapping data for cart
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed|null
     *
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof CartItemInterface)) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $giftWrappingId = $cart->getGwId();

        if (empty($giftWrappingId)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        try {
            $cartGiftWrapping = $this->wrappingRepository->get((int)$giftWrappingId, (int)$store->getStoreId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load gift wrapping for item.'));
        }

        return [
            'id' => $cartGiftWrapping->getWrappingId() ?? '',
            'uid' => $cartGiftWrapping->getWrappingId() ?
                $this->uidEncoder->encode((string) $cartGiftWrapping->getWrappingId()) : '',
            'design' => $cartGiftWrapping->getDesign() ?? '',
            'price' => [
                'value' => $cartGiftWrapping->getBasePrice() ?? '',
                'currency' => $store->getCurrentCurrencyCode()
            ],
            'image' => [
                'label'=> $cartGiftWrapping->getImageName() ?? '',
                'url'=> $cartGiftWrapping->getImageUrl() ?? ''
            ]
        ];
    }
}
