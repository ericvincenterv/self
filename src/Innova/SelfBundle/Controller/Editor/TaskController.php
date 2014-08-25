<?php

namespace Innova\SelfBundle\Controller\Editor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class TaskController
 * @Route(
 *      "admin/editor",
 *      name    = "",
 *      service = "innova_editor_task"
 * )
 */

class TaskController
{

    protected $questionnaireManager;
    protected $questionManager;
    protected $orderQuestionnaireTestManager;
    protected $testManager;
    protected $entityManager;
    protected $request;
    protected $templating;

    public function __construct(
            $questionnaireManager,
            $questionManager,
            $orderQuestionnaireTestManager,
            $testManager,
            $entityManager,
            $templating
    )
    {
        $this->questionnaireManager = $questionnaireManager;
        $this->questionManager = $questionManager;
        $this->orderQuestionnaireTestManager = $orderQuestionnaireTestManager;
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
}
