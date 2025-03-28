<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Entity\Partie;
use App\Repository\PlanteRepository;
use App\Repository\MoleculeRepository;
use App\Repository\CadenasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class JeuController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        return $this->render('jeu/accueil.html.twig');
    }


    #[Route('/jeu', name: 'commencer_jeu')]
    public function startGame(
        PlanteRepository $planteRepository,
        MoleculeRepository $moleculeRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        Request $request
    ): Response {

        if ($request->query->get('from') === 'accueil' || !$session->has('global_start_time')) {
            $session->set('global_start_time', time());
            $session->set('parties_jouees', 0);
            $session->set('formules_trouvees', []);
            $session->set('plantes_jouees', []);
            $session->set('score', 0);
        }


        $partiesJouees = count($session->get('plantes_jouees', []));
        $session->set('parties_jouees', $partiesJouees);


        if ($partiesJouees >= 4) {
            return $this->redirectToRoute('resultat', [
                'success' => true,
                'timeout' => false,
                'parties_jouees' => $partiesJouees,
                'score' => $session->get('score', 0)
            ]);
        }


        $globalStartTime = $session->get('global_start_time');
        $timeElapsed = time() - $globalStartTime;
        $timeRemaining = 300 - $timeElapsed;

        if ($timeRemaining <= 0) {
            return $this->redirectToRoute('resultat', [
                'success' => false,
                'timeout' => true,
                'parties_jouees' => $partiesJouees
            ]);
        }


        $plantesJouees = $session->get('plantes_jouees', []);
        $plante = $planteRepository->findRandomPlante($plantesJouees);

        if (!$plante) {
            return $this->redirectToRoute('resultat', [
                'success' => true,
                'timeout' => false,
                'parties_jouees' => $partiesJouees,
                'score' => $session->get('score', 0)
            ]);
        }

        $molecules = $moleculeRepository->findBy(['plante' => $plante->getId()]);


        $partie = new Partie();
        $partie->setPlante($plante);
        $partie->setEtat('en_cours');
        $partie->setScore(0);

        $entityManager->persist($partie);
        $entityManager->flush();


        $plantesJouees[] = $plante->getId();
        $session->set('plantes_jouees', $plantesJouees);
        $session->set('plante_id', $plante->getId());
        $session->set('partie_id', $partie->getId());
        $session->set('molecules', $molecules);


        $partiesJouees = count($plantesJouees);
        $session->set('parties_jouees', $partiesJouees);

        return $this->render('jeu/decryptage.html.twig', [
            'plante' => $plante,
            'molecules' => $molecules,
            'timeRemaining' => $timeRemaining,
            'parties_jouees' => $partiesJouees
        ]);
    }
    #[Route('/valider', name: 'validate_answer')]
    public function validateAnswer(
        Request $request,
        SessionInterface $session,
        CadenasRepository $cadenasRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $planteId = $session->get('plante_id');
        $partieId = $session->get('partie_id');

        $globalStartTime = $session->get('global_start_time');
        $currentTime = time();
        $timeElapsed = $currentTime - $globalStartTime;
        $timeRemaining = 300 - $timeElapsed;


        $score = $session->get('score', 0);

        $cadenas = $cadenasRepository->findOneBy(['plante' => $planteId]);
        $motSecret = $cadenas->getMotSecret();


        $partie = $entityManager->getRepository(Partie::class)->find($partieId);
        $codeSaisi = trim(strtolower($data['code']));
        $motSecret = trim(strtolower($motSecret));

        $isCodeCorrect = ($codeSaisi === $motSecret);

        if ($timeRemaining <= 0) {
            if ($partie) {
                $partie->setEtat($isCodeCorrect ? 'terminee' : 'echec');
                $partie->setScore($score);
                $entityManager->persist($partie);
                $entityManager->flush();
            }

            return new JsonResponse([
                'status' => 'timeout',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => $isCodeCorrect,
                    'timeout' => true,
                    'parties_jouees' => $session->get('parties_jouees', 0),
                    'score' => $score
                ])
            ]);
        }


        if ($isCodeCorrect) {
            $score += 1;
            $session->set('score', $score);

            $plantesJouees = $session->get('plantes_jouees', []);
            $partiesJouees = count($plantesJouees);
            $session->set('parties_jouees', $partiesJouees);

            if ($partie) {
                $partie->setScore($score);
                $partie->setEtat('terminee');
                $entityManager->persist($partie);
                $entityManager->flush();
            }

            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => true,
                    'timeout' => false,
                    'parties_jouees' => $partiesJouees,
                    'score' => $score
                ])
            ]);
        }


        if ($partie) {
            $partie->setEtat('echec');
            $partie->setScore($score);
            $entityManager->persist($partie);
            $entityManager->flush();
        }

        return new JsonResponse([
            'status' => 'failure',
            'redirectUrl' => $this->generateUrl('resultat', [
                'success' => false,
                'timeout' => false,
                'parties_jouees' => $session->get('parties_jouees'),
                'score' => $score
            ])
        ]);
    }
    #[Route('/resultat', name: 'resultat')]
    public function showResult(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ): Response {
        $isSuccess = $request->query->get('success', false);
        $isTimeout = $request->query->get('timeout', false);
        $score = $session->get('score', 0);
        $partieId = $session->get('partie_id');

        $plantesJouees = $session->get('plantes_jouees', []);
        $partiesJouees = count($plantesJouees);

        $session->set('parties_jouees', $partiesJouees);
        $tentativeRejouerApres4 = $partiesJouees >= 4 && $request->query->get('rejouer', false);
        $partie = $entityManager->getRepository(Partie::class)->find($partieId);
        $plante = $partie ? $partie->getPlante() : null;

        return $this->render('jeu/resultat.html.twig', [
            'isSuccess' => $isSuccess,
            'isTimeout' => $isTimeout,
            'score' => $score,
            'plante' => $plante,
            'parties_jouees' => $partiesJouees,
            'tentativeRejouerApres4' => $tentativeRejouerApres4,
        ]);
    }

}