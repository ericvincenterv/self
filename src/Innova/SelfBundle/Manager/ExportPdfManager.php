<?php

namespace Innova\SelfBundle\Manager;

use Innova\SelfBundle\Entity\Test;
use Innova\SelfBundle\Entity\User;
use Innova\SelfBundle\Entity\Subquestion;
use Symfony\Component\Filesystem\Filesystem;

class ExportPdfManager
{
    protected $entityPdfManager;
    protected $securityContext;
    protected $kernelRoot;
    protected $user;

    public function __construct($entityPdfManager, $securityContext, $kernelRoot)
    {
        $this->entityPdfManager = $entityPdfManager;
        $this->securityContext = $securityContext;
        $this->kernelRoot = $kernelRoot;
        $this->user = $this->securityContext->getToken()->getUser();
    }

    public function exportPdfAction(Test $test)
    {
        $fs = new Filesystem();
        $testId = $test->getId();
        $pdfContent = "";

//        $pdfContent = $this->getPdfContent($test);
        $pdfContent = "titi";
        $pdfName = "self_export-pdf-test_" . $testId . "-" . date("d-m-Y_H:i:s") . '.pdf';

        $pdfPathExport = $this->kernelRoot ."/data/export/".$testId."/";

        $fs->mkdir($pdfPathExport, 0777);
        $pdfh = fopen($pdfPathExport . "/" . $pdfName, 'w+');

        fwrite($pdfh, $pdfContent);
        fclose($pdfh);
        return $pdfName;
    }

    public function getFileList(Test $test)
    {
        $testId = $test->getId();
        $pdfPathExport = $this->kernelRoot ."/data/exportPdf/".$testId."/";
        $fileList = array();

        if ($dossier = opendir($pdfPathExport)) {
            while (false !== ($fichier = readdir($dossier))) {
                if ($fichier != '.' && $fichier != '..') {
                    $fileList[] = $fichier;
                }
            }
        }

        closedir($dossier);
        arsort($fileList);

        return $fileList;
    }

