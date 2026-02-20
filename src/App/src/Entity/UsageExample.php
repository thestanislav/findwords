<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 15.05.2014
 * Time: 17:45
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dictionary_usage_examples')]
class UsageExample
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Example::class, inversedBy: 'usageExamples')]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    protected $example;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Word::class, inversedBy: 'usageExamples')]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    protected $word;

    #[ORM\Column(type: 'string', name: 'matched_word')]
    protected $matchedWord;

    /**
     * @return Example
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @param Example $example
     */
    public function setExample($example)
    {
        $this->example = $example;
    }

    /**
     * @return mixed
     */
    public function getMatchedWord()
    {
        return $this->matchedWord;
    }

    /**
     * @param mixed $matchedWord
     */
    public function setMatchedWord($matchedWord)
    {
        $this->matchedWord = $matchedWord;
    }

    /**
     * @return Word
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param Word $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }
}
