<?php

namespace Imhonet\Connection\Resource\PDO;

class MySQL extends PDO
{
    protected function getEngine()
    {
        return 'mysql';
    }

    protected function getAttributes()
    {
        return array(
//            \PDO::ATTR_EMULATE_PREPARES => false,
        ) + parent::getAttributes();
    }
}
