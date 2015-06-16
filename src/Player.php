<?php
namespace Rally;

class Player
{
    protected $name;
    protected $rating;
    protected $uncertainty;

    public function __construct($name, $rating, $uncertainty)
    {
        $this->name = $name;
        $this->rating = $rating;
        $this->uncertainty = $uncertainty;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function getUncertainty()
    {
        return $this->uncertainty;
    }

    public function setUncertainty($uncertainty)
    {
        $this->uncertainty = $uncertainty;
    }
}
