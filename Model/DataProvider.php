<?php

namespace Stripeofficial\Core\Model;

class DataProvider
{
    private $currentStoreId = null;

    private $stripeContext = '';

    public function getCurrentStoreId()
    {
        return $this->currentStoreId;
    }

    public function setCurrentStoreId($currentStoreId)
    {
        $this->currentStoreId = $currentStoreId;
    }

    public function setStripeContext($name)
    {
        $this->stripeContext = $name;
    }

    public function getStripeContext()
    {
        return $this->stripeContext;
    }
}