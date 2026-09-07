<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
*************************************************************************** 
*   Copyright (C) 2008 by Felipe Ribeiro                                  * 
*   felipernb@gmail.com                                                   *
*************************************************************************** 
*/


/**
 * This class implements the Spell correcting feature, useful for the
 * "Did you mean" functionality on the search engine. Using a dicionary of words
 * extracted from the product catalog.
 *
 * @author Felipe Ribeiro <igorimelnik1995@gmail.com>
 * @date September 22th, 2022
 * @package catalog
 *
 */
class Spellcorrector
{
    private static $NWORDS;
    protected $CI;
    private static $DIR;

    function __construct()
    {
        $this->CI =& get_instance();
        $DIR = realpath('application') . '/libraries/search/';
    }

    /**
     * Reads a text and extracts the list of words
     *
     * @param string $text
     * @return array The list of words
     */
    private static function wordsRO($text)
    {
        $matches = array();
        preg_match_all("/[a-z]+/", mb_strtolower($text), $matches);
        return $matches[0];
    }

    /**
     * Reads a text and extracts the list of words
     *
     * @param string $text
     * @return array The list of words
     */
    private static function wordsEN($text)
    {
        $matches = array();
        preg_match_all("/[a-z]+/", mb_strtolower($text), $matches);
        return $matches[0];
    }

    /**
     * Reads a text and extracts the list of words
     *
     * @param string $text
     * @return array The list of words
     */
    private static function wordsRU($text)
    {
        $matches = array();
        preg_match_all("/([а-я]+)/u", mb_strtolower($text), $matches);
        return $matches[0];
    }

    /**
     * Creates a table (dictionary) where the word is the key and the value is it's relevance
     * in the text (the number of times it appear)
     *
     * @param array $features
     * @return array
     */
    private static function train(array $features)
    {
        $model = array();
        $count = count($features);
        for ($i = 0; $i < $count; $i++) {
            $f = $features[$i];
            if (empty($model[$f])) $model[$f] = 0;
            $model[$f] += 1;
        }
        return $model;
    }

