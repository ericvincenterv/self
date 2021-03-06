<?php

namespace Innova\SelfBundle\Controller\Editor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class QuestionnaireController
 * @Route(
 *      "admin/editor",
 *      name    = "",
 *      service = "innova_editor_questionnaire"
 * )
 */

class QuestionnaireController
{

    protected $questionnaireManager;
    protected $questionManager;
    protected $orderQuestionnaireTestManager;
    protected $editorLogManager;
    protected $testManager;
    protected $entityManager;
    protected $request;
    protected $templating;

    public function __construct(
            $questionnaireManager,
            $questionManager,
            $orderQuestionnaireTestManager,
            $editorLogManager,
            $testManager,
            $entityManager,
            $templating
    )
    {
        $this->questionnaireManager = $questionnaireManager;
        $this->questionManager = $questionManager;
        $this->orderQuestionnaireTestManager = $orderQuestionnaireTestManager;
        $this->editorLogManager = $editorLogManager;
        $this->testManager = $testManager;
        $this->entityManager = $entityManager;
        $this->templating = $templating;
    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }


    /**
     * Lists all Questionnaire entities.
     *
     * @Route("/questionnaires", name="editor_questionnaires_show")
     * @Method("GET")
     * @Template("InnovaSelfBundle:Editor:listQuestionnaires.html.twig")
     */
    public function listQuestionnairesAction()
    {
        $em = $this->entityManager;

        $questionnaires = $em->getRepository('InnovaSelfBundle:Questionnaire')->findAll();

        return array(
            'questionnaires' => $questionnaires,
        );
    }


    /**
     * Lists all Questionnaire entities for a test (ordered)
     *
     * @Route("/test/{testId}/questionnaires", name="editor_test_questionnaires_show")
     * @Method("GET")
     * @Template("InnovaSelfBundle:Editor:listTestQuestionnaires.html.twig")
     */
    public function listTestQuestionnairesAction($testId)
    {
        $em = $this->entityManager;

        $test = $em->getRepository('InnovaSelfBundle:Test')->find($testId);
        $orders = $em->getRepository('InnovaSelfBundle:OrderQuestionnaireTest')->findByTest($testId);
        $potentialQuestionnaires = $this->testManager->getPotentialQuestionnaires($test);
       
        return array(
            'test' => $test,
            'orders' => $orders,
            'potentialQuestionnaires' => $potentialQuestionnaires
        );
    }

    /**
     * Get questionnaires not associated yet to a test 
     *
     * @Route("/test/{testId}/potentials", name="editor_test_questionnaires_potentials", options={"expose"=true})
     * @Method("GET")
     */
    public function getPotentialQuestionnaires($testId){
        $em = $this->entityManager;

        $test = $em->getRepository('InnovaSelfBundle:Test')->find($testId);
        $potentialQuestionnaires = $this->testManager->getPotentialQuestionnaires($test);

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:potentialQuestionnaires.html.twig',array('test'=> $test, 'potentialQuestionnaires' => $potentialQuestionnaires));
        return new Response($template);
    }


    /**
     * Finds and displays a Questionnaire entity.
     *
     * @Route("/questionnaire/{questionnaireId}", name="editor_questionnaire_show", options={"expose"=true})
     * @Method("GET")
     * @Template("InnovaSelfBundle:Editor:index.html.twig")
     */
    public function showAction($questionnaireId)
    {

        $em = $this->entityManager;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $typologies = $em->getRepository('InnovaSelfBundle:Typology')->findAll();
        $status = $em->getRepository('InnovaSelfBundle:Status')->findAll();

        if (!$questionnaire) {
            throw $this->createNotFoundException('Unable to find Questionnaire entity ! ');
        }

        return array(
            'questionnaire' => $questionnaire,
            'typologies' => $typologies,
            'status' => $status
        );
    }

    /**
     *
     * @Route("/questionnaire/create", name="editor_questionnaire_create", options={"expose"=true})
     * @Method("POST")
     */
    public function createQuestionnaireAction()
    {

        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $this->questionnaireManager->createQuestionnaire();
        $this->questionManager->createQuestion($questionnaire);
        
        if($test = $em->getRepository('InnovaSelfBundle:Test')->find($request->get('testId'))){
            $this->orderQuestionnaireTestManager->createOrderQuestionnaireTest($test, $questionnaire);
        }

        return new JsonResponse(
            array(
                'questionnaireId' =>  $questionnaire->getId(),
            )
        );

    }

