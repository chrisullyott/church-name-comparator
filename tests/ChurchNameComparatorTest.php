<?php

use PHPUnit\Framework\TestCase;
use ChrisUllyott\ChurchNameComparator;

class ChurchNameComparatorTest extends TestCase
{
    /**
     * Data for TRUE test cases.
     *
     * @todo Abbreviation with omissions: FBC Clarksville, First Baptist Clarksville
     */
    private static $trueCases = [
        [
            // Group name variations.
            'Living Water Fellowship',
            'Living Water Fellowship Church'
        ],
        [
            // Group name variations.
            'Shadow Mountain Community Church',
            'Shadow Mountain CC'
        ],
        [
            // Shortened name variations.
            'St. Bartholomew\'s Church',
            'St. Bart\'s'
        ],
        [
            // Denominational name variances.
            'Bear Valley Church of the Nazarene',
            'Bear Valley Nazarene Church'
        ],
        [
            // Abbreviation at the beginning.
            'FBC South Lake',
            'First Baptist Church South Lake'
        ],
        [
            // Abbreviation at the end.
            'NEW COVENANT UNITED METHODIST CHURCH',
            'New Covenant UMC'
        ],
        [
            // Abbreviations that may exclude other grammar.
            'FBC South Lake',
            'First Baptist Church of South Lake',
        ],
        [
            // Other name variances.
            'The Episcopal Church in the Philippines',
            'Philippine Episcopal Church'
        ],
        [
            // Minor added suffixes.
            'Casa de Jesus',
            'Casa de Jesus, Inc.'
        ],
    ];

    /**
     * Data for FALSE test cases.
     */
    private static $falseCases = [
        [
            // Strings that should very obviously be dissimilar.
            'Trinity Church',
            'A foxtail is a spikelet or a cluster of grass'
        ],
        [
            // Same name, different denomination.
            'Arabic Church of God',
            'Arabic Evangelical Church'
        ],
        [
            // Same name, different denomination, with an abbreviation.
            'Grace Episcopal Church',
            'Grace SBC'
        ],
        [
            // Same name, different group.
            'Emmanuel Lutheran Church',
            'Emmanuel Lutheran Chapel'
        ],
    ];

    /**
     * @test that the comparison is accurate for TRUE cases.
     */
    public function trueComparisons()
    {
        $comparator = new ChurchNameComparator();
        $comparator->logTo('./tests/comparator_true.log');

        foreach (static::$trueCases as $case) {
            $comparator->setStrings($case[0], $case[1]);

            $this->assertTrue(
                $comparator->isMatch(),
                "False negative: \"{$case[0]}\" ... \"{$case[1]}\""
            );
        }
    }

    /**
     * @test that the comparison is accurate for FALSE cases.
     */
    public function falseComparisons()
    {
        $comparator = new ChurchNameComparator();
        $comparator->logTo('./tests/comparator_false.log');

        foreach (static::$falseCases as $case) {
            $comparator->setStrings($case[0], $case[1]);

            $this->assertFalse(
                $comparator->isMatch(),
                "False positive: \"{$case[0]}\" ... \"{$case[1]}\""
            );
        }
    }
}
