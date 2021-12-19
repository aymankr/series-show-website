<?php

namespace App\Search;

use App\Entity\Genre;
use App\Entity\Country;

class Search {

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $s = '';

    /**
     * @var Country[]
     */
    public $countries = [];


    /**
     * @var Genre[]
     */
    public $categories = [];

    /**
     * @var boolean
     */
    public $followed = false;

}