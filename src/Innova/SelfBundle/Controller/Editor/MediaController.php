<?php

namespace Innova\SelfBundle\Controller\Editor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class MediaController
 * @Route(
 *      "admin/editor",
 *      name    = "",
 *      service = "innova_editor_media"
 * )
 */
class MediaController
{
    protected $mediaManager;
    protected $propositionManager;
    protected $appManager;
    protected $commentManager;
    protected $editorLogManager;
    protected $entityManager;
    protected $request;
    protected $templating;

    public function __construct(
            $mediaManager,
            $propositionManager,
            $appManager,
            $commentManager,
            $editorLogManager,
            $entityManager,
            $templating
    ) {
        $this->mediaManager = $mediaManager;
        $this->propositionManager = $propositionManager;
        $this->appManager = $appManager;
        $this->commentManager = $commentManager;
        $this->editorLogManager = $editorLogManager;
        $this->entityManager = $entityManager;
        $this->templating = $templating;

    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @Route("/set-listening-limit", name="set-listening-limit", options={"expose"=true})
     * @Method("POST")
     */
    public function setListeningLimitAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $media = $em->getRepository('InnovaSelfBundle:Media')->find($request->get('mediaId'));
        $limit = $request->get('listeningLimit');

        $this->mediaManager->updateMediaLimit($questionnaire, $media, $limit);
        $this->editorLogManager->createEditorLog("editor_edit", "listening-limit", $questionnaire);

        return new JsonResponse(
            array()
        );
    }

    /**
     * @Route("/get-media-info", name="get-media-info", options={"expose"=true})
     * @Method("GET")
     */
    public function getMediaInfoAction()
    {
        $em = $this->entityManager;
        $request = $this->request->query;

        $media = $em->getRepository('InnovaSelfBundle:Media')->find($request->get('mediaId'));

        return new JsonResponse(
            array(
                'url' => $media->getUrl(),
                'name' => $media->getName(),
                'description' => $media->getDescription(),
                'mediaType' => $media->getMediaType()->getName(),
                'id' => $media->getId()
            )
        );
    }

    /**
     * @Route("/editor_questionnaire_update-media", name="editor_questionnaire_update-media", options={"expose"=true})
     * @Method("PUT")
     */
    public function updateMediaAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;
        $toBeReloaded = $request->get('toBeReloaded');
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        $media = $em->getRepository('InnovaSelfBundle:Media')->find($request->get('mediaId'));

        $media->setUrl($request->get('url'));
        $media->setName($request->get('name'));
        $media->setDescription($request->get('description'));

        $em->persist($media);
        $em->flush();

