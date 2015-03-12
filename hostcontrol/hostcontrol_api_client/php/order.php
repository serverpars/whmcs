<?php


class Order extends ResourceWrapper
{
    protected $paths = array(
        'collection' => '/order',
    );

    public function order_domain($customer, $type, $name, $tld, $interval, $registrant, $nameservers, $contacts)
    {

        return $this->apiclient->post($this->get_request_path('collection'), array(
            'customer' => intval($customer),
            'type' => 'new',
            'order' => array(
                array(
                    'type' => $type,
                    'name' => $name,
                    'tld' => $tld,
                    'interval' => intval($interval),
                    'registrant' => $registrant,
                    'nameservers' => $nameservers,
                    'contacts' => $contacts
                )
            )
        ));
    }
}
