<?php

/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class Adjustware_Cartalert_Model_Observer
{
    public function aitShowNotification($observer)
    {
        if (!Mage::registry("aitnotification")) {
            $statusModuleHash = $observer->getEvent()->getStatusHash();
            foreach ($statusModuleHash as $key => $row) {
                if ($key == "AdjustWare_Cartalert" && $row == false) {
                    if (Mage::helper('core')->isModuleEnabled('Aitoc_Common')) {
                        Mage::getSingleton('aitoc_common/cron')->validateCronDate()->showError();
                    }
                }
            }
            Mage::register("aitnotification", true);
        }
    }

    public function aitNotificationStatistic()
    {
        if (Mage::getStoreConfig("catalog/adjcartalert/notification_enabled")) {
            $countCartsRecovered = 0;
            $collection          = Mage::getResourceModel('adjcartalert/dailystat_collection')
                ->addFieldToFilter("date", array('from' => date('Y-m-01')));
            foreach ($collection as $row) {
                $countCartsRecovered += $row->getRecoveredCartsNum();
            }
            if ($countCartsRecovered >= 10) {
                $countCarts = 0;
                $sumCarts   = 0;
                foreach ($collection as $row) {
                    $countCarts += $row->getRecoveredCartsNum();
                    $sumCarts += $row->getOrderedCartsPrice();
                }
                $msg = Mage::helper('core')->__(
                    "This month: <b>%s</b> abandoned carts restored <b>%s</b> in revenue (<a href='%s'>see stats</a>)",
                    $countCarts,
                    Mage::app()
                        ->getLocale()
                        ->currency((string)Mage::app()->getStore()->getBaseCurrencyCode())
                        ->toCurrency($sumCarts),
                    Mage::helper("adminhtml")->getUrl("adminhtml/cartalert_dailystat")
                );
                Mage::getSingleton("core/session")->addSuccess($msg);
            }
            $collection          = Mage::getResourceModel('adjcartalert/dailystat_collection')
                ->addFieldToFilter("date", array('to' => date('Y-m-d'),));
            $countCartsRecovered = 0;
            foreach ($collection as $row) {
                $countCartsRecovered += $row->getRecoveredCartsNum();
            }
            if ($countCartsRecovered >= 10) {
                $sumCarts   = 0;
                $countCarts = 0;
                foreach ($collection as $row) {
                    $countCarts += $row->getRecoveredCartsNum();
                    $sumCarts += $row->getOrderedCartsPrice();
                }
                $msg = Mage::helper('core')->__(
                    "All time: <b>%s</b> abandoned carts restored <b>%s</b> in revenue (<a href='%s'>see stats</a>)",
                    $countCarts,
                    Mage::app()
                        ->getLocale()
                        ->currency((string)Mage::app()->getStore()->getBaseCurrencyCode())
                        ->toCurrency($sumCarts),
                    Mage::helper("adminhtml")->getUrl("adminhtml/cartalert_dailystat")
                );
                Mage::getSingleton("core/session")->addSuccess($msg);
            }
        }
    }

    public function createCartalerts()
    {

        $code = 'cron_create_cart_alerts';

        if (Mage::app()->loadCache($code)) {
            return;
        }

        Mage::app()->saveCache(1, $code, array(), 10);

        $curDate = date('Y-m-d H:i:s');
        $this->_createCartalerts(AdjustWare_Cartalert_Model_Cartalert_Cart::CARTALERT_INSTANCE_TYPE, $curDate);
        if (Mage::helper('adjcartalert')->isAbandonedOrdersEnabled()) {
            $this->_createCartalerts(AdjustWare_Cartalert_Model_Cartalert_Order::CARTALERT_INSTANCE_TYPE, $curDate);
        }
        $this->sendCartalerts();

        Mage::app()->removeCache($code);

        return $this;
    }

    /**
     * @param string $instance
     * @param string $curDate
     */
    protected function _createCartalerts($instance, $curDate)
    {
        $cartalertInstance = Mage::getModel('adjcartalert/cartalert_' . $instance);
        $cartalertInstance->generate($curDate);
    }

    public function runStat()
    {
        Mage::getModel('adjcartalert/cronstat')->cron();

        return $this;
    }

    public function sendCartalerts()
    {
        if (!Mage::getStoreConfig('catalog/adjcartalert/sending_enabled')) {
            return $this;
        }
        $collection = Mage::getModel('adjcartalert/cartalert')->getCollection()
            ->addReadyForSendingFilter()
            ->setPageSize(50)
            ->setCurPage(1)
            ->load();
        foreach ($collection as $cartalert) {
            if ($cartalert->send()) {
                $cartalert->delete();
            }
        }

        return $this;
    }

    public function processOrderCreated($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')) {
            $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                ->cancelAlertsFor($order->getCustomerEmail());
        }

        return $this;

    }

    public function updateAlertsStatus($observer)
    {
        if (!Mage::registry('alerts_status_updated')) {
            Mage::register('alerts_status_updated', true);

            $quote = Mage::getSingleton('checkout/session')->getQuote();

            if ($quote) {
                $quote->setAllowAlerts(1);

                if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')) {
                    $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                        ->cancelAlertsFor($quote->getCustomerEmail());
                }
            }
        }

        return $this;
    }
}
