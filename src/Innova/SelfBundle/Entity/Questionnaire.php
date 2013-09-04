<?php

namespace Innova\SelfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Questionnaire
 *
 * @ORM\Table("questionnaire")
 * @ORM\Entity
 */
class Questionnaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", length=255)
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="supportType", type="string", length=255)
     */
    private $supportType;

    /**
     * @var string
     *
     * @ORM\Column(name="focus", type="string", length=255)
     */
    private $focus;

    /**
     * @var string
     *
     * @ORM\Column(name="cognitiveOperation", type="string", length=255)
     */
    private $cognitiveOperation;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=255)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="receptionType", type="string", length=255)
     */
    private $receptionType;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=255)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="sourceType", type="string", length=255)
     */
    private $sourceType;

    /**
     * @var string
     *
     * @ORM\Column(name="languageLevel", type="string", length=255)
     */
    private $languageLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="durationGroup", type="string", length=255)
     */
    private $durationGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="flow", type="string", length=255)
     */
    private $flow;

    /**
     * @var integer
     *
     * @ORM\Column(name="wordCount", type="integer")
     */
    private $wordCount;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=255)
     */
    private $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="instruction", type="string", length=255)
     */
    private $instruction;

    /**
     * @var string
     *
     * @ORM\Column(name="audioInstruction", type="string", length=255)
     */
    private $audioInstruction;

    /**
     * @var string
     *
     * @ORM\Column(name="audioContext", type="string", length=255)
     */
    private $audioContext;

    /**
     * @var string
     *
     * @ORM\Column(name="audioItem", type="string", length=255)
     */
    private $audioItem;

    /**
    * @ORM\OneToMany(targetEntity="Question", mappedBy="questionnaire", cascade={"remove", "persist"})
    */
    protected $questions;

    /**
    * @ORM\OneToMany(targetEntity="Trace", mappedBy="questionnaire", cascade={"remove", "persist"})
    */
    protected $traces;

    /**
    * @ORM\ManyToMany(targetEntity="Test", mappedBy="questionnaires")
    */
    private $tests;

    public function __construct() {
        $this->tests = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString(){
        return $this->theme;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set level
     *
     * @param string $level
     * @return Questionnaire
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Questionnaire
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Set supportType
     *
     * @param string $supportType
     * @return Questionnaire
     */
    public function setSupportType($supportType)
    {
        $this->supportType = $supportType;

        return $this;
    }

    /**
     * Get supportType
     *
     * @return string
     */
    public function getSupportType()
    {
        return $this->supportType;
    }

    /**
     * Set focus
     *
     * @param string $focus
     * @return Questionnaire
     */
    public function setFocus($focus)
    {
        $this->focus = $focus;

        return $this;
    }

    /**
     * Get focus
     *
     * @return string
     */
    public function getFocus()
    {
        return $this->focus;
    }

    /**
     * Set cognitiveOperation
     *
     * @param text $cognitiveOperation
     * @return Questionnaire
     */
    public function setCognitiveOperation($cognitiveOperation)
    {
        $this->cognitiveOperation = $cognitiveOperation;

        return $this;
    }

    /**
     * Get cognitiveOperation
     *
     * @return string
     */
    public function getCognitiveOperation()
    {
        return $this->cognitiveOperation;
    }

    /**
     * Set function
     *
     * @param string $function
     * @return Questionnaire
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set receptionType
     *
     * @param string $receptionType
     * @return Questionnaire
     */
    public function setReceptionType($receptionType)
    {
        $this->receptionType = $receptionType;

        return $this;
    }

    /**
     * Get receptionType
     *
     * @return string
     */
    public function getReceptionType()
    {
        return $this->receptionType;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return Questionnaire
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Questionnaire
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sourceType
     *
     * @param string $sourceType
     * @return Questionnaire
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    /**
     * Get sourceType
     *
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * Set languageLevel
     *
     * @param string $languageLevel
     * @return Questionnaire
     */
    public function setLanguageLevel($languageLevel)
    {
        $this->languageLevel = $languageLevel;

        return $this;
    }

    /**
     * Get languageLevel
     *
     * @return string
     */
    public function getLanguageLevel()
    {
        return $this->languageLevel;
    }

    /**
     * Set durationGroup
     *
     * @param string $durationGroup
     * @return Questionnaire
     */
    public function setDurationGroup($durationGroup)
    {
        $this->durationGroup = $durationGroup;

        return $this;
    }

    /**
     * Get durationGroup
     *
     * @return string
     */
    public function getDurationGroup()
    {
        return $this->durationGroup;
    }

    /**
     * Set flow
     *
     * @param string $flow
     * @return Questionnaire
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * Get flow
     *
     * @return string
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * Set wordCount
     *
     * @param integer $wordCount
     * @return Questionnaire
     */
    public function setWordCount($wordCount)
    {
        $this->wordCount = $wordCount;

        return $this;
    }

    /**
     * Get wordCount
     *
     * @return integer
     */
    public function getWordCount()
    {
        return $this->wordCount;
    }

    /**
     * Set theme
     *
     * @param string $theme
     * @return Questionnaire
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return Questionnaire
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set instruction
     *
     * @param string $instruction
     * @return Questionnaire
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;

        return $this;
    }

    /**
     * Get instruction
     *
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Set audioInstruction
     *
     * @param string $audioInstruction
     * @return Questionnaire
     */
    public function setAudioInstruction($audioInstruction)
    {
        $this->audioInstruction = $audioInstruction;

        return $this;
    }

    /**
     * Get audioInstruction
     *
     * @return string
     */
    public function getAudioInstruction()
    {
        return $this->audioInstruction;
    }

    /**
     * Set audioContext
     *
     * @param string $audioContext
     * @return Questionnaire
     */
    public function setAudioContext($audioContext)
    {
        $this->audioContext = $audioContext;

        return $this;
    }

    /**
     * Get audioContext
     *
     * @return string
     */
    public function getAudioContext()
    {
        return $this->audioContext;
    }

    /**
     * Set audioItem
     *
     * @param string $audioItem
     * @return Questionnaire
     */
    public function setAudioItem($audioItem)
    {
        $this->audioItem = $audioItem;

        return $this;
    }

    /**
     * Get audioItem
     *
     * @return string
     */
    public function getAudioItem()
    {
        return $this->audioItem;
    }

    /**
     * Add questions
     *
     * @param \Innova\SelfBundle\Entity\Question $questions
     * @return Questionnaire
     */
    public function addQuestion(\Innova\SelfBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Innova\SelfBundle\Entity\Question $questions
     */
    public function removeQuestion(\Innova\SelfBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add traces
     *
     * @param \Innova\SelfBundle\Entity\Trace $traces
     * @return Questionnaire
     */
    public function addTrace(\Innova\SelfBundle\Entity\Trace $traces)
    {
        $this->traces[] = $traces;

        return $this;
    }

    /**
     * Remove traces
     *
     * @param \Innova\SelfBundle\Entity\Trace $traces
     */
    public function removeTrace(\Innova\SelfBundle\Entity\Trace $traces)
    {
        $this->traces->removeElement($traces);
    }

    /**
     * Get traces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTraces()
    {
        return $this->traces;
    }

    /**
     * Add tests
     *
     * @param \Innova\SelfBundle\Entity\Test $tests
     * @return Questionnaire
     */
    public function addTest(\Innova\SelfBundle\Entity\Test $tests)
    {
        $this->tests[] = $tests;
        $tests->addQuestionnaire($this);

        return $this;
    }

    /**
     * Remove tests
     *
     * @param \Innova\SelfBundle\Entity\Test $tests
     */
    public function removeTest(\Innova\SelfBundle\Entity\Test $tests)
    {
        $this->tests->removeElement($tests);
    }

    /**
     * Get tests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTests()
    {
        return $this->tests;
    }

}