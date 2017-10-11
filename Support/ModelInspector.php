<?php
namespace Gracious\Interconnect\Support;

/**
 * Class OrderInspector
 * @package Gracious\Interconnect\Support
 * Provides insoection/reflection on a model for cases where the model doesn't provide methods
 */
class ModelInspector
{
    /**
     * @var
     */
    protected $model;

    /**
     * OrderInspector constructor.
     * @param  $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return bool
     * Hack for determining whether this order is new or not until we can find a better way to do this. Order::isNewObject only works before save and we need to know it after the
     * order and order lines are saved
     */
    public function isNew() {
        return strtotime($this->model->getCreatedAt()) - strtotime($this->model->getUpdatedAt()) === 0;
    }
}