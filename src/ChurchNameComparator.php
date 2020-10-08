<?php

namespace ChrisUllyott;

class ChurchNameComparator
{
    private static $prefixes = [
        'First',
        'Second'
    ];

    private static $denominations = [
        'Adventist',
        'Anglican',
        'Assembly of God',
        'Baptist',
        'Brethren',
        'Calvary Chapel',
        'Catholic',
        'Christian',
        'Church of Christ',
        'Church of God',
        'Church of God in Christ',
        'Church of Jesus Christ',
        'Episcopal',
        'Evangelical',
        'Lutheran',
        'Mennonite',
        'Methodist',
        'Moravian',
        'Nazarene',
        'Orthodox',
        'Pentecostal',
        'Presbyterian',
        'Protestant',
        'Reformed',
        'Seventh-day Adventist',
        'Spiritualist',
        'Unitarian Universalist',
        'Universal',
        'Wesleyan',
    ];

    private static $adjectives = [
        'Apostolic',
        'Eastern',
        'Evangelical',
        'New',
        'Northern',
        'Orthodox',
        'Reformed',
        'Southern',
        'United',
        'Western',
    ];

    private static $groups = [
        'Cathedral',
        'Center',
        'Chapel',
        'Church',
        'Community',
        'Fellowship',
        'Temple',
    ];

    private static $suffixes = [
        'Inc',
    ];

    private static $grammar = [
        'an',
        'at',
        'in',
        'of',
        'the',
    ];

    public function __construct($string1 = null, $string2 = null)
    {
        if ($string1 && $string2) $this->setStrings($string1, $string2);
    }

    public function logTo($file)
    {
        !file_exists($file) || unlink($file);

        $this->logToFile = $file;

        return $this;
    }

    private function log($message)
    {
        if (empty($this->logToFile)) return;

        file_put_contents($this->logToFile, "{$message}\n", FILE_APPEND);

        return $this;
    }

    public function setStrings($string1, $string2)
    {
        $this->string1 = static::sanitize($string1);
        $this->string2 = static::sanitize($string2);

        return $this;
    }

    public function isMatch()
    {
        $this->log("{$this->string1} -- {$this->string2}");

        // The strings are identical except for case.
        if ($this->isEqual()) {
            $this->log("isEqual\n");
            return true;
        }

        // Groups are different, so don't go any further.
        if ($this->isDifferentGroup()) {
            $this->log("isDifferentGroup\n");
            return false;
        }

        // The strings are equal when abbreviated.
        if ($this->isAbbreviation()) {
            $this->log("isAbbreviation\n");
            return true;
        }

        // Denominations are different, so don't go any further.
        if ($this->isDifferentDenomination()) {
            $this->log("isDifferentDenomination\n");
            return false;
        }

        // When reduced (no denomination or group), one string is a substring.
        if ($this->matchesReduced()) {
            $this->log("matchesReduced\n");
            return true;
        }

        $this->log("not a match\n");

        return false;
    }

    public function isEqual()
    {
        return strtolower($this->string1) === strtolower($this->string2);
    }

    public function isDifferentDenomination()
    {
        $denom1 = $this->parseDenomination($this->string1);
        $denom2 = $this->parseDenomination($this->string2);
        $this->log("denom: {$denom1} -- {$denom2}");

        return isset($denom1) && isset($denom2) && $denom1 !== $denom2;
    }

    public function isDifferentGroup()
    {
        $group1 = $this->parseGroup($this->string1);
        $group2 = $this->parseGroup($this->string2);
        $this->log("group: {$group1} -- {$group2}");

        return isset($group1) && isset($group2) && $group1 !== $group2;
    }

    public function matchesReduced()
    {
        $reduced1 = static::reduceTerms($this->string1);
        $reduced2 = static::reduceTerms($this->string2);
        $this->log("reduced: {$reduced1} -- {$reduced2}");

        return static::isContained($reduced1, $reduced2);
    }

