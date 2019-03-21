<?php

namespace App\Model;


class ScoreHandType
{
    public const ROYAL_FLUSH = 'royalFlush';
    public const STRAIGHT_FLUSH = 'straightFlush';
    public const FOUR_OF_A_KIND = 'fourOfAKind';
    public const FULL_HOUSE = 'fullHouse';
    public const FLUSH = 'flush';
    public const STRAIGHT = 'straight';
    public const THREE_OF_A_KIND = 'threeOfAKind';
    public const DOUBLE_PAIRS = 'doublePairs';
    public const PAIRS = 'pairs';
    public const HIGH_CARDS = 'highCards';

    public const SCORE_HAND_TYPES = [
        self::HIGH_CARDS,
        self::PAIRS,
        self::DOUBLE_PAIRS,
        self::THREE_OF_A_KIND,
        self::STRAIGHT,
        self::FLUSH,
        self::FULL_HOUSE,
        self::FOUR_OF_A_KIND,
        self::STRAIGHT_FLUSH,
        self::ROYAL_FLUSH,
    ];

    protected $name;
    protected $weight;

    /**
     * ScoreHandType constructor.
     * @param $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->weight = array_search($name, self::SCORE_HAND_TYPES, true);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return ucfirst(preg_replace('/([A-Z])/', ' $1', $this->getName()));
    }
}