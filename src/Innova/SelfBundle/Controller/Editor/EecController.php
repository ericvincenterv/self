<?php

namespace Innova\SelfBundle\Controller\Editor;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Innova\SelfBundle\Entity\Clue;

/**
 * Class EecController
 * @Route(
 *      "admin/editor",
 *      name    = "",
 *      service = "innova_editor_eec"
 * )
 */
class EecController
{
    protected $propositionManager;
    protected $questionManager;
    protected $subquestionManager;
    protected $mediaManager;
    protected $entityManager;
    protected $editorLogManager;
    protected $request;
    protected $templating;

    public function __construct(
            $mediaManager,
            $propositionManager,
            $questionManager,
            $subquestionManager,
            $editorLogManager,
            $entityManager,
            $templating
    ) {
        $this->mediaManager = $mediaManager;
        $this->propositionManager = $propositionManager;
        $this->questionManager = $questionManager;
        $this->subquestionManager = $subquestionManager;
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
     *
     * @Route("/questionnaires/create-liste", name="editor_questionnaire_create-liste", options={"expose"=true})
     * @Method("PUT")
     */
    public function createListeAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        // récupération des medias des distracteurs
        $i = 0;
        $distractors = array();
        foreach ($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion) {
            $distractors[$i] = array();
            foreach ($subquestion->getPropositions() as $proposition) {
                if ($proposition->getMedia()->getMediaPurpose()->getName() == "distractor") {
                    $distractors[$i][] = $proposition->getMedia();
                }
            }
            $i++;
        }

        $question = $this->questionManager->removeSubquestions($questionnaire->getQuestions()[0]);

        if ($questionnaire->getMediaBlankText()) {
            $this->editorLogManager->createEditorLog("editor_create", "words-list", $questionnaire);
            $texte = $questionnaire->getMediaBlankText()->getDescription();

            preg_match_all("/#(.*?)#/", $texte, $lacunes);

            $countLacunes = count($lacunes[1]);
            for ($i=0; $i < $countLacunes; $i++) {
                $lacune = $lacunes[1][$i];
                $subquestion = $this->subquestionManager->createSubquestion($question->getTypology(), $question);
                $lacuneMedia = $this->mediaManager->createMedia($questionnaire, "texte", $lacune, $lacune, null, 0, "proposition");
                $this->propositionManager->createProposition($subquestion, $lacuneMedia, true);

                if ($question->getTypology()->getName() == "TLCMLDM") {
                    for ($j=0; $j < $countLacunes; $j++) {
                        if ($j != $i) {
                            $lacune = $lacunes[1][$j];
                            $lacuneMedia = $this->mediaManager->createMedia($questionnaire, "texte", $lacune, $lacune, null, 0, "proposition");
                            $this->propositionManager->createProposition($subquestion, $lacuneMedia, false);
                        }
                    }
                }
                // réinjection des distracteurs.
                if(!empty($distractors[$i])){
                    foreach ($distractors[$i] as $media) {
                        $this->propositionManager->createProposition($subquestion, $media, false);
                    }
                }

                $em->persist($lacuneMedia);
                $em->refresh($subquestion);
            }
            
            $em->flush();
        }

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/create-lacunes", name="editor_questionnaire_create-lacunes", options={"expose"=true})
     * @Method("PUT")
     */
    public function createLacunesAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        // récupération des indices et syllabes
        $i = 0;
        $clues = array();
        $syllables = array();
        foreach ($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion) {
            $clues[$i] = $subquestion->getClue();
            $syllables[$i] = $subquestion->getMediaSyllable();
            $i++;
        }

        $question = $this->questionManager->removeSubquestions($questionnaire->getQuestions()[0]);
        if ($questionnaire->getMediaBlankText()) {
            $this->editorLogManager->createEditorLog("editor_create", "blanks", $questionnaire);
            $texte = $questionnaire->getMediaBlankText()->getDescription();

            preg_match_all("/#(.*?)#/", $texte, $lacunes);

            $countLacunes = count($lacunes[1]);
            for ($i=0; $i < $countLacunes; $i++) {
                $lacune = $lacunes[1][$i];
                $subquestion = $this->subquestionManager->createSubquestion($question->getTypology(), $question);
                $lacuneMedia = $this->mediaManager->createMedia($questionnaire, "texte", $lacune, $lacune, null, 0, "proposition");
                $em->persist($lacuneMedia);
                $this->propositionManager->createProposition($subquestion, $lacuneMedia, true);
                $em->refresh($subquestion);

                // réinjection des indices et syllabes
                if(!empty($clues[$i])){
                    $subquestion->setClue($clues[$i]);    
                    $em->persist($subquestion);
                }

                 if(!empty($syllables[$i])){
                    $subquestion->setMediaSyllable($syllables[$i]);    
                    $em->persist($subquestion);
                }
            }
            $em->flush();
        }

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig', array('questionnaire' => $questionnaire));

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/create-clue", name="editor_questionnaire_create-clue", options={"expose"=true})
     * @Method("PUT")
     */
    public function createClueAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->find($request->get('subquestionId'));
        $clueName = $request->get('clue');

        if ($clueName == "" && $clue = $subquestion->getClue())  {
             $subquestion->setClue(null);
        } else {
            if (!$clue = $subquestion->getClue()) {
                $clue = new Clue();
                $clue->setClueType($em->getRepository('InnovaSelfBundle:ClueType')->findOneByName("fonctionnel"));

                $subquestion->setClue($clue);
                $em->persist($clue);
                $em->persist($subquestion);
                $this->editorLogManager->createEditorLog("editor_create", "clue", $questionnaire);
            }

            if (!$media = $subquestion->getClue()->getMedia()) {
                $media = $this->mediaManager->createMedia($questionnaire, "texte", $clueName, $clueName, null, 0, "clue");
                $clue->setMedia($media);
                $em->persist($clue);
            } elseif($media->getDescription() != $clueName) {
                $this->editorLogManager->createEditorLog("editor_edit", "clue", $questionnaire);
                $media->setDescription($clueName);
                $media->setName($clueName);
                $em->persist($media);
            }
        }

        $em->flush();

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/set-clue-type", name="editor_questionnaire_set-clue-type", options={"expose"=true})
     * @Method("PUT")
     */
    public function setClueTypeAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $clueId = $request->get('clueId');
        $clueTypeName = $request->get('clueType');
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        $clue = $em->getRepository('InnovaSelfBundle:Clue')->find($clueId);
        $clue->setClueType($em->getRepository('InnovaSelfBundle:ClueType')->findOneByName($clueTypeName));
        $this->editorLogManager->createEditorLog("editor_edit", "clue-type", $questionnaire);

        $em->persist($clue);
        $em->flush();

        return new JsonResponse(
            array()
        );
    }


