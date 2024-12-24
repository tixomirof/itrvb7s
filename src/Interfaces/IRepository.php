<?php

namespace ITRvB\Interfaces;

use ITRvB\Models\UUID;
use ITRvB\Repositories\Connection\MySQL;

interface IRepository
{
    public function get(UUID $uuid, MySQL $openConnection = null);
    public function save($model) : void;
}