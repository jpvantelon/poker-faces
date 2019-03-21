<?php

namespace App\Model;

class ScoreHand
{
    /** @var Card[] */
    protected $cards = [];
    /** @var int[] */
    protected $cardsValues = [];
    /** @var ScoreHandType */
    protected $scoreHandType;
    /** @var Card[]|null */
    protected $kickers;
    /** @var int[] */
    protected $kickerValues = [];

    /**
     * @param Card[] $cards
     * @param ScoreHandType $scoreHandType
     * @param Card[]|null $kickers
     */
    public function __construct(array $cards, ScoreHandType $scoreHandType, array $kickers = null)
    {
        $this->cards = $cards;
        $this->scoreHandType = $scoreHandType;
        $this->kickers = $kickers;

        $this->initializeValues();
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @return int[]
     */
    public function getCardsValues(): array
    {
        return $this->cardsValues;
    }

    public function getBestValue(): int
    {
        return max($this->getCardsValues());
    }

    /**
     * @return ScoreHandType
     */
    public function getScoreHandType(): ScoreHandType
    {
        return $this->scoreHandType;
    }

    /**
     * @return Card[]|null
     */
    public function getKickers(): ?array
    {
        return $this->kickers;
    }

    /**
     * @return Card[]|null
     */
    public function getKickersValuesSorted(): ?array
    {
        $kickerValuesSorted = $this->kickerValues;
        arsort($kickerValuesSorted, SORT_NUMERIC);

        return $kickerValuesSorted;
    }

    /**
     * @param ScoreHand $hand
     * @return int
     */
    public function compareWith(ScoreHand $hand): int
    {
        if ($this->getScoreHandType()->getWeight() === $hand->getScoreHandType()->getWeight()) {
            switch ($this->getScoreHandType()->getName()) {
                case ScoreHandType::ROYAL_FLUSH:
                    return 3;
                    break;
                case ScoreHandType::FULL_HOUSE:
                    // Check the three of a kind part first
                    $toak1 = new ScoreHand(
                        array_slice($this->getCards(), 0, 3),
                        new ScoreHandType(ScoreHandType::THREE_OF_A_KIND)
                    );
                    $toak2 = new ScoreHand(
                        array_slice($hand->getCards(), 0, 3),
                        new ScoreHandType(ScoreHandType::THREE_OF_A_KIND)
                    );
                    $toakcmp = $toak1->compareWith($toak2);
                    if ($toakcmp === 3) {
                        // Check the pair part next
                        $p1 = new ScoreHand(array_slice($this->getCards(), 3), new ScoreHandType(ScoreHandType::PAIRS));
                        $p2 = new ScoreHand(array_slice($hand->getCards(), 3), new ScoreHandType(ScoreHandType::PAIRS));
                        $pcmp = $p1->compareWith($p2);
                        if ($pcmp === 3) {
                            return $this->compareKickers($hand);
                        } else {
                            return $pcmp;
                        }
                    } else {
                        return $toakcmp;
                    }
                    break;
                case ScoreHandType::DOUBLE_PAIRS:
                    if ($this->getBestValue() === $hand->getBestValue()) {
                        // Compare second pair
                        $p1 = new ScoreHand(array_slice($this->getCards(), 2), new ScoreHandType(ScoreHandType::PAIRS));
                        $p2 = new ScoreHand(array_slice($hand->getCards(), 2), new ScoreHandType(ScoreHandType::PAIRS));
                        $pcmp = $p1->compareWith($p2);
                        if ($pcmp === 3) {
                            return $this->compareKickers($hand);
                        } else {
                            return $pcmp;
                        }
                    } elseif ($this->getBestValue() < $hand->getBestValue()) {
                        return 2;
                    } else {
                        return 1;
                    }
                default:
                    if ($this->getBestValue() === $hand->getBestValue()) {
                        return $this->compareKickers($hand);
                    } elseif ($this->getBestValue() < $hand->getBestValue()) {
                        return 2;
                    } else {
                        return 1;
                    }
                    break;
            }
        } elseif ($this->getScoreHandType()->getWeight() < $hand->getScoreHandType()->getWeight()) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $scoreType = (string)$this->getScoreHandType();
        $cards = implode(
            ' ',
            array_map(
                function ($e) {
                    return (string)$e;
                },
                $this->getCards()
            )
        );

        return sprintf('%s ("%s")', $scoreType, $cards);
    }

    protected function initializeValues(): void
    {
        foreach ($this->cards as $card) {
            $this->cardsValues[] = $card->getValue();
        }

        if ($this->kickers) {
            foreach ($this->kickers as $kicker) {
                $this->kickerValues[] = $kicker->getValue();
            }
        }
    }

    /**
     * @param ScoreHand $hand
     * @return int
     */
    protected function compareKickers(ScoreHand $hand): int
    {
        $kickers1 = array_values($this->getKickersValuesSorted());
        $kickers2 = array_values($hand->getKickersValuesSorted());

        $kickersCount = min(count($kickers1), count($kickers2));
        for ($i = 0; $i < $kickersCount; $i++) {
            $kickerValue1 = $kickers1[$i];
            $kickerValue2 = $kickers2[$i];
            if ($kickerValue1 < $kickerValue2) {
                return 2;
            } elseif ($kickerValue1 > $kickerValue2) {
                return 1;
            }
        }

        return 3;
    }
}