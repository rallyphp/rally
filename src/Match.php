<?php
namespace Rally;

use InvalidArgumentException;

class Match
{
    use EventEmitter;

    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETE = 2;

    const PLAYER_1 = 0;
    const PLAYER_2 = 1;

    protected $status = self::STATUS_NOT_STARTED;
    protected $p1;
    protected $p2;
    protected $games = [];
    protected $curGame = -1;
    protected $p1Score = 0;
    protected $p2Score = 0;
    protected $coinTossWinner;
    protected $isMatchPoint = false;

    public function __construct(Player $p1, Player $p2, $numGames = 1)
    {
        /**
         * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.12.01
         */
        if ($numGames % 2 === 0) {
            throw new InvalidArgumentException('Number of games must be odd');
        }

        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->numGames = $numGames;

        $coinTossWinnerIndex = mt_rand(0, 1) === 0
            ? self::PLAYER_1
            : self::PLAYER_2;
        $this->coinTossWinner = $this->getPlayer($coinTossWinnerIndex);

        for ($i = 0; $i < $numGames; $i++) {
            /**
             * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.13.06
             */
            $servesFirst = $i % 2 === 0
                ? $this->coinTossWinner
                : $this->getPlayer($this->getOpponentIndex($coinTossWinnerIndex));

            $game = new Game($p1, $p2, $servesFirst);

            $game->on('start', function () use ($game) {
                $this->emit('game-start', $game);
            });

            $game->on('point', function (...$args) {
                $this->emit('point', ...$args);
            });

            $game->on('server-change', function (...$args) {
                $this->emit('server-change', ...$args);
            });

            $game->on('game-point', function (Player $leader) {
                /**
                 * @todo Handle this better.
                 */
                $leaderIndex = $leader === $this->p1 ? self::PLAYER_1 : self::PLAYER_2;

                if ($this->getPlayerScore($leaderIndex) == ceil($this->numGames / 2) - 1) {
                    $this->isMatchPoint = true;
                    $this->emit('match-point', $leader);
                } else {
                    $this->emit('game-point', $leader);
                }
            });

            $game->on('game-point-end', function () {
                if ($this->isMatchPoint) {
                    $this->isMatchPoint = false;
                    $this->emit('match-point-end');
                } else {
                    $this->emit('game-point-end');
                }
            });

            $game->on('deuce', function () {
                $this->emit('deuce');
            });

            $game->on('complete', function (Outcome $outcome) {
                if ($outcome->getWinner() === $this->p1) {
                    $this->scoreGameForPlayer1();
                } else {
                    $this->scoreGameForPlayer2();
                }
            });

            $this->addGame($game);
        }
    }

    public function isTied()
    {
        return $this->p1Score === $this->p2Score;
    }

    public function isMatchPoint()
    {
        return $this->isMatchPoint;
    }

    public function getPlayer($playerIndex)
    {
        return $playerIndex === self::PLAYER_1 ? $this->p1 : $this->p2;
    }

    public function getPlayer1()
    {
        return $this->getPlayer(self::PLAYER_1);
    }

    public function getPlayer2()
    {
        return $this->getPlayer(self::PLAYER_2);
    }

    public function getPlayerScore($playerIndex)
    {
        return $playerIndex === self::PLAYER_1
            ? $this->p1Score
            : $this->p2Score;
    }

    public function getPlayer1Score()
    {
        return $this->getPlayerScore(self::PLAYER_1);
    }

    public function getPlayer2Score()
    {
        return $this->getPlayerScore(self::PLAYER_2);
    }

    public function addGame(Game $game)
    {
        $this->games[] = $game;
    }

    public function start()
    {
        if ($this->status !== self::STATUS_NOT_STARTED) {
            throw new \Exception();
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->emit('start');
        $this->emit('coin-toss', $this->coinTossWinner);
    }

    public function startNextGame()
    {
        $this->games[++$this->curGame]->start();
    }

    public function isComplete()
    {
        return $this->status === self::STATUS_COMPLETE;
    }

    public function getCurrentGame()
    {
        return $this->games[$this->curGame];
    }

    public function scoreGameForPlayer1()
    {
        $this->p1Score++;
        $this->emit('game-complete', $this->getCurrentGame()->getOutcome());

        /**
         * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.12.01
         */
        if ($this->p1Score == ceil($this->numGames / 2)) {
            $outcome = new Outcome(
                $this->p1,
                $this->p2,
                $this->p1Score,
                $this->p2Score
            );

            $this->status = self::STATUS_COMPLETE;
            $this->emit('complete', $outcome);
        }
    }

    public function scoreGameForPlayer2()
    {
        $this->p2Score++;
        $this->emit('game-complete', $this->getCurrentGame()->getOutcome());

        /**
         * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.12.01
         */
        if ($this->p2Score == ceil($this->numGames / 2)) {
            $outcome = new Outcome(
                $this->p2,
                $this->p1,
                $this->p2Score,
                $this->p1Score
            );

            $this->status = self::STATUS_COMPLETE;
            $this->emit('complete', $outcome);
        }
    }

    protected function getOpponentIndex($playerIndex)
    {
        return $playerIndex === self::PLAYER_1 ? self::PLAYER_2 : self::PLAYER_1;
    }
}
