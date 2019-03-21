<?php

namespace App\Model;

use App\Exception\CardException;

class Card
{
    public const SUITS = ['C', 'D', 'H', 'S'];

    protected const LETTERS_TO_VALUES = [
        'T' => 10,
        'J' => 11,
        'Q' => 12,
        'K' => 13,
        'A' => 14,
    ];

    /** @var int */
    protected $value;
    /** @var string */
    protected $suit;

    /**
     * @param int $value
     * @param string $suit
     */
    public function __construct(int $value, string $suit)
    {
        $suit = strtoupper($suit);
        if (!in_array($suit, self::SUITS, true)) {
            throw new CardException(sprintf('Card definition is invalid: unknown letter "%s" for suit', $suit));
        }
        if ($value < 2 || $value > 14) {
            throw new CardException(sprintf('Card definition is invalid: invalid card value "%s"', $value));
        }

        $this->value = $value;
        $this->suit = $suit;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return $this
     */
    public function changeAceValue(): self
    {
        if ($this->getValue() === 14) {
            $this->value = 1;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Card factory
     *
     * @param string $cardDefinition
     * @return Card
     */
    public static function createFromString(string $cardDefinition): self
    {
        if (strlen($cardDefinition) !== 2) {
            throw new CardException('Card definition is invalid: not 2 characters');
        }
        [$value, $suit] = str_split($cardDefinition);

        /** @noinspection TypeUnsafeComparisonInspection */
        if (is_numeric($value) && (int)$value == $value) {
            $value = (int)$value;
            if ($value < 2 || $value > 9) {
                throw new CardException(
                    sprintf('Card definition is invalid: invalid card type for value "%s"', $value)
                );
            }
        } elseif (array_key_exists(strtoupper($value), self::LETTERS_TO_VALUES)) {
            $value = self::LETTERS_TO_VALUES[strtoupper($value)];
        } else {
            throw new CardException(sprintf('Card definition is invalid: unknown letter for value "%s"', $value));
        }

        return new Card($value, $suit);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $valuesToLetters = array_flip(self::LETTERS_TO_VALUES);
        $valueAsString = array_key_exists($this->getValue(), $valuesToLetters) ? $valuesToLetters[$this->getValue(
        )] : (string)$this->getValue();

        return $valueAsString.$this->getSuit();
    }
}