        switch ($toBeReloaded) {
            case 'contexte':
                $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:contexte.html.twig',array('questionnaire' => $questionnaire));
                break;
            case 'texte':
                $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:texte.html.twig',array('questionnaire' => $questionnaire));
                break;
            case 'functional-instruction':
                $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:functionalInstruction.html.twig',array('questionnaire' => $questionnaire));
                break;
            case 'feedback':
                $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:feedback.html.twig',array('questionnaire' => $questionnaire));
                break;
            case 'subquestion':
                $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));
                break;
        }

        $this->editorLogManager->createEditorLog("editor_edit", $media->getMediaPurpose()->getName(), $questionnaire);

        return new Response($template);
    }


    /**
     * @Route("/questionnaires/create-media", name="editor_questionnaire_create-media", options={"expose"=true})
     * @Method("PUT")
     */
    public function createMediaAction()
    {
        $em = $this->entityManager;

        $request = $this->request->request;
        $entityType = $request->get('entityType');
        $entityId = $request->get('entityId');
        $entityField = $request->get('entityField');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $media = $this->mediaManager->createMedia($questionnaire, $request->get('type'), $request->get('name'), $request->get('description'), $request->get('url'), 0, $entityField);

        $template = "";
        switch ($entityType) {
            case "questionnaire":
                $entity =  $em->getRepository('InnovaSelfBundle:Questionnaire')->findOneById($entityId);
                if ($entityField == "contexte") {
                    $entity->setMediaContext($media);
                    $em->persist($entity);
                    $em->flush();
    
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:contexte.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "texte") {
                    $entity->setMediaText($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:texte.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "functional-instruction") {
                    $entity->setMediaFunctionalInstruction($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:functionalInstruction.html.twig',array('questionnaire' => $entity));
                }  elseif ($entityField == "feedback") {
                    $entity->setMediaFeedback($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:feedback.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "instruction") {
                    $entity->setMediaInstruction($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "blank-text") {
                    $entity->setMediaBlankText($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $entity));
                }
                elseif ($entityField == "comment") {
                    $comment = $this->commentManager->createComment($questionnaire, $media);
                    $em->refresh($questionnaire);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:comments.html.twig',array('questionnaire' => $entity));
                }

                break;
            case "subquestion":
                $entity =  $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                if ($entityField == "amorce") {
                    $entity->setMediaAmorce($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestion.html.twig',array('questionnaire' => $questionnaire, 'subquestion' => $entity));
                } elseif ($entityField == "app-media") {
                    $entity->setMedia($media);
                    $em->persist($entity);
                    $em->flush();

                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));
                }
                break;
            case "proposition":
                if ($entityField == "app-answer") {
                    $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                    $proposition = $this->propositionManager->createProposition($subquestion, $media, true);
                    $this->appManager->createAppFakeAnswer($proposition);

                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));
                } elseif ($entityField == "app-distractor") {
                    $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->findOneById($entityId);
                    $subquestions = $questionnaire->getQuestions()[0]->getSubquestions();
                    foreach ($subquestions as $subquestion) {
                        $proposition = $this->propositionManager->createProposition($subquestion, $media, false);
                    }
                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));
                } else {
                    $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                    $proposition = $this->propositionManager->createProposition($subquestion, $media, false);

                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestion.html.twig',array('questionnaire' => $questionnaire, 'subquestion' => $subquestion));
                }
                break;
        }
        $this->editorLogManager->createEditorLog("editor_create", $entityField, $questionnaire);

        return new Response($template);
    }

    /**
     * Supprime la relation qu'a un media avec un élément (ou supprime l'élément)... il faudrait supprimer le media dans certains cas.
     *
     * @Route("/questionnaires/unlink-media", name="editor_questionnaire_unlink-media", options={"expose"=true})
     * @Method("DELETE")
     */
    public function unlinkMediaAction()
    {
        $request = $this->request->request;
        $em = $this->entityManager;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        $entityType = $request->get('entityType');
        $entityId = $request->get('entityId');
        $entityField = $request->get('entityField');

        $template = "";
        switch ($entityType) {
            case "questionnaire":
                $entity =  $em->getRepository('InnovaSelfBundle:Questionnaire')->findOneById($entityId);
                if ($entityField == "contexte") {
                    $entity->setMediaContext(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:contexte.html.twig', array('questionnaire' => $entity));
                } elseif ($entityField == "texte") {
                    $entity->setMediaText(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:texte.html.twig', array('questionnaire' => $entity));
                } elseif ($entityField == "feedback") {
                    $entity->setMediaFeedback(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:feedback.html.twig', array('questionnaire' => $entity));
                } elseif ($entityField == "blank-text") {
                    $entity->setMediaBlankText(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $entity));
                }  elseif ($entityField == "functional-instruction") {
                    $entity->setMediaFunctionalInstruction(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:functionalInstruction.html.twig', array('questionnaire' => $entity));
                } elseif ($entityField == "instruction") {
                    $entity->setMediaInstruction(null);
                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $entity));
                }

                $em->persist($entity);
                $em->flush();
                break;
            case "subquestion":
                $entity =  $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                if ($entityField == "amorce") {
                    $entity->setMediaAmorce(null);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:subquestion.html.twig', array('questionnaire' => $questionnaire, 'subquestion' => $entity));
                } elseif ($entityField == "app-paire") {
                    if ($rightProposition = $em->getRepository('InnovaSelfBundle:Proposition')->findOneBy(array("subquestion" => $entity, "rightAnswer" => true))) {
                        $mediaToSearch = $rightProposition->getMedia();
                        $question = $entity->getQuestion();
                        $this->appManager->appDeletePropositions($mediaToSearch, $question);
                    }

                    $em->remove($entity);
                    $em->flush();

                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $questionnaire));
                }
                break;
            case "proposition":
                $proposition =  $em->getRepository('InnovaSelfBundle:Proposition')->findOneById($entityId);

                if ($entityField == "app-distractor") {
                    
                    $mediaToSearch = $proposition->getMedia();
                    $question = $proposition->getSubquestion()->getQuestion();
                    $this->appManager->appDeletePropositions($mediaToSearch, $question);

                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $questionnaire));
                }  elseif ($entityField == "distractor") {
                    $question = $proposition->getSubquestion()->getQuestion();
                    $media = $proposition->getMedia();
                    foreach ($question->getSubquestions() as $subquestion) {
                        foreach ($subquestion->getPropositions() as $needle) {
                            if ($needle->getMedia() == $media) {
                                $em->remove($needle);
                            }
                        }
                    }
                    $em->remove($media);
                    $em->flush();
                    $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $questionnaire));
                } else {
                    $em->remove($proposition);
                    $em->flush();

                    $template =  $this->templating->render('InnovaSelfBundle:Editor/partials:proposition.html.twig', array('questionnaire' => $questionnaire, 'proposition' => null));
                }
                break;
        }

        $this->editorLogManager->createEditorLog("editor_delete", $entityField, $questionnaire);

        return new Response($template);
    }
}
