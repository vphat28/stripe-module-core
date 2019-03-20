<?php

namespace Stripeofficial\Core\Model;

class DataProvider
{
    private $currentStoreId = null;

    public function getCurrentStoreId()
    {
        return $this->currentStoreId;
    }

    public function setCurrentStoreId($currentStoreId)
    {
        $this->currentStoreId = $currentStoreId;
    }
}