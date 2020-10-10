<?php

namespace Signifyd\Fixinvoiceflow\Plugin\Model;

use Signifyd\Connect\Model\Casedata as SignifydCasedata;
use Signifyd\Connect\Helper\OrderHelper;
use Signifyd\Connect\Logger\Logger;

class Casedata
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Casedata constructor.
     * @param OrderHelper $orderHelper
     * @param Logger $logger
     */
    public function __construct(OrderHelper $orderHelper, Logger $logger)
    {
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
    }

    /**
     * @param SignifydCasedata $subject
     * @param $caseData
     * @param $orderAction
     * @param $case
     * @return array
     */
    public function beforeUpdateOrder(SignifydCasedata $subject, $caseData, $orderAction, $case)
    {
        $this->logger->info("Fixinvoiceflow plugin, order action: " . json_encode($orderAction));

        if ($orderAction["action"] == 'capture') {
            /** @var $order \Magento\Sales\Model\Order */
            $order = $caseData['order'];
            $order->unhold();

            if ($order->canInvoice() === false) {
                $reason = $this->orderHelper->getCannotInvoiceReason($order);

                $this->logger->info("Fixinvoiceflow plugin, can't invoice reason: " . $reason);

                if ($reason == "no items can be invoiced") {
                    $orderAction["action"] = 'nothing';
                }
            }
        }

        return [$caseData, $orderAction, $case];
    }
}