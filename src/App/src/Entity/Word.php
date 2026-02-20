<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.04.2014
 * Time: 13:17
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Core\Stdlib\StringUtils;
use ExprAs\Rest\Entity\AbstractEntity;

#[ORM\Entity]
#[ORM\Table(name: 'dictionary_words', indexes: [
    new ORM\Index(name: 'word_idx', columns: ['word']),
    new ORM\Index(name: 'phoneme_idx', columns: ['phoneme']),
    new ORM\Index(name: 'anagram_key_idx', columns: ['anagram_key']),
    new ORM\Index(name: 'letter_mask_idx', columns: ['letter_mask']),
    new ORM\Index(name: 'rhyme_idx', columns: ['phoneme_last_2_symbols', 'phoneme_last_3_symbols', 'phoneme_last_4_symbols', 'last_2_letters', 'last_3_letters', 'last_4_letters']),
])]
class Word extends AbstractEntity
{
    #[ORM\Column(type: 'string', unique: true, columnDefinition: 'varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL')]
    protected $word;

    #[ORM\Column(type: 'string', name: 'phoneme', nullable: true)]
    protected $phoneme;

    #[ORM\Column(type: 'string', name: 'phoneme_last_2_symbols', length: 2, nullable: true)]
    protected $phonemeLastTwoSymbols;

    #[ORM\Column(type: 'string', name: 'phoneme_last_3_symbols', length: 3, nullable: true)]
    protected $phonemeLastTreeSymbols;

    #[ORM\Column(type: 'string', name: 'phoneme_last_4_symbols', length: 4, nullable: true)]
    protected $phonemeLastFourSymbols;

    #[ORM\Column(type: 'string', name: 'last_2_letters', length: 2, nullable: true)]
    protected $lastTwoLetters;

    #[ORM\Column(type: 'string', name: 'last_3_letters', length: 3, nullable: true)]
    protected $lastTreeLetters;

    #[ORM\Column(type: 'string', name: 'last_4_letters', length: 4, nullable: true)]
    protected $lastFourLetters;

    #[ORM\Column(type: 'string', nullable: true, name: 'anagram_key')]
    protected $anagramKey;

    #[ORM\Column(type: 'boolean', name: 'is_phrase')]
    protected $isPhrase = false;

    #[ORM\Column(type: 'integer')]
    protected $length;

    #[ORM\Column(type: 'string', length: 2)]
    protected $lang;

    #[ORM\Column(type: 'integer', name: 'letter_mask', nullable: false, options: ['default' => 0])]
    protected $letterMask;

    #[ORM\Column(type: 'smallint', name: 'definition_count', nullable: true)]
    protected $definitionCount = 0;

    #[ORM\Column(type: 'smallint', name: 'usage_example_count', nullable: true)]
    protected $usageExampleCount = 0;

    #[ORM\OneToMany(targetEntity: Definition::class, mappedBy: 'word', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $definitions;

    #[ORM\OneToMany(targetEntity: UsageExample::class, mappedBy: 'word', cascade: ['persist', 'remove'])]
    protected $usageExamples;

    #[ORM\OneToMany(targetEntity: Phrase::class, mappedBy: 'relatedWord', cascade: ['persist', 'remove'])]
    protected $relatedPhrases;

    public function __construct()
    {
        $this->definitions = new ArrayCollection();
        $this->usageExamples = new ArrayCollection();
        $this->relatedPhrases = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getPhoneme()
    {
        return $this->phoneme;
    }

    /**
     * @param mixed $doubleMetaphoneTranscription
     */
    public function setPhoneme($doubleMetaphoneTranscription)
    {
        $this->phoneme = $doubleMetaphoneTranscription;
    }


    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }


