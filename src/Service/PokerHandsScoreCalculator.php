<?php

namespace App\Service;

use App\Model\Card;
use App\Model\PokerHand;
use App\Model\ScoreHand;
use App\Model\ScoreHandType;

class PokerHandsScoreCalculator
{
    /**
     * @param PokerHand $hand
     * @return ScoreHand|null
     */
    public static function getBestHandWithKicker(PokerHand $hand): ?ScoreHand
    {
        foreach (array_reverse(ScoreHandType::SCORE_HAND_TYPES) as $scoreType) {
            $function = 'get'.ucfirst($scoreType);
            $result = self::$function($hand);
            if (count($result)) {
                // For flush, every card is a kicker
                $kickers = $scoreType === ScoreHandType::FLUSH ? $hand->getCards() : self::getKickers($hand, $result);

                return new ScoreHand($result, new ScoreHandType($scoreType), $kickers);
            }
        }

        return null;
    }

    /**
     * @param PokerHand $hand
     * @return array
     */
    public static function getFullScore(PokerHand $hand): array
    {
        return [
            ScoreHandType::HIGH_CARDS => self::getHighCards($hand),
            ScoreHandType::PAIRS => self::getPairs($hand),
            ScoreHandType::DOUBLE_PAIRS => self::getDoublePairs($hand),
            ScoreHandType::THREE_OF_A_KIND => self::getThreeOfAKind($hand),
            ScoreHandType::STRAIGHT => self::getStraight($hand),
            ScoreHandType::FLUSH => self::getFlush($hand),
            ScoreHandType::FULL_HOUSE => self::getFullHouse($hand),
            ScoreHandType::FOUR_OF_A_KIND => self::getFourOfAKind($hand),
            ScoreHandType::STRAIGHT_FLUSH => self::getStraightFlush($hand),
            ScoreHandType::ROYAL_FLUSH => self::getRoyalFlush($hand),
        ];
    }

