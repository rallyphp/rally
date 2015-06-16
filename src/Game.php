<?php
namespace Rally;

use UnexpectedValueException;

class Game
{
    use EventEmitter;

    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DEUCE = 2;
    const STATUS_COMPLETE = 3;

    const PLAYER_1 = 0;
    const PLAYER_2 = 1;

    /**
     * The number of points to be played before alternating service before
     * deuce.
     *
     * @var int
     * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.13.03
     */
    const POINTS_TO_ALT_SERVE_BEFORE_DEUCE = 2;

    /**
     * The number of points to be played before alternating service at deuce.
     *
     * @var int
     * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.13.03
     */
    const POINTS_TO_ALT_SERVE_AT_DEUCE = 1;

    /**
     * The number of points needed to win a game before deuce.
     *
     * @var int
     * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.11.01
     */
    const POINTS_TO_WIN_BEFORE_DEUCE = 11;

    /**
     * The number of points more than the opponent's score needed to win at
     * deuce.
     *
     * @var int
     * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.11.01
     */
    const POINTS_TO_WIN_BY_AT_DEUCE = 2;

    /**
     * The number of points needed by both players to initiate deuce.
     *
     * @var int
     * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.11.01
     */
    const POINTS_FOR_DEUCE = 10;

    protected $status = self::STATUS_NOT_STARTED;
    protected $p1;
    protected $p2;
    protected $p1Score = 0;
    protected $p2Score = 0;
    protected $outcome;
    protected $server;
    protected $isGamePoint = false;

    public function __construct(Player $p1, Player $p2, Player $servesFirst)
    {
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->server = $servesFirst;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS
            || $this->status === self::STATUS_DEUCE;
    }

    public function isDeuce()
    {
        return $this->status === self::STATUS_DEUCE;
    }

    public function isTied()
    {
        return $this->p1Score === $this->p2Score;
    }

    public function isGamePoint()
    {
        return $this->isGamePoint;
    }

    public function isComplete()
    {
        return $this->status === self::STATUS_COMPLETE;
    }

    public function getPlayer($playerIndex)
    {
        return $playerIndex === self::PLAYER_1
            ? $this->p1
            : $this->p2;
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

    public function setPlayerScore($playerIndex, $score)
    {
        if ($playerIndex === self::PLAYER_1) {
            $this->p1Score = $score;
        } else {
            $this->p2Score = $score;
        }
    }

    public function getLeader()
    {
        if ($this->isTied()) {
            return null;
        }

        return $this->p1Score > $this->p2Score ? $this->p1 : $this->p2;
    }

    public function getOutcome()
    {
        if (!$this->isComplete()) {
            throw new \Exception();
        }

        return $this->outcome;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function start()
    {
        if ($this->status !== self::STATUS_NOT_STARTED) {
            throw new \Exception();
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->emit('start');
    }

    public function scorePointForPlayer1()
    {
        $this->scorePoint(self::PLAYER_1);
    }

    public function scorePointForPlayer2()
    {
        $this->scorePoint(self::PLAYER_2);
    }

    public function scorePoint($playerIndex)
    {
        if (!$this->isInProgress()) {
            throw new \Exception();
        }

        $player = $this->getPlayer($playerIndex);
        $opponentIndex = $this->getOpponentIndex($playerIndex);
        $opponent = $this->getPlayer($opponentIndex);

        $this->setPlayerScore($playerIndex, $this->getPlayerScore($playerIndex) + 1);
        $this->emit('point', $player, $this->p1Score, $this->p2Score);

        $playerScore = $this->getPlayerScore($playerIndex);
        $opponentScore = $this->getPlayerScore($opponentIndex);

        switch ($this->status) {
            case self::STATUS_IN_PROGRESS:
                if ($playerScore === self::POINTS_TO_WIN_BEFORE_DEUCE) {
                    $this->outcome = new Outcome(
                        $player,
                        $opponent,
                        $playerScore,
                        $opponentScore
                    );

                    $this->status = self::STATUS_COMPLETE;
                    $this->emit('complete', $this->outcome);

                    break;
                }

                if ($playerScore === self::POINTS_FOR_DEUCE
                    && $opponentScore === self::POINTS_FOR_DEUCE
                ) {
                    $this->status = self::STATUS_DEUCE;
                    $this->emit('deuce');
                }

                if ($this->isGamePoint()) {
                    if ($this->isTied()) {
                        $this->isGamePoint = false;
                        $this->emit('game-point-end');
                    }
                } else {
                    if ($playerScore === self::POINTS_TO_WIN_BEFORE_DEUCE - 1) {
                        $this->isGamePoint = true;
                        $this->emit('game-point', $player);
                    }
                }

                /**
                 * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.13.03
                 */
                if ($this->getTotalPoints() % self::POINTS_TO_ALT_SERVE_BEFORE_DEUCE === 0) {
                    $this->switchServer();
                }

                break;

            case self::STATUS_DEUCE:
                if ($playerScore - $opponentScore === self::POINTS_TO_WIN_BY_AT_DEUCE) {
                    $this->outcome = new Outcome(
                        $player,
                        $opponent,
                        $playerScore,
                        $opponentScore
                    );

                    $this->status = self::STATUS_COMPLETE;
                    $this->emit('complete', $this->outcome);

                    break;
                }

                if ($this->isGamePoint()) {
                    $this->isGamePoint = false;
                    $this->emit('game-point-end');
                } else {
                    if ($playerScore - $opponentScore === self::POINTS_TO_WIN_BY_AT_DEUCE - 1) {
                        $this->isGamePoint = true;
                        $this->emit('game-point', $player);
                    }
                }

                /**
                 * @link http://www.ittf.com/ittf_handbook/hb.asp?s_number=2.13.03
                 */
                if ($this->getTotalPoints() % self::POINTS_TO_ALT_SERVE_AT_DEUCE === 0) {
                    $this->switchServer();
                }

                break;

            default:
                throw new UnexpectedValueException(sprintf(
                    'Unrecognized game status: %d',
                    $this->status
                ));
        }
    }

    protected function getOpponentIndex($playerIndex)
    {
        return $playerIndex === self::PLAYER_1 ? self::PLAYER_2 : self::PLAYER_1;
    }

    protected function getTotalPoints()
    {
        return $this->p1Score + $this->p2Score;
    }

    protected function switchServer()
    {
        $this->server = $this->server === $this->p1 ? $this->p2 : $this->p1;
        $this->emit('server-change', $this->server);
    }
}
