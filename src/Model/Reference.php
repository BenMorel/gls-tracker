<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Model;

class Reference
{
    /**
     * The reference type.
     *
     * Example: CUSTREF
     *
     * @var string
     */
    public $type;

    /**
     * The reference name.
     *
     * Example: Customer's own reference number
     *
     * @var string
     */
    public $name;

    /**
     * The reference value.
     *
     * Example: 123456
     *
     * @var string
     */
    public $value;
}
