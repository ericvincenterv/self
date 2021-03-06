<?php

namespace Innova\SelfBundle\Manager;

use Innova\SelfBundle\Entity\Question;

class QuestionManager
{
    protected $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createQuestion($questionnaire)
    {
        $em = $this->entityManager;

        $question = new Question();
        $question->setQuestionnaire($questionnaire);
        $em->persist($question);
        $em->flush();

        return $question;
    }

    public function removeSubquestions($question)
    {
        $em = $this->entityManager;

        $subquestions = $question->getSubquestions();
        foreach ($subquestions as $subquestion) {
            $em->remove($subquestion);
        }
        $em->flush();
        $em->refresh($question);
        return $question;
    }
}
