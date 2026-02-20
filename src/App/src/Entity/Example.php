<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 15.05.2014
 * Time: 17:45
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;

#[ORM\Entity]
#[ORM\Table(name: 'dictionary_examples')]
class Example extends AbstractEntity
{
    #[ORM\Column(type: 'string', length: 1024)]
    protected $sentence;

    #[ORM\OneToMany(targetEntity: UsageExample::class, mappedBy: 'example', cascade: ['remove'])]
    protected $usageExamples;

    #[ORM\Column(type: 'string', name: 'crc', unique: true)]
    protected $crc;

    public function __construct()
    {
        $this->usageExamples = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getSentence()
    {
        return $this->sentence;
    }

    /**
     * @param mixed $sentence
     */
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;
    }

    /**
     * @return Word
     */
    public function getUsageExamples()
    {
        return $this->usageExamples;
    }

    /**
     * @param mixed $word
     */
    public function setUsageExamples($word)
    {
        $this->usageExamples = $word;
    }

    /**
     * @param UsageExample $examples
     *
     * @return array|\Traversable|UsageExample
     */
    public function addUsageExamples($examples)
    {
        if (is_array($examples) || $examples instanceof \Traversable) {
            foreach ($examples as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $examples->setExample($this);
        $this->usageExamples[] = $examples;
        return $this;
    }

    /**
     * @param UsageExample $examples
     *
     * @return $this
     */
    public function removeUsageExamples($examples)
    {
        if (is_array($examples) || $examples instanceof \Traversable) {
            foreach ($examples as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
            return $this;
        }
        $this->usageExamples->removeElement($examples);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCrc()
    {
        return $this->crc;
    }

    /**
     * @param mixed $crc
     */
    public function setCrc($crc)
    {
        $this->crc = $crc;
    }


    public function __toString()
    {
        return $this->getSentence();
    }

}
