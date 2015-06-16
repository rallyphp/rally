<?php
namespace Rally;

class Round
{
    protected $matches;

    public function __construct(array $matches)
    {
        $this->matches = $matches;
    }
}
