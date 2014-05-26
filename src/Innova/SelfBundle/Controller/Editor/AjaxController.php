<?php

namespace Innova\SelfBundle\Controller\Editor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Innova\SelfBundle\Entity\Media;
use Innova\SelfBundle\Entity\Subquestion;
use Innova\SelfBundle\Entity\Proposition;
/**
 * Main controller.
 *
 * @Route("admin/editor/ajax")
 */
class AjaxController extends Controller
{

    /**
     *
     * @Route("/edit-media", name="edit-media", options={"expose"=true})
     * @Method("GET")
     */
    public function editMedia()
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->container->get('request');
        $mediaId = $request->query->get('id');
        
        $media = $em->getRepository('InnovaSelfBundle:Media')->findOneById($mediaId);

        return new JsonResponse(
            array(
                'id' => $media->getId(),
                'type' => $media->getMediaType()->getName(),
                'description' => $media->getDescription(),
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
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $questionnaireId = $request->request->get('questionnaireId');
        $theme = $request->request->get('theme');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $questionnaire->setTheme($theme);
        $em->persist($questionnaire);
        $em->flush();

        return new JsonResponse(
            array(
                'theme' => $questionnaire->getTheme(),
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
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $questionnaireId = $request->request->get('questionnaireId');
        $skillName = $request->request->get('skill');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        if (!$skill = $em->getRepository('InnovaSelfBundle:Skill')->findOneByName($skillName)){
            $skill = null;
        }

        $questionnaire->setSkill($skill);
        $em->persist($questionnaire);
        $em->flush();

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
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $questionnaireId = $request->request->get('questionnaireId');
        $levelName = $request->request->get('level');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        if(!$level = $em->getRepository('InnovaSelfBundle:Level')->findOneByName($levelName)){
            $level = null;
        }
        $questionnaire->setLevel($level);
        $em->persist($questionnaire);
        $em->flush();

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
        /* $msg = ""; */
        $request = $this->get('request');
        $questionnaireId = $request->request->get('questionnaireId');
        $typologyName = $request->request->get('typology');

        $em = $this->getDoctrine()->getManager();
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);

        if(!$typology = $em->getRepository('InnovaSelfBundle:Typology')->findOneByName($typologyName)){
            $typology = null;
            foreach($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion){
                $subquestion->setTypology(null);
                $em->persist($subquestion);
            }
        } else {
            if(mb_substr($typology->getName(), 0, 3) == "APP"){
                foreach($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion){
                    $subquestion->setTypology($typology);
                    $em->persist($subquestion);
                }

            } else {
                $typologySubquestion = $em->getRepository('InnovaSelfBundle:Typology')->findOneByName(mb_substr($typologyName, 1));
                foreach($questionnaire->getQuestions()[0]->getSubquestions() as $subquestion){
                    $subquestion->setTypology($typologySubquestion);
                    $em->persist($subquestion);
                }
            }
        }

        /*
        // on teste s'il n'y a pas de subquestion 
        // (pour éviter un conflit entre la typo de la question et des subq)
        if(count($questionnaire->getQuestions()[0]->getSubquestions()) > 0 ){
            $msg = "Vous ne pouvez pas éditer la typologie s'il y a déjà des subquestions !";
        } else {
        */
            $questionnaire->getQuestions()[0]->setTypology($typology);
            $em->persist($questionnaire);
            $em->flush();
        /*
        }
        */

        $typologyName = "-";
        if($typology = $questionnaire->getQuestions()[0]->getTypology()){
            $typologyName = $typology->getName();
        }

        return new JsonResponse(
            array(
                /*'msg' => $msg,*/
                'typology'=> $typologyName,
            )
        );
    }



     /**
     *
     * @Route("/questionnaires/upload-image", name="editor_questionnaire_upload-image", options={"expose"=true})
     * @Method("POST")
     */
    public function uploadImageAction()
    {
        $request = $this->get('request');

        foreach($request->files as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION); 
            $newName = uniqid(). "." . $ext;

            $directory = __DIR__.'/../../../../../web/upload/media/';
            $file = $uploadedFile->move($directory, $newName);
        }

        return new JsonResponse(
            array(
                'url' => $newName,
            )
        );
    }

    /**
     *
     * @Route("/questionnaires/upload-audio", name="editor_questionnaire_upload-audio", options={"expose"=true})
     * @Method("POST")
     */
    public function uploadAudioAction()
    {
        $request = $this->get('request');

        foreach($request->files as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION); 
            $newName = uniqid();

            //convertir en ogg
            $directory = __DIR__.'/../../../../../web/upload/media/';
            $file = $uploadedFile->move($directory, $newName.".".$ext);
        }

        return new JsonResponse(
            array(
                'url' => $newName,
            )
        );
    }


    /**
     *
     * @Route("/questionnaires/upload-video", name="editor_questionnaire_upload-video", options={"expose"=true})
     * @Method("POST")
     */
    public function uploadVideoAction()
    {
        $request = $this->get('request');
        

        foreach($request->files as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION); 

            // tester si ext == webm
            $newName = uniqid();

            $directory = __DIR__.'/../../../../../web/upload/media/';
            $file = $uploadedFile->move($directory, $newName);
        }

