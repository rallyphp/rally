<?php
namespace Rally;

class Outcome
{
    protected $winner;
    protected $loser;
    protected $winnerScore;
    protected $loserScore;

    public function __construct(Player $winner, Player $loser, $winnerScore, $loserScore)
    {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->winnerScore = $winnerScore;
        $this->loserScore = $loserScore;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    public function getLoser()
    {
        return $this->loser;
    }

    public function getWinnerScore()
    {
        return $this->winnerScore;
    }

    public function getLoserScore()
    {
        return $this->loserScore;
    }
}
