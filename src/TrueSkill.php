<?php
namespace Rally;

class TrueSkill
{
    protected $performanceVariance;
    protected $drawProbability;

    public function __construct($drawProbability = 0)
    {
        $initRating = 25;
        $initUncertainty = $initRating / 3;

        $this->performanceVariance = $initUncertainty / 2;
        $this->drawProbability = $drawProbability;
    }

    public function updateRatings(Player $winner, Player $loser)
    {
        $winnerRating = $winner->getRating();
        $winnerUncertainty = $winner->getUncertainty();
        $winnerUncertaintySquared = pow($winnerUncertainty, 2);

        $loserRating = $loser->getRating();
        $loserUncertainty = $loser->getUncertainty();
        $loserUncertaintySquared = pow($loserUncertainty, 2);

        $n1 = 1; // # of players in team 1
        $n2 = 1; // # of players in team 2
        $drawMargin = self::inverseCumulativeTo(($this->drawProbability + 1) * 0.5) * sqrt($n1 + $n2) * $this->performanceVariance;

        $cSquared = 2 * pow($this->performanceVariance, 2) + $winnerUncertaintySquared + $loserUncertaintySquared;
        $c = sqrt($cSquared);

        $ratingDiff = $winnerRating - $loserRating;
        $scaledRatingDiff = $ratingDiff / $c;
        $scaledDrawMargin = $drawMargin / $c;

        $winnerNewRating = $winnerRating + $winnerUncertaintySquared / $c * self::v($scaledRatingDiff, $scaledDrawMargin);
        $loserNewRating = $loserRating - $loserUncertaintySquared / $c * self::v($scaledRatingDiff, $scaledDrawMargin);

        $winnerNewUncertainty = sqrt($winnerUncertaintySquared * (1 - $winnerUncertaintySquared / $cSquared * self::w($scaledRatingDiff, $scaledDrawMargin)));
        $loserNewUncertainty = sqrt($loserUncertaintySquared * (1 - $loserUncertaintySquared / $cSquared * self::w($scaledRatingDiff, $scaledDrawMargin)));

        $winner->setRating($winnerNewRating);
        $winner->setUncertainty($winnerNewUncertainty);

        $loser->setRating($loserNewRating);
        $loser->setUncertainty($loserNewUncertainty);
    }

    public function getSkillEstimate(Player $player)
    {
        return $player->getRating() - 3 * $player->getUncertainty();
    }

    protected static function v($t, $a)
    {
        $denom = self::cumulativeTo($t - $a);

        if ($denom < 2.222758749e-162) {
            return -$t + $a;
        }

        return self::at($t - $a) / $denom;
    }

    protected static function w($t, $a)
    {
        $denom = self::cumulativeTo($t - $a);

        if ($denom < 2.222758749e-162) {
            if ($t < 0.0) {
                return 1.0;
            }

            return 0.0;
        }

        $vWin = self::v($t, $a);

        return $vWin * ($vWin + $t - $a);
    }

    public static function at($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        // See http://mathworld.wolfram.com/NormalDistribution.html
        //                1              -(x-mean)^2 / (2*stdDev^2)
        // P(x) = ------------------- * e
        //        stdDev * sqrt(2*pi)
        $multiplier = 1.0 / ($standardDeviation * sqrt(2 * M_PI));
        $expPart = exp((-1.0 * pow($x - $mean, 2)) / (2 * pow($standardDeviation, 2)));
        $result = $multiplier * $expPart;

        return $result;
    }

    public static function cumulativeTo($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        return self::errorFunctionCumulativeTo(-0.707106781186547524400844362104 * $x) * 0.5;
    }

    private static function errorFunctionCumulativeTo($x)
    {
        // Derived from page 265 of Numerical Recipes 3rd Edition
        $z = abs($x);
        $t = 2.0/(2.0 + $z);
        $ty = 4*$t - 2;
        $coefficients = array(
                                -1.3026537197817094,
                                6.4196979235649026e-1,
                                1.9476473204185836e-2,
                                -9.561514786808631e-3,
                                -9.46595344482036e-4,
                                3.66839497852761e-4,
                                4.2523324806907e-5,
                                -2.0278578112534e-5,
                                -1.624290004647e-6,
                                1.303655835580e-6,
                                1.5626441722e-8,
                                -8.5238095915e-8,
                                6.529054439e-9,
                                5.059343495e-9,
                                -9.91364156e-10,
                                -2.27365122e-10,
                                9.6467911e-11,
                                2.394038e-12,
                                -6.886027e-12,
                                8.94487e-13,
                                3.13092e-13,
                                -1.12708e-13,
                                3.81e-16,
                                7.106e-15,
                                -1.523e-15,
                                -9.4e-17,
                                1.21e-16,
                                -2.8e-17 );
        $ncof = count($coefficients);
        $d = 0.0;
        $dd = 0.0;
        for ($j = $ncof - 1; $j > 0; $j--)
        {
            $tmp = $d;
            $d = $ty*$d - $dd + $coefficients[$j];
            $dd = $tmp;
        }
        $ans = $t*exp(-$z*$z + 0.5*($coefficients[0] + $ty*$d) - $dd);
        return ($x >= 0.0) ? $ans : (2.0 - $ans);
    }

    private static function inverseErrorFunctionCumulativeTo($p)
    {
        // From page 265 of numerical recipes
        if ($p >= 2.0)
        {
            return -100;
        }
        if ($p <= 0.0)
        {
            return 100;
        }
        $pp = ($p < 1.0) ? $p : 2 - $p;
        $t = sqrt(-2*log($pp/2.0)); // Initial guess
        $x = -0.70711*((2.30753 + $t*0.27061)/(1.0 + $t*(0.99229 + $t*0.04481)) - $t);
        for ($j = 0; $j < 2; $j++)
        {
            $err = self::errorFunctionCumulativeTo($x) - $pp;
            $x += $err/(1.12837916709551257*exp(-pow($x, 2)) - $x*$err); // Halley
        }
        return ($p < 1.0) ? $x : -$x;
    }
    public static function inverseCumulativeTo($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        // From numerical recipes, page 320
        return $mean - sqrt(2) * $standardDeviation * self::inverseErrorFunctionCumulativeTo(2 * $x);
    }
}