        return new JsonResponse(
            array(
                'url' => $newName,
            )
        );
    }



    /**
     * 
     *
     * @Route("/questionnaires/create-media", name="editor_questionnaire_create-media", options={"expose"=true})
     * @Method("POST")
     */
    public function CreateMediaAction()
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        /* Création du nouveau media */
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $url = $request->request->get('url');
        $type = $em->getRepository('InnovaSelfBundle:MediaType')->findOneByName($request->request->get('type'));

        $media = new Media;
        $media->setMediaType($type);
        $media->setName($name);
        $media->setDescription($description);
        $media->setUrl($url);

        $em->persist($media);
        $em->flush();

        /* Création de la relation avec une entité */
        $entityType = $request->request->get('entityType');
        $entityId = $request->request->get('entityId');
        $entityField = $request->request->get('entityField');

        $template = "";
        switch ($entityType) {
            case "questionnaire": 
                $entity =  $em->getRepository('InnovaSelfBundle:Questionnaire')->findOneById($entityId);
                if ($entityField == "contexte"){
                    $entity->setMediaContext($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:contexte.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "texte") {
                    $entity->setMediaText($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:texte.html.twig',array('questionnaire' => $entity));
                } 
                break;
            case "subquestion":
                $entity =  $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                if ($entityField == "amorce"){
                    $entity->setMediaAmorce($media);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:subquestion.html.twig',array('subquestion' => $entity));
                }
                break;
            case "proposition":
                $entity = $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                $proposition = new Proposition;
                $proposition->setSubquestion($entity);
                $proposition->setMedia($media);
                $proposition->setRightAnswer(false);
                $em->persist($proposition);
                $em->persist($entity);
                $em->flush();

                $template =  $this->renderView('InnovaSelfBundle:Editor/partials:subquestion.html.twig',array('subquestion' => $entity));
                break;
        }

        return new Response($template);
    }

    /**
     * 
     *
     * @Route("/questionnaires/unlink-media", name="editor_questionnaire_unlink-media", options={"expose"=true})
     * @Method("POST")
     */
    public function UnlinkMediaAction()
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        $entityType = $request->request->get('entityType');
        $entityId = $request->request->get('entityId');
        $entityField = $request->request->get('entityField');

        $template = "";
        switch ($entityType) {
            case "questionnaire": 
                $entity =  $em->getRepository('InnovaSelfBundle:Questionnaire')->findOneById($entityId);
                if ($entityField == "contexte"){
                    $entity->setMediaContext(null);
                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:contexte.html.twig',array('questionnaire' => $entity));
                } elseif ($entityField == "texte") {
                    $entity->setMediaText(null);
                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:texte.html.twig',array('questionnaire' => $entity));
                }
                $em->persist($entity);
                $em->flush();
                break;
            case "subquestion":
                $entity =  $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($entityId);
                if ($entityField == "amorce"){
                    $entity->setMediaAmorce(null);
                    $em->persist($entity);
                    $em->flush();

                    $template =  $this->renderView('InnovaSelfBundle:Editor/partials:subquestion.html.twig',array('subquestion' => $entity));
                }
                break;
            case "proposition":
                $entity =  $em->getRepository('InnovaSelfBundle:Proposition')->findOneById($entityId);
                $subquestion = $entity->getSubquestion();
                $em->remove($entity);
                $em->flush();

                $template =  $this->renderView('InnovaSelfBundle:Editor/partials:proposition.html.twig',array('proposition' => null));

                break;
        }

        return new Response($template);
    }

    /**
     *
     * @Route("/questionnaires/create-subquestion", name="editor_questionnaire_create-subquestion", options={"expose"=true})
     * @Method("POST")
     */
    public function createSubquestionAction()
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $questionnaireId = $request->request->get('questionnaireId');
        $questionnaireTypology = $request->request->get('questionnaireTypology');

        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        $question = $questionnaire->getQuestions()[0];

        if(mb_substr($questionnaireTypology, 0, 3) == "APP"){
            $typology = $em->getRepository('InnovaSelfBundle:Typology')->findOneByName($questionnaireTypology);
        } else {
            $typology = $em->getRepository('InnovaSelfBundle:Typology')->findOneByName(mb_substr($questionnaireTypology, 1));
        }

        $subquestion = new Subquestion();
        $subquestion->setTypology($typology);
        $subquestion->setQuestion($question);

        $em->persist($subquestion);
        $em->flush();


        $template = $this->renderView('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }


    /**
     *
     * @Route("/questionnaires/delete-subquestion", name="editor_questionnaire_delete_subquestion", options={"expose"=true})
     * @Method("POST")
     */
    public function deleteSubquestionAction()
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $subquestionId = $request->request->get('subquestionId');
        $questionnaireId = $request->request->get('questionnaireId');

        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->find($subquestionId);
        $questionnaire = $em->getRepository('InnovaSelfBundle:Questionnaire')->find($questionnaireId);
        
        $em->remove($subquestion);
        $em->flush();

        $template = $this->renderView('InnovaSelfBundle:Editor/partials:subquestions.html.twig',array('questionnaire' => $questionnaire));

        return new Response($template);
    }



    /**
     *
     * @Route("/questionnaires/toggle_right_answer", name="editor_questionnaire_toggle_right_anwser", options={"expose"=true})
     * @Method("POST")
     */
    public function toggleRightAnswserAction()
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        $propositionId = $request->request->get('propositionId');
        $proposition = $em->getRepository('InnovaSelfBundle:Proposition')->find($propositionId);

        if($proposition->getRightAnswer() == true){
            $proposition->setRightAnswer(false);
        } else {
            $proposition->setRightAnswer(true);
        }
       
        $em->persist($proposition);
        $em->flush();

        $template = $this->renderView('InnovaSelfBundle:Editor/partials:proposition.html.twig',array('proposition' => $proposition));

        return new Response($template);
    }

}