    /**
     *
     * @Route("/questionnaires/set-theme", name="editor_questionnaire_set-theme", options={"expose"=true})
     * @Method("POST")
     */
    public function setThemeAction()
    {
        $request = $this->request;
        $em = $this->entityManager;

        $questionnaireId = $request->request->get('questionnaireId');
        $theme = $request->request->get('theme');
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $msg = "";

        if ($this->questionnaireManager->isUnique($theme)) {
            $questionnaire->setTheme($theme);
            $em->persist($questionnaire);
            $em->flush();
        } else {
            $msg = "Un tâche avec le même nom existe déja";
        }
        
        $this->editorLogManager->createEditorLog("editor_edit", "theme", $questionnaire);

        return new JsonResponse(
            array(
                'theme' => $questionnaire->getTheme(),
                'msg' => $msg
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-fixed-order", name="editor_questionnaire_set-fixed-order", options={"expose"=true})
     * @Method("POST")
     */
    public function setFixedOrderAction()
    {
        $request = $this->request;
        $em = $this->entityManager;
        $questionnaireId = $request->request->get('questionnaireId');
        $isChecked = $request->request->get('isChecked');

        if ($isChecked == "true") {
            $isChecked = 1;
        } else {
            $isChecked = 0;
        }
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $questionnaire->setFixedOrder($isChecked);
        $em->persist($questionnaire);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "fixed-order", $questionnaire);

        return new JsonResponse(
            array(
                'id' => $questionnaire->getId(),
                'fixed' => $questionnaire->getFixedOrder()
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-skill", name="editor_questionnaire_set-skill", options={"expose"=true})
     * @Method("POST")
     */
    public function setSkillAction()
    {
        $request = $this->request;
        $em = $this->entityManager;
        $questionnaireId = $request->request->get('questionnaireId');
        $skillName = $request->request->get('skill');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        if (!$skill = $em->getRepository('InnovaSelfBundle:Skill')->findOneByName($skillName)) {
            $skill = null;
        }

        $questionnaire->setSkill($skill);
        $em->persist($questionnaire);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "skill", $questionnaire);

        return new JsonResponse(
            array(
                'skill' => $skill,
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-level", name="editor_questionnaire_set-level", options={"expose"=true})
     * @Method("POST")
     */
    public function setLevelAction()
    {
        $request = $this->request;
        $em = $this->entityManager;
        $questionnaireId = $request->request->get('questionnaireId');
        $levelName = $request->request->get('level');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        if (!$level = $em->getRepository('InnovaSelfBundle:Level')->findOneByName($levelName)) {
            $level = null;
        }
        $questionnaire->setLevel($level);
        $em->persist($questionnaire);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "level", $questionnaire);

        return new JsonResponse(
            array(
                'level' => $level,
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-typology", name="editor_questionnaire_set-typology", options={"expose"=true})
     * @Method("POST")
     */
    public function setTypologyAction()
    {
        $request = $this->request;
        $questionnaireId = $request->request->get('questionnaireId');
        $typologyName = $request->request->get('typology');

        $em = $this->entityManager;
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);

        $typology = $this->questionnaireManager->setTypology($questionnaire, $typologyName);

        $typologyName = "-";
        if ($typology = $questionnaire->getQuestions()[0]->getTypology()) {
            $typologyName = $typology->getName();
        }

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        $this->editorLogManager->createEditorLog("editor_edit", "typology", $questionnaire);

        return new JsonResponse(
            array(
                'typology'=> $typologyName,
                'subquestions' => $template
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-status", name="editor_questionnaire_set-status", options={"expose"=true})
     * @Method("POST")
     */
    public function setStatusAction()
    {
        $request = $this->request;
        $questionnaireId = $request->request->get('questionnaireId');
        $statusId = $request->request->get('status');

        $em = $this->entityManager;
        $status = $em->getRepository('InnovaSelfBundle:Status')->find($statusId);
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);

        $questionnaire->setStatus($status);
        $em->persist($questionnaire);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "status", $questionnaire);

        return new JsonResponse(
            array(
                'status'=> $statusId,
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/set-text-type", name="set-text-type", options={"expose"=true})
     * @Method("POST")
     */
    public function setTextTypeAction()
    {
        $em = $this->entityManager;
        $request = $this->request;

        $questionnaireId = $request->request->get('questionnaireId');
        $textType = $request->request->get('textType');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $questionnaire->setDialogue($textType);
        $em->persist($questionnaire);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "text-type", $questionnaire);

        $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:texte.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }

}
