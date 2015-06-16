<?php
namespace Rally;

class Tournament
{
    protected $participants = [];

    public function addEntry(Player $player, $seed = null)
    {
        $this->participants[] = $player;
    }

    public function start()
    {
        shuffle($this->participants);

        $n = 1;
        while ($n <= count($this->participants)) {
            $n *= 2;
        }
        $n /= 2;

        $matches = [];

        for ($i = 0; $i < $n; $i++) {
            $matches[] = new Match();
        }
    }
}
