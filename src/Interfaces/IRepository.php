<?php

namespace ITRvB\Interfaces;

use ITRvB\Models\UUID;

interface IRepository
{
    public function get(UUID $uuid);
    public function save($model) : void;
    public function delete(UUID $uuid) : void;
}