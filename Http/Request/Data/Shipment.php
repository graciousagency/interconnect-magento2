<?php

namespace Gracious\Interconnect\Http\Request\Data;

class Shipment extends Data
{
    public function setupData(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        $totalItemsInOrder = count($shipment->getOrder()->getItems());
        $totalItemsInShipment = count($shipment->getShipment()->getItems());

        $data = [
            'storeId' => $shipment->getStoreId(),
            'orderId' => $shipment->getShipment()->getData('order_id'),
            'emailAddress' => $shipment->getShipment()->getOrder()->getData('customer_email'),
        ];

        if ($totalItemsInOrder === $totalItemsInShipment) {
            $data['status'] = 'shipped';
            return $data;
        }

        if (0 === $totalItemsInShipment) {
            $data['status'] = 'not_shipped';
            return $data;
        }

        if ($totalItemsInOrder > $totalItemsInShipment && $totalItemsInShipment > 0) {
            $data['status'] = 'partially_shipped';
            return $data;
        }
    }
}
