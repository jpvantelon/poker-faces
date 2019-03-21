<?php

namespace App\Tests;

use App\Exception\CardException;
use App\Exception\HandException;
use App\Model\Card;
use App\Model\PokerHand;


class PokerHandTest extends \Codeception\Test\Unit
{
    protected static $testHands = [
        ['hand' => 'KH QH TH jH aH', 'score' => 100, 'name' => 'Royal Flush'],
        ['hand' => '9C jC TC KC QC', 'score' => 91, 'name' => 'Straight Flush King'],
        ['hand' => '7D 9D 8D TD jD', 'score' => 90, 'name' => 'Straight Flush Jack'],
        ['hand' => 'KH QC kD KC kS', 'score' => 81, 'name' => 'Four Of A Kind King'],
        ['hand' => '9H 9S jC 9D 9C', 'score' => 80, 'name' => 'Four Of A Kind 9'],
        ['hand' => 'KH QC kD KC QS', 'score' => 72, 'name' => 'Full House King / Queen'],
        ['hand' => '2C 2S 3C 3D 3s', 'score' => 71, 'name' => 'Full House 3 / 2'],
        ['hand' => '3d 3S 2C 2D 2s', 'score' => 70, 'name' => 'Full House 2 / 3'],

        ['hand' => 'TC QC 5C 7C AC', 'score' => 67, 'name' => 'Flush Ace - Kicker Queen'],
        ['hand' => '5C 6C aC 8C TC', 'score' => 66, 'name' => 'Flush Ace - Kicker 10'],
        ['hand' => '6S QS KS 5S tS', 'score' => 65, 'name' => 'Flush King - Kicker Queen 10'],
        ['hand' => '5H qH kH 2H 7H', 'score' => 64, 'name' => 'Flush King - Kicker Queen Seven'],
        ['hand' => '7S 2S TS KS 6S', 'score' => 63, 'name' => 'Flush King - Kicker 10'],
        ['hand' => '3D 6D QD 2D 4D', 'score' => 62, 'name' => 'Flush Queen - Kicker 6'],
        ['hand' => 'jD 6D tD 9D 2D', 'score' => 61, 'name' => 'Flush Jack'],
        ['hand' => '9D tD 3D 4D 5D', 'score' => 60, 'name' => 'Flush 10'],

        ['hand' => 'AS jS kH qD tH', 'score' => 52, 'name' => 'Straight Ace'],
        ['hand' => 'JH tS 8C 9D qD', 'score' => 51, 'name' => 'Straight Queen'],
        ['hand' => '8C 7D 9D tC jS', 'score' => 50, 'name' => 'Straight Jack'],

        ['hand' => '8S kS AD aH aS', 'score' => 43, 'name' => 'Three Of A Kind Ace'],
        ['hand' => 'QC QD QS JH AS', 'score' => 42, 'name' => 'Three Of A Kind Queen'],
        ['hand' => 'JD AD JH TD JC', 'score' => 41, 'name' => 'Three Of A Kind Jack - Kicker Ace'],
        ['hand' => '7S QD jH jC jS', 'score' => 40, 'name' => 'Three Of A Kind Jack - Kicker Queen'],

        ['hand' => 'jD AS 8S jH AH', 'score' => 33, 'name' => 'Double Pairs Ace / Jack - Kicker 8'],
        ['hand' => 'aD JD jC aH 4H', 'score' => 32, 'name' => 'Double Pairs Ace / Jack - Kicker 4'],
        ['hand' => 'JH aD AC 4S JD', 'score' => 32, 'name' => 'Double Pairs Ace / Jack - Kicker 4'],
        ['hand' => 'KH 2H 2D AC kS', 'score' => 31, 'name' => 'Double Pairs King / 2 - Kicker Ace'],
        ['hand' => 'jC qS jS 9H QD', 'score' => 30, 'name' => 'Double Pairs Queen / Jack'],

        ['hand' => 'AS aD 3D kC 8H', 'score' => 23, 'name' => 'Pairs Ace'],
        ['hand' => 'kD 4H JH JD QS', 'score' => 22, 'name' => 'Pairs Jack - Kicker King Queen 4'],
        ['hand' => '5D kD JH 3D JD', 'score' => 21, 'name' => 'Pairs Jack - Kicker King 5 3'],
        ['hand' => '5D kD JH 2D JD', 'score' => 20, 'name' => 'Pairs Jack - Kicker King 5 2'],

        ['hand' => 'kH TC jS aS 7D', 'score' => 15, 'name' => 'High Cards Ace - Kicker King Jack 10 7'],
        ['hand' => 'kD 6C 3S JC aS', 'score' => 14, 'name' => 'High Cards Ace - Kicker King Jack 6 3'],
        ['hand' => '6H JH kD aD 2C', 'score' => 13, 'name' => 'High Cards Ace - Kicker King Jack 6 2'],
        ['hand' => 'AS 3S QC TD 7S', 'score' => 12, 'name' => 'High Cards Ace - Kicker Queen 10 7 3'],
        ['hand' => 'kS 4H 6H QH 5C', 'score' => 11, 'name' => 'High Cards King'],
        ['hand' => '2H 9H JS 7D TH', 'score' => 10, 'name' => 'High Cards Jack'],
    ];

