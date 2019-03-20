<?php

namespace Stripeofficial\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Stripeofficial\Core\Model\DataProvider;

class SalesOrderInvoicePay implements ObserverInterface
{
    /** @var DataProvider */
    private $dataProvider;

    /**
     * SalesOrderInvoicePay constructor.
     * @param DataProvider $dataProvider
     */
    public function __construct(
        DataProvider $dataProvider,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->dataProvider = $dataProvider;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getData('request');
        $order = $this->orderRepository->get($request->getParam('order_id'));
        $this->dataProvider->setCurrentStoreId($order->getStoreId());
    }
}
