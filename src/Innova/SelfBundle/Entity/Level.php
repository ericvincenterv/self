<?php

namespace Innova\SelfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Level
 * 28/11/2013 : Add "OneToMany" to User.
 *
 * @ORM\Table("questionnaireLevel")
 * @ORM\Entity
 */
class Level
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @ORM\OneToMany(targetEntity="Questionnaire", mappedBy="level")
    */
    protected $questionnaires;

    /**
    * @ORM\OneToMany(targetEntity="User", mappedBy="level")
    */
    protected $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questionnaires = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * To String
     */

    public function __toString() {
        return $this->getName();
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
     * Set name
     *
     * @param string $name
     * @return Level
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add questionnaires
     *
     * @param \Innova\SelfBundle\Entity\Questionnaire $questionnaires
     * @return Level
     */
    public function addQuestionnaire(\Innova\SelfBundle\Entity\Questionnaire $questionnaires)
    {
        $this->questionnaires[] = $questionnaires;

        return $this;
    }

    /**
     * Remove questionnaires
     *
     * @param \Innova\SelfBundle\Entity\Questionnaire $questionnaires
     */
    public function removeQuestionnaire(\Innova\SelfBundle\Entity\Questionnaire $questionnaires)
    {
        $this->questionnaires->removeElement($questionnaires);
    }

    /**
     * Get questionnaires
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestionnaires()
    {
        return $this->questionnaires;
    }

    /**
     * Add users
     *
     * @param \Innova\SelfBundle\Entity\User $users
     * @return Level
     */
    public function addUser(\Innova\SelfBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Innova\SelfBundle\Entity\User $users
     */
    public function removeUser(\Innova\SelfBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}