    protected $validValues;
    protected $validSuits;
    protected $invalidValues;
    protected $invalidSuits;

    /**
     * @var \PokerHandActor
     */
    protected $tester;

    protected function _before(): void
    {
        $this->validValues = array_merge(range(2, 9), ['T', 'J', 'Q', 'K', 'A', 't', 'j', 'q', 'k', 'a']);
        $this->validSuits = Card::SUITS;
        $this->invalidValues = array_merge([0, 1, 10], range('B', 'I'), ['L'], range('M', 'P'), range('R', 'Z'));
        $this->invalidSuits = array_merge([0], ['A', 'B'], range('E', 'G'), range('I', 'R'), range('T', 'Z'));
    }

    protected function _after(): void
    {
    }

    // *************
    // *** TESTS ***
    // *************

    public function testValidation(): void
    {
        foreach ($this->generateInvalidPokerHands() as $invalidPokerHand) {
            $this->assertIsString($invalidPokerHand);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $this->expectException(HandException::class);
            $this->assertNotInstanceOf(PokerHand::class, PokerHand::createFromString($invalidPokerHand), sprintf('PokerHand::createFromString("%s") with invalid hand has produced a PokerHand object', $invalidPokerHand));
        }

        foreach ($this->generateValidPokerHandsWithInvalidCards() as $invalidPokerHand) {
            $this->assertIsString($invalidPokerHand);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $this->expectException(CardException::class);
            $this->assertNotInstanceOf(PokerHand::class, PokerHand::createFromString($invalidPokerHand), sprintf('PokerHand::createFromString("%s") with invalid cards has produced a PokerHand object', $invalidPokerHand));
        }

        $validHand = $this->generateValidPokerHand();
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(PokerHand::class, PokerHand::createFromString($validHand), sprintf('PokerHand::createFromString("%s") with valid hand has not produced a PokerHand object', $validHand));
    }

    public function testCompareWithWonLost(): void
    {
        $isWon = false;
        $isLost = false;
        $i = 0;
        do {
            $i++;

            $h1 = PokerHand::createFromString($this->generateValidPokerHand());
            do {
                $h2 = PokerHand::createFromString($this->generateValidPokerHand());
            } while ($h1->hasSameCard($h2));

            $result = $h1->compareWith($h2);

            $this->assertIsInt($result, sprintf('Result is not an integer while comparing hand "%s" with hand "%s"', $h1->getCardsAsString(), $h2->getCardsAsString()));

            switch ($result) {
                case 1:
                    $isWon = true;
                    break;
                case 2:
                    $isLost = true;
                    break;
            }

            $WinLostCasesCovered = $isWon && $isLost;

        } while (!$WinLostCasesCovered && $i < 20);

        $this->assertTrue($WinLostCasesCovered, sprintf('Won and lost cases have not been covered in %s iterations', $i));
    }

    public function testCompareWithTie(): void
    {
        $h1 = PokerHand::createFromString('AC KD AH QD 7S');
        do {
            $h2 = PokerHand::createFromString('AS KC AD QC 7D');
        } while ($h1->hasSameCard($h2));

        $result = $h1->compareWith($h2);

        $this->assertIsInt($result, sprintf('Result is not an integer while comparing hand "%s" with hand "%s"', $h1->getCardsAsString(), $h2->getCardsAsString()));

        $this->assertEquals(3, $result, 'Result in not 3 while hands are tied');
    }

