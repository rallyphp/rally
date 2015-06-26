<?php
namespace Rally;

use PDO;

class Database extends PDO
{
    public function query($query, array $params = [])
    {
        $st = $this->prepare($query);
        $st->execute($params);

        return $st;
    }
}