    /**
     * @return string
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
    public function getIsPhrase()
    {
        return $this->isPhrase;
    }

    /**
     * @param mixed $isPhrase
     */
    public function setIsPhrase($isPhrase)
    {
        $this->isPhrase = $isPhrase;
    }


    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }



    /**
     * @return ArrayCollection
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @param Definition $definitions
     */
    public function setDefinitions($definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param Definition $definition
     *
     * @return array|\Traversable|Definition
     */
    public function addDefinitions($definition)
    {
        if (is_array($definition) || $definition instanceof \Traversable) {
            foreach ($definition as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $definition->setWord($this);
        $this->definitions[] = $definition;
        return $this;
    }

    /**
     * @param Definition $definition
     *
     * @return $this
     */
    public function removeDefinitions($definition)
    {
        if (is_array($definition) || $definition instanceof \Traversable) {
            foreach ($definition as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $this->definitions->removeElement($definition);
        return $this;
    }


    /**
     * @return mixed
     */
    public function getDefinitionCount()
    {
        return $this->definitionCount;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsageExamples()
    {
        return $this->usageExamples;
    }

    /**
     * @param UsageExample $example
     *
     * @return array|\Traversable|UsageExample
     */
    public function addUsageExamples($example)
    {
        if (is_array($example) || $example instanceof \Traversable) {
            foreach ($example as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $example->setWord($this);
        $this->usageExamples[] = $example;
        return $this;
    }

    /**
     * @param UsageExample $example
     *
     * @return $this
     */
    public function removeUsageExamples($example)
    {
        if (is_array($example) || $example instanceof \Traversable) {
            foreach ($example as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $this->usageExamples->removeElement($example);
        return $this;
    }

    /**
     * @param mixed $usageExamples
     */
    public function setUsageExamples($usageExamples)
    {
        $this->usageExamples = $usageExamples;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedPhrases()
    {
        return $this->relatedPhrases;
    }

    /**
     * @param Phrase $phrase
     *
     * @return array|\Traversable|Phrase
     */
    public function addRelatedPhrases($phrase)
    {
        if (is_array($phrase) || $phrase instanceof \Traversable) {
            foreach ($phrase as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $phrase->setRelatedWord($this);
        $this->relatedPhrases[] = $phrase;
        return $this;
    }

    /**
     * @param Phrase $phrase
     *
     * @return $this
     */
    public function removeRelatedPhrases($phrase)
    {
        if (is_array($phrase) || $phrase instanceof \Traversable) {
            foreach ($phrase as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $this->relatedPhrases->removeElement($phrase);
        return $this;
    }

    /**
     * @param mixed $relatedPhrases
     */
    public function setRelatedPhrases($relatedPhrases)
    {
        $this->relatedPhrases = $relatedPhrases;
    }




    /**
     * @param mixed $definitionCount
     */
    public function setDefinitionCount($definitionCount)
    {
        $this->definitionCount = $definitionCount;
    }


    /**
     * @return mixed
     */
    public function getAnagramKey()
    {
        return $this->anagramKey;
    }

    /**
     * @param mixed $anagram
     */
    public function setAnagramKey($anagram)
    {
        $this->anagramKey = $anagram;
    }

    public function generateAnagramKey()
    {
        $this->setAnagramKey(StringUtils::generateAnagramKey($this->getWord()));
    }

    /**
     * @return mixed
     */
    public function getUsageExampleCount()
    {
        return $this->usageExampleCount;
    }

    /**
     * @param mixed $usageExampleCount
     */
    public function setUsageExampleCount($usageExampleCount)
    {
        $this->usageExampleCount = $usageExampleCount;
    }

    /**
     * @return int
     */
    public function getLetterMask()
    {
        return $this->letterMask;
    }

    /**
     * @param int $letterMask
     */
    public function setLetterMask($letterMask)
    {
        $this->letterMask = $letterMask;
    }

    /**
     * @return string
     */
    public function getPhonemeLastTwoSymbols()
    {
        return $this->phonemeLastTwoSymbols;
    }

    /**
     * @param string $phonemeLastTwoSymbols
     */
    public function setPhonemeLastTwoSymbols($phonemeLastTwoSymbols)
    {
        $this->phonemeLastTwoSymbols = $phonemeLastTwoSymbols;
    }

    /**
     * @return string
     */
    public function getPhonemeLastTreeSymbols()
    {
        return $this->phonemeLastTreeSymbols;
    }

    /**
     * @param string $phonemeLastTreeSymbols
     */
    public function setPhonemeLastTreeSymbols($phonemeLastTreeSymbols)
    {
        $this->phonemeLastTreeSymbols = $phonemeLastTreeSymbols;
    }


    /**
     * @return string
     */
    public function getLastTwoLetters()
    {
        return $this->lastTwoLetters;
    }

    /**
     * @param string $lastTwoLetters
     */
    public function setLastTwoLetters($lastTwoLetters)
    {
        $this->lastTwoLetters = $lastTwoLetters;
    }

    /**
     * @return string
     */
    public function getLastTreeLetters()
    {
        return $this->lastTreeLetters;
    }

    /**
     * @param string $lastTreeLetters
     */
    public function setLastTreeLetters($lastTreeLetters)
    {
        $this->lastTreeLetters = $lastTreeLetters;
    }

    /**
     * @return string
     */
    public function getPhonemeLastFourSymbols()
    {
        return $this->phonemeLastFourSymbols;
    }

    /**
     * @param string $phonemeLastFourSymbols
     */
    public function setPhonemeLastFourSymbols($phonemeLastFourSymbols)
    {
        $this->phonemeLastFourSymbols = $phonemeLastFourSymbols;
    }

    /**
     * @return string
     */
    public function getLastFourLetters()
    {
        return $this->lastFourLetters;
    }

    /**
     * @param string $lastFourLetters
     */
    public function setLastFourLetters($lastFourLetters)
    {
        $this->lastFourLetters = $lastFourLetters;
    }



}