    private function getPdfContent(Test $test){
        $em = $this->entityPdfManager;
        $csv = "";
        $result = array();
        $questionnaires = $em->getRepository('InnovaSelfBundle:Questionnaire')->getByTest($test);
        foreach ($questionnaires as $questionnaire) {
            $traces = $questionnaire->getTraces();
            foreach ($traces as $trace) {
                $userId  = $trace->getUser()->getId();
                $userName  = (string) $trace->getUser();
                $emailName = (string) $trace->getUser()->getEmail();
                $testDate  = date_format($trace->getDate(), 'dm');
                if (!isset($result[$userId]["time"])) {
                    $result[$userId]["time"]=0;
                }
                $result[$userId]["time"] = $result[$userId]["time"] + $trace->getTotalTime();
                $result[$userId]["name"]  = $userName;
                $result[$userId]["email"] = $emailName;
                $result[$userId]["date"]  = $testDate;
            }
        }
        /* Partie enlevée pour le pilote 3.
        $csv .= "\n";

        $csv .= "Difficulté" . ";" ;
        $csv .= "Libellé" . ";" ;
        $csv .= "\n";
        $csv .= "1" . ";" ;
        $csv .= "Très facile" . ";" ;
        $csv .= "\n";
        $csv .= "2" . ";" ;
        $csv .= "Facile" . ";" ;
        $csv .= "\n";
        $csv .= "3" . ";" ;
        $csv .= "Normal" . ";" ;
        $csv .= "\n";
        $csv .= "4" . ";" ;
        $csv .= "Difficile" . ";" ;
        $csv .= "\n";
        $csv .= "5" . ";" ;
        $csv .= "Très Difficile" . ";" ;
        $csv .= "\n";

        $csv .= "\n";
        $csv .= "\n";
        Fin partie enlevée pour le pilote 3. */

        // CSV HEADER
        $csv .= "Nom;" ; // A
        $csv .= "Prénom;" ; // B
        $csv .= "Date;" ; // C
        $csv .= "Temps en secondes (pour le test entier);" ; // D
        $csv .= "Niveau Dialang CO;" ; // E
        $csv .= "Niveau Dialang CE;" ; // F
        $csv .= "Niveau Dialang EEC;" ; // G
        $csv .= "Niveau Lansad acquis;" ; // H
        $csv .= "Score total obtenu dans le test (formule du total);" ; // I

        $cpt_questionnaire = 0;
        if ($cpt_questionnaire == 0) {
            foreach ($questionnaires as $questionnaire) {
                $cpt_questionnaire++;
                // Suite réception nouvelle version du fichier le 29/11/2013 :
                // je prends le dernier ou les 2 derniers caractères du thême
                $themeCode = substr($questionnaire->getTheme(), -2);
                // Si l'extrait est numérique, alors OK
                // sinon, je ne prends que le dernier caractère.
                // Exemple : A1COT2, je prends le dernier
                // A1COT13, je prends les 2 derniers.
                //
                if (!is_numeric($themeCode)) {
                    $themeCode = substr($questionnaire->getTheme(), -1);
                }
                $csv .= "T" . $cpt_questionnaire . " - NOM de ma TACHE;";
                $csv .= "T" . $cpt_questionnaire . " - Protocole d'interaction;";
                $csv .= "T" . $cpt_questionnaire . " - difficulté;";
                $csv .= "T" . $cpt_questionnaire . " - TEMPS;";

                $questions = $questionnaire->getQuestions();

                if(count($questions) > 0){
                    $subquestions = $questions[0]->getSubquestions();
                    $cpt=0;
                    foreach ($subquestions as $subquestion) {
                        $cpt++;
                        $csv .= "T" . $cpt_questionnaire . "_" . $cpt . " - CORR-FAUX : 1 pour correct / 0 pour faux;";
                        $csv .= "T" . $cpt_questionnaire . "_" . $cpt . " - PROPOSITION CHOISIE;";
                    }
                }
            }
        }
        $csv .= "\n";

        // CSV BODY
        $users = $em->getRepository('InnovaSelfBundle:User')->findAll();
        foreach ($users as $user) {
           if ($this ->countQuestionnaireDone($test, $user) > 0 && isset($result[$user->getId()])) {
                $csv .= $user->getUserName() . ";" ;
                $csv .= $user->getFirstName() . ";" ;
                $csv .= $result[$user->getId()]["date"] . ";" . $result[$user->getId()]["time"] . ";";
                $csv .= $user->getCoLevel() . ";";
                $csv .= $user->getCeLevel() . ";";
                $csv .= $user->getEeLevel() . ";";
                $csv .= $user->getlevelLansad() . ";";
                $csv .= $this->calculateScore($user, $test) . ";";

                $answersArray = array();

                $questionnaires = $em->getRepository('InnovaSelfBundle:Questionnaire')->getByTest($test);
                foreach ($questionnaires as $questionnaire) {
                    $traces = $em->getRepository('InnovaSelfBundle:Trace')->findBy(array('user' => $user->getId(),'questionnaire' => $questionnaire->getId()));
                    $questions = $questionnaire->getQuestions();

                    foreach ($traces as $trace) {
                        $answers = $trace->getAnswers();
                        $csv .= $questionnaire->getTheme() . ";" ;
                        $csv .= $questions[0]->getTypology()->getName() .  ";" ;
                        $csv .= $trace->getDifficulty() . ";" ;
                        $csv .= $trace->getTotalTime() . ";" ;

                        // création tableau de correspondance subquestion -> réponses
                        foreach ($answers as $answer) {
                            if (!isset ($answersArray[$answer->getProposition()->getSubQuestion()->getId()])) {
                                $answersArray[$answer->getProposition()->getSubQuestion()->getId()] = array();
                            }
                            $answersArray[$answer->getProposition()->getSubQuestion()->getId()][] = $answer->getProposition();
                        }

                        $subquestions = $questions[0]->getSubquestions();

                        foreach ($subquestions as $subquestion) {
                            $propositions = $subquestion->getPropositions();
                            $rightProps = array();
                            $nbPropositionRightAnswser = 0;
                            $cptProposition = 0;
                            $propLetters = array();
                            // on compte les bonnes propositions
                            foreach ($propositions as $proposition) {
                                $cptProposition++;
                                if ($proposition->getRightAnswer()) {
                                    $nbPropositionRightAnswser++;
                                    $rightProps[] = $proposition->getId();
                                }
                                $propLetters[$proposition->getId()] = $this->intToLetter($cptProposition);
                            }

                            // Récupération bonne réponse ou non
                            $subquestionOk = $this->checkRightAnswer($answersArray, $subquestion, $nbPropositionRightAnswser, $rightProps);
                            if ($subquestionOk) {
                                $csv .= "1" . ";";
                            } else {
                                $csv .= "0" . ";";
                            }

                            // Récupération de la saisie ou des lettres associées aux réponses
                            $textToDisplay = $this->textToDisplay($subquestion, $answersArray, $propLetters);
                            $csv .= $textToDisplay;
                            $csv .= ";";
                        }
                    }
                }
                $csv .= "\n";
            }
        }

        return $csv;
    }

