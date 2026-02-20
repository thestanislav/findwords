<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 26.05.2014
 * Time: 9:40
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dictionary_word_phrases')]
class Phrase
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Word::class, inversedBy: 'relatedPhrases')]
    #[ORM\JoinColumn(onDelete: 'cascade', name: 'related_word_id')]
    protected $relatedWord;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Word::class)]
    #[ORM\JoinColumn(onDelete: 'cascade', name: 'phrase_word_id')]
    protected $phraseWord;

    /**
     * @return mixed
     */
    public function getPhraseWord()
    {
        return $this->phraseWord;
    }

    /**
     * @param mixed $phraseWord
     */
    public function setPhraseWord($phraseWord)
    {
        $this->phraseWord = $phraseWord;
    }

    /**
     * @return mixed
     */
    public function getRelatedWord()
    {
        return $this->relatedWord;
    }

    /**
     * @param mixed $relatedWord
     */
    public function setRelatedWord($relatedWord)
    {
        $this->relatedWord = $relatedWord;
    }


}