    public function isAbbreviation()
    {
        $abbrev1 = $this->smartAbbreviate($this->string1, true);
        $abbrev2 = $this->smartAbbreviate($this->string2, true);
        $this->log("abbrev: {$abbrev1} -- {$abbrev2}");

        return $abbrev1 === $abbrev2;
    }

    private function parseDenomination($string)
    {
        $denom = static::matchLongestSubstring($string, static::$denominations);

        if (!$denom) {
            return static::matchLongestSubstring($string, $this->getAbbreviations());
        }

        return $denom;
    }

    private function parseGroup($group)
    {
        return static::matchLongestSubstring($group, static::$groups);
    }

    private function getAbbreviations()
    {
        if (empty($this->abbreviations)) {
            $this->abbreviations = $this->generateAbbreviations();
        }

        return $this->abbreviations;
    }

    private static function getAllTerms()
    {
        return array_merge(
            static::$prefixes,
            static::$denominations,
            static::$adjectives,
            static::$groups,
            static::$suffixes,
            static::$grammar
        );
    }

    private function generateAbbreviations()
    {
        $abbreviations = [];

        $prefixes = array_merge(static::$prefixes, ['']);
        $adjectives = array_merge(static::$adjectives, ['']);

        foreach ($prefixes as $pre) {
            foreach ($adjectives as $adj) {
                foreach (static::$denominations as $den) {
                    foreach (static::$groups as $grp) {
                        $abbrev = static::abbreviate("{$pre} {$adj} {$den} {$grp}");
                        $abbreviations[$abbrev] = 1;
                    }
                }
            }
        }

        return array_keys($abbreviations);
    }

    private static function isContained($string1, $string2)
    {
        $sorted = static::sortArrayByLength([$string1, $string2]);

        return stripos($sorted[0], $sorted[1]) !== false;
    }

    private static function reduceTerms($string)
    {
        $string = static::removePosession($string);
        $string = static::removeNonAlpha($string);
        $searches = static::sortArrayByLength(static::getAllTerms());

        foreach ($searches as $search) {
            $pattern = "/\b" . preg_quote($search) . "\b/i";
            $string = preg_replace($pattern, ' ', $string);
        }

        return static::sanitize($string);
    }

    private static function abbreviate($string)
    {
        $out = '';
        $string = strtoupper(static::removeNonAlpha($string));
        $parts = preg_split('/\s+/', $string);

        foreach ($parts as $part) {
            if (static::isGrammarString($part)) continue;
            $out .= $part[0];
        }

        return $out;
    }

    private function smartAbbreviate($string)
    {
        $out = '';
        $string = strtoupper(static::removeNonAlpha($string));
        $parts = preg_split('/\s+/', $string);

        foreach ($parts as $part) {
            if (static::isGrammarString($part)) continue;
            $out .= $this->isAbbreviatedString($part) ? $part : $part[0];
        }

        return $out;
    }

    private function isAbbreviatedString($string)
    {
        $string = strtoupper($string);

        return in_array($string, $this->getAbbreviations());
    }

    private static function isGrammarString($string)
    {
        return in_array(strtolower($string), static::$grammar);
    }

    private static function matchLongestSubstring($string, array $substrings)
    {
        $substrings = static::sortArrayByLength($substrings);

        foreach ($substrings as $substring) {
            $pattern = "/\b" . preg_quote($substring) . "\b/i";
            if (preg_match($pattern, $string)) return $substring;
        }

        return null;
    }

    private static function sortArrayByLength(array $array)
    {
        usort($array, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return $array;
    }

    private static function removePosession($string)
    {
        return static::sanitize(preg_replace("/\'s\b/", '', $string));
    }

    private static function removeNonAlpha($string)
    {
        return static::sanitize(preg_replace('/[^a-z ]/i', '', $string));
    }

    private static function sanitize($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
