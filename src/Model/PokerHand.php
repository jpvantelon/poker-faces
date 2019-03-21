<?php

namespace App\Model;

use App\Exception\HandException;
use App\Service\PokerHandsScoreCalculator;

class PokerHand
{
    /** @var Card[] */
    protected $cards = [];
    /** @var int[] */
    protected $cardsValues = [];
    /** @var string[] */
    protected $cardsSuits = [];

    /**
     * @param Card[] $cards
     */
    public function __construct(array $cards)
    {
        $this->checkInputCards($cards);

        $this->cards = $cards;

        $this->initializeValuesAndSuits();
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @return string
     */
    public function getCardsAsString(): string
    {
        return implode(' ', $this->getCards());
    }

    /**
     * @return int[]
     */
    public function getCardsValues(): array
    {
        return $this->cardsValues;
    }

    /**
     * @return string[]
     */
    public function getCardsSuits(): array
    {
        return $this->cardsSuits;
    }

    /**
     * @return ScoreHand
     */
    public function getBestHand(): ScoreHand
    {
        return PokerHandsScoreCalculator::getBestHandWithKicker($this);
    }

    /**
     * @return string
     */
    public function getBestHandAsString(): string
    {
        return (string)PokerHandsScoreCalculator::getBestHandWithKicker($this);
    }

    /**
     * @return array
     */
    public function getScore(): array
    {
        return PokerHandsScoreCalculator::getFullScore($this);
    }

    /**
     * @param PokerHand $hand
     * @return int
     */
    public function compareWith(PokerHand $hand): int
    {
        if ($this->hasSameCard($hand)) {
            throw new HandException('The two hands have at least one same card');
        }

        return $this->getBestHand()->compareWith($hand->getBestHand());
    }

    /**
     * @param PokerHand $hand
     * @return bool
     */
    public function hasSameCard(PokerHand $hand): bool
    {
        foreach ($this->getCards() as $card1) {
            foreach ($hand->getCards() as $card2) {
                if ($card1 === $card2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Poker Hand factory
     *
     * @param string $handDefinition
     * @return PokerHand
     */
    public static function createFromString(string $handDefinition): self
    {
        /** @var string $handDefinition */
        $handDefinition = preg_replace('/\s+/', ' ', $handDefinition);
        /** @var string $handDefinition */
        $handDefinition = preg_filter('/[a-zA-Z0-9 ]+/', '$0', $handDefinition);

        if (strlen($handDefinition) !== 14) {
            throw new HandException('Poker Hand definition is invalid: not 14 characters');
        }
        $cardsInput = explode(' ', $handDefinition);

        $cards = [];
        foreach ($cardsInput as $cardInput) {
            $cards[] = Card::createFromString($cardInput);
        }


        return new self($cards);
    }

    /**
     * @param array $cards
     */
    protected function checkInputCards(array $cards): void
    {
        if (count($cards) !== 5) {
            throw new HandException('Poker Hand definition is invalid: not 5 cards');
        }

        $cardsAsString = [];
        foreach ($cards as $card) {
            $cardAsString = (string)$card;
            if (in_array($cardAsString, $cardsAsString, true)) {
                throw new HandException('Poker Hand definition is invalid: same card more than one time');
            }
            $cardsAsString[] = $cardAsString;
        }
    }

    protected function initializeValuesAndSuits(): void
    {
        foreach ($this->cards as $card) {
            $this->cardsValues[] = $card->getValue();
            $this->cardsSuits[] = $card->getSuit();
        }
    }
}