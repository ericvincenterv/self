<?php

namespace Innova\SelfBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony command to delete tests. EV. 12/03/2014
 * We must execute this command with parameter "sql" like :
 * php app/console self:delete:all sql
*/
class DeleteAllCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('self:delete:all')
            ->setDescription('Delete test SELF')
            ->addArgument('name')
           ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = $input->getArgument('name');
        if ($name != 'sql') {
            $output->writeln("Absence de paramètres. Impossible d'exécuter les requêtes.");

        } else {
            $em = $this->getContainer()->get('doctrine')->getEntityManager('default');

            $output->writeln("Suppression ANSWER ...");
            $answers = $em->getRepository('InnovaSelfBundle:Answer')->findAll();
            foreach ($answers as $answer) {
                $em->remove($answer);
            }

            $output->writeln("Suppression TRACE ...");
            $traces = $em->getRepository('InnovaSelfBundle:Trace')->findAll();
            foreach ($traces as $trace) {
                $em->remove($trace);
            }

            $output->writeln("Suppression COMMENTAIRE ...");
            $comments = $em->getRepository('InnovaSelfBundle:Comment')->findAll();
            foreach ($comments as $comment) {
                $em->remove($comment);
            }

            $output->writeln("Suppression PROPOSITION ...");
            $propositions = $em->getRepository('InnovaSelfBundle:Proposition')->findAll();
            foreach ($propositions as $proposition) {
                $em->remove($proposition);
            }

            $output->writeln("Suppression SUBQUESTION ...");
            $subquestions = $em->getRepository('InnovaSelfBundle:Subquestion')->findAll();
            foreach ($subquestions as $subquestion) {
                $em->remove($subquestion);
            }

            $output->writeln("Suppression QUESTION ...");
            $questions = $em->getRepository('InnovaSelfBundle:Question')->findAll();
            foreach ($questions as $question) {
                $em->remove($question);
            }

            $output->writeln("Suppression CLUE ...");
            $clues = $em->getRepository('InnovaSelfBundle:Clue')->findAll();
            foreach ($clues as $clue) {
                $em->remove($clue);
            }

            $output->writeln("Suppression EditorLOG ...");
            $logs = $em->getRepository('InnovaSelfBundle:EditorLog')->findAll();
            foreach ($logs as $log) {
                $em->remove($log);
            }

            $output->writeln("Suppression QUESTIONNAIRE ...");
            $questionnaires = $em->getRepository('InnovaSelfBundle:Questionnaire')->findAll();
            foreach ($questionnaires as $questionnaire) {
                $em->remove($questionnaire);
            }

            $output->writeln("Suppression MediaLimit ...");
            $limits = $em->getRepository('InnovaSelfBundle:MediaLimit')->findAll();
            foreach ($limits as $limit) {
                $em->remove($limit);
            }

            $output->writeln("Suppression ECOUTES MEDIA ...");
            $clicks = $em->getRepository('InnovaSelfBundle:MediaClick')->findAll();
            foreach ($clicks as $click) {
                $em->remove($click);
            }

            $output->writeln("Suppression MEDIA ...");
            $medias = $em->getRepository('InnovaSelfBundle:Media')->findAll();
            foreach ($medias as $media) {
                $em->remove($media);
            }

            $output->writeln("Suppression ORDER ...");
            $orders = $em->getRepository('InnovaSelfBundle:OrderQuestionnaireTest')->findAll();
            foreach ($orders as $order) {
                $em->remove($order);
            }

            $output->writeln("Suppression TEST ...");
            $tests = $em->getRepository('InnovaSelfBundle:Test')->findAll();
            foreach ($tests as $test) {
                $em->remove($test);
            }

            $em->flush();
        }

    }

}
