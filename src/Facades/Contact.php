<?php

namespace ThomasVantuycom\StatamicBrevo\Facades;

use Illuminate\Support\Facades\Facade;
use ThomasVantuycom\StatamicBrevo\Contacts\Contacts;

class Contact extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contacts::class;
    }
}
