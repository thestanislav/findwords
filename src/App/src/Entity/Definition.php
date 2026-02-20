<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 13.04.2014
 * Time: 0:01
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;

#[ORM\Entity]
#[ORM\Table(name: 'dictionary_word_definitions')]
class Definition extends AbstractEntity
{
    #[ORM\Column(type: 'string', name: 'match_term', nullable: true)]
    protected $matchTerm;

    #[ORM\Column(type: 'text')]
    protected $content;

    #[ORM\Column(type: 'string', name: 'part_of_speech', length: 12, nullable: true)]
    protected $partOfSpeech;

    #[ORM\ManyToOne(targetEntity: Word::class, inversedBy: 'definitions')]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    protected $word;

    #[ORM\Column(type: 'boolean', name: 'is_phrase_related_definition', options: ['default' => 0])]
    protected $isPhraseRelatedDefinition = false;

    #[ORM\ManyToOne(targetEntity: Dictionary::class, inversedBy: 'definitions')]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    protected $dictionary;

    /**
     * @return boolean
     */
    public function isIsPhraseRelatedDefinition()
    {
        return $this->isPhraseRelatedDefinition;
    }

    /**
     * @param boolean $isPhraseRelatedDefinition
     */
    public function setIsPhraseRelatedDefinition($isPhraseRelatedDefinition)
    {
        $this->isPhraseRelatedDefinition = $isPhraseRelatedDefinition;
    }


    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return Dictionary
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param mixed $dictionary
     */
    public function setDictionary($dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * @return mixed
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param mixed $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }


    /**
     * @return mixed
     */
    public function getPartOfSpeech()
    {
        return $this->partOfSpeech;
    }

    /**
     * @param mixed $partOfSpeech
     */
    public function setPartOfSpeech($partOfSpeech)
    {
        $this->partOfSpeech = $partOfSpeech;
    }

    /**
     * @return mixed
     */
    public function getMatchTerm()
    {
        return $this->matchTerm;
    }

    /**
     * @param mixed $matchTerm
     */
    public function setMatchTerm($matchTerm)
    {
        $this->matchTerm = $matchTerm;
    }

    /**
     * @return mixed
     */
    public function getIsPhraseRelatedDefinition()
    {
        return $this->isPhraseRelatedDefinition;
    }


}