    public function testCompareWithSpecificTestHands(): void
    {
        foreach (self::$testHands as $testHand1) {
            try {
                /** @var PokerHand $h1 */
                $h1 = $this->constructPokerHandWithoutCheckingSameCards(PokerHand::createFromString($testHand1['hand']));
            } catch (\Exception $e) {
                codecept_debug($testHand1['hand']);
                exit;
            }
            foreach (self::$testHands as $testHand2) {
                try {
                    /** @var PokerHand $h2 */
                    $h2 = $this->constructPokerHandWithoutCheckingSameCards(PokerHand::createFromString($testHand2['hand']));
                } catch (\Exception $e) {
                    codecept_debug($testHand2['hand']);
                    exit;
                }

                $result = $h1->compareWith($h2);

                if ($testHand1['score'] === $testHand2['score']) {
                    $this->assertEquals(3, $result, sprintf('Result is not 3 while comparing equal hands : "%s" (%s) vs "%s" (%s)', $h1->getCardsAsString(), $testHand1['name'], $h2->getCardsAsString(), $testHand2['name']));
                } elseif ($testHand1['score'] > $testHand2['score']) {
                    $this->assertEquals(1, $result, sprintf('Result is not 1 while comparing a winning hand : "%s" (%s) vs "%s" (%s)', $h1->getCardsAsString(), $testHand1['name'], $h2->getCardsAsString(), $testHand2['name']));
                } else {
                    $this->assertEquals(2, $result, sprintf('Result is not 2 while comparing a losing hand : "%s" (%s) vs "%s" (%s)', $h1->getCardsAsString(), $testHand1['name'], $h2->getCardsAsString(), $testHand2['name']));
                }
            }
        }
    }

    // ************************
    // *** Helper Functions ***
    // ************************

    protected function generateValidPokerHand(): string
    {
        $validValues = $this->validValues;
        $validSuits = $this->validSuits;
        $cards = [];
        $i = 0;
        do {
            $card = $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)];
            if (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 5);

        return implode(' ', $cards);
    }

    protected function generateInvalidPokerHands(): array
    {
        $validValues = $this->validValues;
        $validSuits = $this->validSuits;

        $invalidPokerHands = [];

        // Bad format
        $validPokerHand = $this->generateValidPokerHand();
        $invalidPokerHands[] = str_replace(' ', '', $validPokerHand);

        // Not enough cards
        $cards = [];
        $i = 0;
        do {
            $card = $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)];
            if (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 4);
        $invalidPokerHands[] = implode(' ', $cards);

        // Too much cards
        $cards = [];
        $i = 0;
        do {
            $card = $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)];
            if (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 6);
        $invalidPokerHands[] = implode(' ', $cards);

        // Same cards
        $cards = [];
        $i = 0;
        do {
            $card = $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)];
            if ($i > 2 && in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            } elseif (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 5);
        $invalidPokerHands[] = implode(' ', $cards);

        return $invalidPokerHands;
    }

    protected function generateValidPokerHandsWithInvalidCards(): array
    {
        $validValues = $this->validValues;
        $validSuits = $this->validSuits;

        $invalidValues = $this->invalidValues;
        $invalidSuits = $this->invalidSuits;

        $invalidPokerHands = [];

        // Invalid values
        $cards = [];
        $i = 0;
        do {
            $card = ($i % 2) ? $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)] : $invalidValues[array_rand($invalidValues)].$validSuits[array_rand($validSuits)];
            if (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 5);
        $invalidPokerHands[] = implode(' ', $cards);

        // Invalid suits
        $cards = [];
        $i = 0;
        do {
            $card = ($i % 2) ? $validValues[array_rand($validValues)].$validSuits[array_rand($validSuits)] : $validValues[array_rand($validValues)].$invalidSuits[array_rand($invalidSuits)];
            if (!in_array(strtoupper($card), array_map('strtoupper', $cards), true)) {
                $cards[] = $card;
                $i++;
            }
        } while ($i < 5);
        $invalidPokerHands[] = implode(' ', $cards);

        return $invalidPokerHands;
    }

    /** @noinspection PhpUndefinedClassInspection */
    protected function constructPokerHandWithoutCheckingSameCards(PokerHand $hand): object
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->construct(PokerHand::class, ['cards' => $hand->getCards()], ['hasSameCard' => false]);
    }
}