    /**
     * @param PokerHand $fullHand
     * @param Card[] $bestHandCards
     * @return Card[]
     */
    protected static function getKickers(PokerHand $fullHand, array $bestHandCards): array
    {
        return array_diff($fullHand->getCards(), $bestHandCards);
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getHighCards(PokerHand $hand): array
    {
        $highScore = max($hand->getCardsValues());
        $highCardsIndexes = array_keys($hand->getCardsValues(), $highScore);
        $highCards = [];
        foreach ($highCardsIndexes as $highCardIndex) {
            $highCards[] = $hand->getCards()[$highCardIndex];
        }

        return $highCards;
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getPairs(PokerHand $hand): array
    {
        return array_diff(
            self::getPairedCards($hand, 2),
            self::getDoublePairs($hand),
            self::getThreeOfAKind($hand),
            self::getFourOfAKind($hand)
        );
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getDoublePairs(PokerHand $hand): array
    {
        $pairs = array_diff(self::getPairedCards($hand, 2), self::getThreeOfAKind($hand), self::getFourOfAKind($hand));

        if (count($pairs) === 4) {
            usort(
                $pairs,
                function (Card $card1, Card $card2) {
                    if ($card1->getValue() === $card2->getValue()) {
                        return 0;
                    }

                    return ($card1->getValue() > $card2->getValue()) ? -1 : 1;
                }
            );

            return $pairs;
        }

        return [];
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getThreeOfAKind(PokerHand $hand): array
    {
        return array_diff(self::getPairedCards($hand, 3), self::getFourOfAKind($hand));
    }

    protected static function getFourOfAKind(PokerHand $hand): array
    {
        return self::getPairedCards($hand, 4);
    }

    /**
     * @param PokerHand $hand
     * @param int $numPaired
     * @return Card[]
     */
    protected static function getPairedCards(PokerHand $hand, int $numPaired): array
    {
        $pairedCardsValues = array_keys(
            array_filter(
                array_count_values($hand->getCardsValues()),
                function ($count) use ($numPaired) {
                    return $count === $numPaired;
                }
            )
        );

        $pairedCards = [];
        foreach ($pairedCardsValues as $pairedCardValue) {
            $pairedCardsIndexes = array_keys($hand->getCardsValues(), $pairedCardValue);
            foreach ($pairedCardsIndexes as $pairedCardIndex) {
                $pairedCards[] = $hand->getCards()[$pairedCardIndex];
            }
        }

        return $pairedCards;
    }

    /**
     * @param PokerHand $hand
     * @param bool $checkStraightFlush
     * @return Card[]
     */
    protected static function getStraight(PokerHand $hand, bool $checkStraightFlush = true): array
    {
        if ($checkStraightFlush && count(self::getStraightFlush($hand, false))) {
            // Don't return as a straight if it is a straight flush
            return [];
        }

        $cardValues = $hand->getCardsValues();
        asort($cardValues, SORT_NUMERIC);

        $sequenceCards = self::findNumericalSequenceInArray($cardValues);

        $changeAceValue = false;
        if (!count($sequenceCards) && in_array(14, $cardValues, true)) {
            // If Ace in cards, try with a value of 1
            $aceKey = array_search(14, $cardValues, true);
            $cardValues[$aceKey] = 1;
            asort($cardValues, SORT_NUMERIC);

            $sequenceCards = self::findNumericalSequenceInArray($cardValues);

            $changeAceValue = true;
        }

        $straightCards = [];
        if (count($sequenceCards)) {
            foreach ($sequenceCards as $cardIndex => $cardValue) {
                $card = $hand->getCards()[$cardIndex];
                if ($changeAceValue && $card->getValue() === 14) {
                    // Change Ace value to 1
                    $card->changeAceValue();
                }
                $straightCards[] = $card;
            }
        }

        return $straightCards;
    }

    protected static function findNumericalSequenceInArray(array $cardsValues): array
    {
        $arrayValues = array_values($cardsValues);
        $max = count($arrayValues) - 1;
        $sequenceIndexes = [0];
        for ($i = 0; $i < $max; $i++) {
            if ($arrayValues[$i + 1] - $arrayValues[$i] === 1) {
                $sequenceIndexes[] = $i + 1;
            } else {
                // Not a numerical sequence, exiting function
                return [];
            }
        }

        $sequenceCards = [];
        if (count($sequenceIndexes) === 5) {
            $i = 0;
            foreach ($cardsValues as $cardIndex => $cardValue) {
                if (in_array($i, $sequenceIndexes, true)) {
                    $sequenceCards[$cardIndex] = $cardValue;
                }
                $i++;
            }
        }

        return $sequenceCards;
    }

    /**
     * @param PokerHand $hand
     * @param bool $checkStraightFlush
     * @return Card[]
     */
    protected static function getFlush(PokerHand $hand, bool $checkStraightFlush = true): array
    {
        if ($checkStraightFlush && count(self::getStraightFlush($hand, false))) {
            // Don't return as a flush if it is a straight flush
            return [];
        }

        $suitedCards = [];
        if (count(
            array_filter(
                array_count_values($hand->getCardsSuits()),
                function ($count) {
                    return $count === 5;
                }
            )
        )) {
            $suitedCards = $hand->getCards();
        }

        return $suitedCards;
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getFullHouse(PokerHand $hand): array
    {
        $fullHouseCards = [];
        $threeOfAKindCards = self::getThreeOfAKind($hand);
        if (count($threeOfAKindCards)) {
            $pairCards = self::getPairs($hand);
            if (count($pairCards)) {
                $fullHouseCards = array_merge($threeOfAKindCards, $pairCards);
            }
        }

        return $fullHouseCards;
    }

    /**
     * @param PokerHand $hand
     * @param bool $checkRoyalFlush
     * @return Card[]
     */
    protected static function getStraightFlush(PokerHand $hand, bool $checkRoyalFlush = true): array
    {
        if ($checkRoyalFlush && count(self::getRoyalFlush($hand))) {
            // Don't return as a flush if it is a straight flush
            return [];
        }

        $straightCards = self::getStraight($hand, false);
        $flushCards = self::getFlush($hand, false);

        return count($straightCards) && count($flushCards) ? $straightCards : [];
    }

    /**
     * @param PokerHand $hand
     * @return Card[]
     */
    protected static function getRoyalFlush(PokerHand $hand): array
    {
        $straightFlushCards = self::getStraightFlush($hand, false);

        return (count($straightFlushCards) && max($hand->getCardsValues()) === 14) ? $straightFlushCards : [];
    }
}