    /**
     *
     * @Route("/questionnaires/create-syllable", name="editor_questionnaire_create-syllable", options={"expose"=true})
     * @Method("PUT")
     */
    public function createSyllableAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->find($request->get('subquestionId'));
        $syllable = $request->get('syllable');

        if (!$syllableMedia = $subquestion->getMediaSyllable()) {
            $syllableMedia = $this->mediaManager->createMedia($questionnaire, "texte", $syllable, $syllable, null, 0, "syllable");
            $this->editorLogManager->createEditorLog("editor_create", "syllable", $questionnaire);
        } else {
            $syllableMedia->setDescription($syllable);
            $syllableMedia->setName($syllable);
            $em->persist($syllableMedia);
            $this->editorLogManager->createEditorLog("editor_edit", "syllable", $questionnaire);
        }
        $subquestion->setMediaSyllable($syllableMedia);
        $em->persist($subquestion);
        $em->flush();

        return new JsonResponse(
            array()
        );
    }

    /**
     *
     * @Route("/questionnaires/set-display", name="editor_questionnaire_set-display", options={"expose"=true})
     * @Method("PUT")
     */
    public function setDisplayAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        if ($request->get('display') == "true") {
            $display = 1;
        } else {
            $display = 0;
        }

        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->find($request->get('subquestionId'));
        $subquestion->setDisplayAnswer($display) ;

        $em->persist($subquestion);
        $em->flush();

        return new JsonResponse(
            array()
        );
    }

    /**
     *
     * @Route("/questionnaires/add-distractor", name="editor_questionnaire_add-distractor", options={"expose"=true})
     * @Method("PUT")
     */
    public function addDistractorAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));

        $media = $this->mediaManager->createMedia($questionnaire, "texte", "", "", null, 0, "distractor");
        $this->editorLogManager->createEditorLog("editor_create", "distractor", $questionnaire);

        foreach($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion){

            $this->propositionManager->createProposition($subquestion, $media, false);

            $em->persist($subquestion);
            $em->refresh($subquestion);
        }

        $em->flush();

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/add-distractor-mult", name="editor_questionnaire_add-distractor-mult", options={"expose"=true})
     * @Method("PUT")
     */
    public function addDistractorMultAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->find($request->get('subquestionId'));

        $media = $this->mediaManager->createMedia($questionnaire, "texte", "", "", null, 0, "distractor");
        $this->editorLogManager->createEditorLog("editor_create", "distractor", $questionnaire);

        $this->propositionManager->createProposition($subquestion, $media, false);

        $em->persist($subquestion);
        $em->refresh($subquestion);
        $em->flush();

        $template = $this->templating->render('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/edit-distractor", name="editor_questionnaire_edit-distractor", options={"expose"=true})
     * @Method("PUT")
     */
    public function editDistractorAction()
    {
        $em = $this->entityManager;
        $request = $this->request->request;

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($request->get('questionnaireId'));
        $media = $em->getRepository('InnovaSelfBundle:Media')->find($request->get('mediaId'));
        $text = $request->get('text');

        $media->setDescription($text);
        $media->setName($text);
        $em->persist($media);
        $em->flush();

        $this->editorLogManager->createEditorLog("editor_edit", "distractor", $questionnaire);

        return new JsonResponse(
            array()
        );
    }
}
