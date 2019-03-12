<?php

namespace Stripeofficial\Core\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Stripeofficial\Core\Model\Cron\Webhook;
use Stripeofficial\Core\Model\Logger;
use Stripeofficial\Core\Model\ChargeFactory;
use Stripeofficial\Core\Model\WebhookQueueItem;
use Stripeofficial\Core\Model\WebhookQueueItemFactory;
use Stripeofficial\Core\Model\ResourceModel\WebhookQueueItem as WebhookResource;
use Stripe\Stripe;

class Index extends Action
{
    /**
     * @var WebhookQueueItemFactory
     */
    private $webhookQueueItemFactory;

    /**
     * @var WebhookResource
     */
    private $webhookResource;

    /**
     * @var Logger
     */
    private $logger;

    /** @var Webhook */
    private $webhook;

    /**
     * Index constructor.
     * @param Context $context
     * @param Logger $logger
     * @param WebhookQueueItemFactory $webhookQueueItemFactory
     * @param WebhookResource $webhookResource
     */
    public function __construct(
        Context $context,
        Logger $logger,
        WebhookQueueItemFactory $webhookQueueItemFactory,
        WebhookResource $webhookResource,
        Webhook $webhook
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->webhook = $webhook;
        $this->webhookQueueItemFactory = $webhookQueueItemFactory;
        $this->webhookResource = $webhookResource;

        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof Http && $request->isPost() && empty($request->getParam('form_key'))) {
                $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
                $request->setParam('form_key', $formKey->getFormKey());
            }
        }
    }

    /**
     * @return ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        //@codingStandardsIgnoreStart
        try {
            $input = file_get_contents("php://input");
        } catch (\Exception $e) {
            $this->logger->error(__('No input found'));
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $resultPage->setHttpResponseCode(200);
            $resultPage->setContents('Done!');
            return $resultPage;
        }
        $eventJson = json_decode($input, true);
        $this->logger->info("Webhook is called " .  $eventJson['type']);
        //@codingStandardsIgnoreStart

        if (in_array(
            $eventJson['type'],
            [
                'charge.captured',
                'charge.refunded',
                'source.chargeable',
                'charge.succeeded',
                'source.failed',
                'source.canceled',
                'charge.failed',
            ]
        )) {
            try {
                $this->webhook->proceedItem($eventJson);
            } catch (\Exception $e) {
                /** @var WebhookQueueItem $webhook */
                $webhook = $this->webhookQueueItemFactory->create();
                $webhook->setData('event_data', json_encode($eventJson));
                $this->webhookResource->save($webhook);

                $this->logger->debug("Webhook failed, id is " .  $webhook->getId());
            }
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultPage->setHttpResponseCode(200);
        $resultPage->setContents('Done!');
        return $resultPage;
    }
}
