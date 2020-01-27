<?php


namespace AppBundle\Model\Event;


class OrderEvents
{
    const ORDER_ITEM_DELETED_EVENT = 'order_item.deleted.success';
    const ORDER_ITEM_UPDATED_EVENT = 'order_item.updated.success';

    const CART_EXPIRED_EVENT = 'cart.expired';
}