    private function countQuestionnaireDone(Test $test, User $user){
        $em = $this->entityPdfManager;

        $count = $em->getRepository('InnovaSelfBundle:Questionnaire')
                ->countDoneYetByUserByTest($test->getId(), $user->getId());

        return  $count;
    }

    private function intToLetter($int){
        $arr = array(1 => "A", 2 => "B", 3 => "C", 4 => "D", 5 => "E");

        return $arr[$int];
    }

    private function checkRightAnswer($answersArray, Subquestion $subquestion, $nbPropositionRightAnswser, $rightProps)
    {
        $typo = $subquestion->getTypology()->getName();
        $subquestionId = $subquestion->getId();

        switch ($typo) {
            case 'TVF':
            case 'VF':
            case 'TVFNM':
            case 'VFNM':
            case 'QRU':
            case 'QRM':
            case 'TQRU':
            case 'TQRM':
            case 'APP':
                $nbAnswers = count($answersArray[$subquestionId]);
                $subquestionOk = true;
                if ($nbAnswers == $nbPropositionRightAnswser) {
                    foreach ($rightProps as $rightProp) {
                        $found = false;
                        foreach ($answersArray[$subquestionId] as $answerProp) {
                            if ($rightProp == $answerProp->getId()) {
                                $found = true;
                            }
                        }
                        if ($found === false) {
                            $subquestionOk = false;
                        }
                    }
                } else {
                    $subquestionOk = false;
                }
                break;

            case 'TLCMLDM':
            case 'TLCMLMULT':
            case 'TLQROC':
                $subquestionOk = true;
                $proposition = $answersArray[$subquestionId][0];
                $subquestionOk = $proposition->getRightAnswer();
                break;
        }

        return  $subquestionOk;
    }

    private function textToDisplay(Subquestion $subquestion, $answersArray, $propLetters ){
        $typo = $subquestion->getTypology()->getName();
        $subquestionId = $subquestion->getId();
        $textToDisplay = "";

        switch ($typo) {
            case 'TVF':
            case 'VF':
            case 'TVFNM':
            case 'VFNM':
            case 'QRU':
            case 'QRM':
            case 'TQRU':
            case 'TQRM':
            case 'APP':
                $letters = array();
                foreach ($answersArray[$subquestion->getId()] as $answer) {
                    $idAnswer = $answer->getId();
                    $letters[$propLetters[$idAnswer]] = 1;
                }
                ksort($letters);
                foreach ($letters as $key => $value) {
                    $textToDisplay .= $key;
                }
                break;

            case 'TLCMLDM':
            case 'TLCMLMULT':
            case 'TLQROC':
                $proposition = $answersArray[$subquestionId][0];
                $textToDisplay = $proposition->getMedia()->getDescription();
                break;
        }

        return $textToDisplay;
    }