    /**
     * Generates a list of possible "disturbances" on the passed string
     *
     * @param string $word
     * @return array
     */
    private static function editsRO($word)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $alphabet = str_split($alphabet);
        $n = strlen($word);
        $edits = array();
        for ($i = 0; $i < $n; $i++) {
            $edits[] = substr($word, 0, $i) . substr($word, $i + 1);        //deleting one char
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i + 1); //substituting one char
            }
        }
        for ($i = 0; $i < $n - 1; $i++) {
            $edits[] = substr($word, 0, $i) . $word[$i + 1] . $word[$i] . substr($word, $i + 2); //swapping chars order
        }
        for ($i = 0; $i < $n + 1; $i++) {
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i); //inserting one char
            }
        }
        return $edits;
    }


    /**
     * Generates a list of possible "disturbances" on the passed string
     *
     * @param string $word
     * @return array
     */
    private static function editsEN($word)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $alphabet = str_split($alphabet);
        $n = strlen($word);
        $edits = array();
        for ($i = 0; $i < $n; $i++) {
            $edits[] = substr($word, 0, $i) . substr($word, $i + 1);        //deleting one char
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i + 1); //substituting one char
            }
        }
        for ($i = 0; $i < $n - 1; $i++) {
            $edits[] = substr($word, 0, $i) . $word[$i + 1] . $word[$i] . substr($word, $i + 2); //swapping chars order
        }
        for ($i = 0; $i < $n + 1; $i++) {
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i); //inserting one char
            }
        }
        return $edits;
    }

    /**
     * Generates a list of possible "disturbances" on the passed string
     *
     * @param string $word
     * @return array
     */
    private static function editsRU($word)
    {
        $alphabet = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
        $alphabet = str_split($alphabet);
        $n = strlen($word);
        $edits = array();
        for ($i = 0; $i < $n; $i++) {
            $edits[] = substr($word, 0, $i) . substr($word, $i + 1);        //deleting one char
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i + 1); //substituting one char
            }
        }
        for ($i = 0; $i < $n - 1; $i++) {
            $edits[] = substr($word, 0, $i) . $word[$i + 1] . $word[$i] . substr($word, $i + 2); //swapping chars order
        }
        for ($i = 0; $i < $n + 1; $i++) {
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i) . $c . substr($word, $i); //inserting one char
            }
        }

        return $edits;
    }

    /**
     * Generate possible "disturbances" in a second level that exist on the dictionary
     *
     * @param string $word
     * @return array
     */
    private static function known_editsRO($word)
    {
        $known = array();
        foreach (self::editsEN($word) as $e1) {
            foreach (self::editsEN($e1) as $e2) {
                if (array_key_exists($e2, self::$NWORDS)) $known[] = $e2;
            }
        }
        return $known;
    }

    /**
     * Generate possible "disturbances" in a second level that exist on the dictionary
     *
     * @param string $word
     * @return array
     */
    private static function known_editsEN($word)
    {
        $known = array();
        foreach (self::editsRO($word) as $e1) {
            foreach (self::editsRO($e1) as $e2) {
                if (array_key_exists($e2, self::$NWORDS)) $known[] = $e2;
            }
        }
        return $known;
    }

    /**
     * Generate possible "disturbances" in a second level that exist on the dictionary
     *
     * @param string $word
     * @return array
     */
    private static function known_editsRU($word)
    {
        $known = array();
        foreach (self::editsRU($word) as $e1) {
            foreach (self::editsRU($e1) as $e2) {
                if (array_key_exists($e2, self::$NWORDS)) $known[] = $e2;
            }
        }
        return $known;
    }

    /**
     * Given a list of words, returns the subset that is present on the dictionary
     *
     * @param array $words
     * @return array
     */
    private static function known(array $words)
    {
        $known = array();
        foreach ($words as $w) {
            if (array_key_exists($w, self::$NWORDS)) {
                $known[] = $w;

            }
        }
        return $known;
    }


    /**
     * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
     * word passed as parameter,
     *
     * @param string $word
     * @return string
     */
    public static function correctRO($word)
    {
        $word = trim($word);
        $DIR = realpath('application') . '/libraries/search/';
        if (empty($word)) return;

        $word = mb_strtolower($word);

        if (!file_exists($DIR . 'serialized_dictionaryRO.txt')) {
            self::$NWORDS = self::train(self::wordsRO(file_get_contents($DIR . "bigRO.txt")));
            $fp = fopen($DIR . "serialized_dictionaryRO.txt", "w+");
            fwrite($fp, serialize(self::$NWORDS));
            fclose($fp);
        } else {
            self::$NWORDS = unserialize(file_get_contents($DIR . "serialized_dictionaryRO.txt"));
        }

        $candidates = array();
        if (self::known(array($word))) {
            return $word;
        } elseif (!empty($tmp_candidates = self::known(self::editsRO($word)))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } elseif (!empty($tmp_candidates = self::known_editsRO($word))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } else {
            return $word;
        }
        $max = 0;
        foreach ($candidates as $c) {
            $value = self::$NWORDS[$c];
            if ($value > $max) {
                $max = $value;
                $word = $c;
            }
        }
        return $word;
    }

    /**
     * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
     * word passed as parameter,
     *
     * @param string $word
     * @return string
     */
    public static function correctEN($word)
    {
        $word = trim($word);
        $DIR = realpath('application') . '/libraries/search/';
        if (empty($word)) return;

        $word = mb_strtolower($word);

        if (!file_exists($DIR . 'serialized_dictionaryEN.txt')) {
            self::$NWORDS = self::train(self::wordsRO(file_get_contents($DIR . "bigEN.txt")));
            $fp = fopen($DIR . "serialized_dictionaryEN.txt", "w+");
            fwrite($fp, serialize(self::$NWORDS));
            fclose($fp);
        } else {
            self::$NWORDS = unserialize(file_get_contents($DIR . "serialized_dictionaryEN.txt"));
        }

        $candidates = array();
        if (self::known(array($word))) {
            return $word;
        } elseif (!empty($tmp_candidates = self::known(self::editsRO($word)))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } elseif (!empty($tmp_candidates = self::known_editsRO($word))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } else {
            return $word;
        }
        $max = 0;
        foreach ($candidates as $c) {
            $value = self::$NWORDS[$c];
            if ($value > $max) {
                $max = $value;
                $word = $c;
            }
        }
        return $word;
    }

    /**
     * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
     * word passed as parameter,
     *
     * @param string $word
     * @return string
     */

    public static function correctRU($word)
    {
        $word = trim($word);
        $DIR = realpath('application') . '/libraries/search/';
        if (empty($word)) return;

        $word = mb_strtolower($word);


        if (!file_exists($DIR . 'serialized_dictionaryRU.txt')) {
            self::$NWORDS = self::train(self::wordsRU(file_get_contents($DIR . "bigRU.txt")));
            $fp = fopen($DIR . "serialized_dictionaryRU.txt", "w+");
            fwrite($fp, serialize(self::$NWORDS));
            fclose($fp);
        } else {
            self::$NWORDS = unserialize(file_get_contents($DIR . "serialized_dictionaryRU.txt"));
        }

        $candidates = array();
        if (self::known(array($word))) {
            return $word;
        } elseif (($tmp_candidates = self::known(self::editsRU($word)))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } elseif (($tmp_candidates = self::known_editsRU($word))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } else {
            return $word;
        }
        $max = 0;
        foreach ($candidates as $c) {
            $value = self::$NWORDS[$c];
            if ($value > $max) {
                $max = $value;
                $word = $c;
            }
        }
        return $word;
    }

}

?>