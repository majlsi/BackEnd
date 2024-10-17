<?php

namespace Models;


class Subscription {
    public $id;
    public $customer; 
    public $price;
    public $plan;
    public $status;
    public $start;
    public $canceled_at;
    public $cancel_reason;
    public $created;
    public $extra_fields;
    public $base_subscription;
    public $override_price;
    public $quantifiable_items_prices;
    public $items_price;
    public $end_date;
}