    /**
     * calculateScore function
     *
     */
    private function calculateScore(User $user, Test $test)
    {
        $em = $this->entityPdfManager;
        $score = 0;
        $traces = $em->getRepository('InnovaSelfBundle:Trace')->findBy(array('user' => $user->getId(),'test' => $test->getId()));

        foreach ($traces as $trace) {
            $answers = $trace->getAnswers();
            $answersArray = array();
            $typology = $answers[0]->getProposition()->getSubQuestion()->getTypology()->getName();

            switch ($typology) {
                case 'TLCMLDM':
                case 'TLCMLMULT':
                case 'TLQROC':
                    //
                    // TODO : c'est le même code que pour les typologies QRU, QRM etc ...
                    // En attente de remarques des autres langues
                    // pour confirmation de ce fonctionnement.
                    // Si OK, alors ajouter les 3 nouvelles typos dans la grande liste ci-dessous.
                    // Si KO, alors modifier dans ce bloc.
                    //
                    foreach ($answers as $answer) {
                        if (!isset ($answersArray[$answer->getProposition()->getSubQuestion()->getId()])) {
                            $answersArray[$answer->getProposition()->getSubQuestion()->getId()] = array();
                        }
                        $answersArray[$answer->getProposition()->getSubQuestion()->getId()][] = $answer->getProposition()->getId();
                    }

                    foreach ($answersArray as $subquestionId => $answers) {
                        // Initialisation des variables.
                        $nbProposition = $nbPropositionRightAnswser = $nbRightAnswer = 0;
                        // Recherche de toutes les traces pour un utilisateur, un questionnaire et un test.
                        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($subquestionId);
                        $propositions = $subquestion->getPropositions();

                        // Calcul du nombre de réponses.
                        $nbAnswers = count($answers);
                        $rightProps = array();
                        // Accès à la proposition.
                        // Calcul du nombre de proposition et
                        // calcul du nombre de bonnes réponses.
                        foreach ($propositions as $proposition) {
                                $nbProposition++;
                                if ($proposition->getRightAnswer()) {
                                    $nbPropositionRightAnswser++;
                                    $rightProps[] = $proposition;
                                }
                        }

                        // Je calcule le score que si le testeur a répondu à autant de réponses
                        // qu'il y a de propositions.
                        // Si ce n'est pas le cas, il aura forcément ZERO point.
                        if ($nbAnswers == $nbPropositionRightAnswser) {
                            foreach ($rightProps as $rightProp) {
                                if (in_array($rightProp->getId(),$answersArray[$subquestionId])) {
                                        $nbRightAnswer++;
                                }
                            }
                        }

                        if (($nbPropositionRightAnswser == $nbAnswers) && ($nbAnswers == $nbRightAnswer)) {
                            $score++;
                        }
                    }
                    break;

                case "APP";
                    foreach ($answers as $answer) {
                        if ($answer->getProposition()->getRightAnswer()) {
                            $score++;
                        }
                    }
                    break;
                case "QRM";
                case "TQRM";
                case "QRU";
                case "TQRU";
                case "VF";
                case "TVF";
                case "VFNM";
                case "TVFNM";
                    foreach ($answers as $answer) {
                        if (!isset ($answersArray[$answer->getProposition()->getSubQuestion()->getId()])) {
                            $answersArray[$answer->getProposition()->getSubQuestion()->getId()] = array();
                        }
                        $answersArray[$answer->getProposition()->getSubQuestion()->getId()][] = $answer->getProposition()->getId();
                    }

                    foreach ($answersArray as $subquestionId => $answers) {
                        // Initialisation des variables.
                        $nbProposition = $nbPropositionRightAnswser = $nbRightAnswer = 0;
                        // Recherche de toutes les traces pour un utilisateur, un questionnaire et un test.
                        $subquestion = $em->getRepository('InnovaSelfBundle:Subquestion')->findOneById($subquestionId);
                        $propositions = $subquestion->getPropositions();

                        // Calcul du nombre de réponses.
                        $nbAnswers = count($answers);
                        $rightProps = array();
                        // Accès à la proposition.
                        // Calcul du nombre de proposition et
                        // calcul du nombre de bonnes réponses.
                        foreach ($propositions as $proposition) {
                                $nbProposition++;
                                if ($proposition->getRightAnswer()) {
                                    $nbPropositionRightAnswser++;
                                    $rightProps[] = $proposition;
                                }
                        }

                        // Je calcule le score que si le testeur a répondu à autant de réponses
                        // qu'il y a de propositions.
                        // Si ce n'est pas le cas, il aura forcément ZERO point.
                        if ($nbAnswers == $nbPropositionRightAnswser) {
                            foreach ($rightProps as $rightProp) {
                                if (in_array($rightProp->getId(),$answersArray[$subquestionId])) {
                                        $nbRightAnswer++;
                                }
                            }
                        }

                        if (($nbPropositionRightAnswser == $nbAnswers) && ($nbAnswers == $nbRightAnswer)) {
                            $score++;
                        }
                    }
                    break;
            }
        }

        return $score;
    }

}
