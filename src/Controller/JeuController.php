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
        SessionInterface $session
    ): Response {
        $plante = $planteRepository->findRandomPlante();

        if (!$plante) {
            $this->addFlash('error', 'Aucune plante n\'est disponible pour jouer.');
            return $this->redirectToRoute('accueil');
        }

        $molecules = $moleculeRepository->findBy(['id' => $plante->getId()]);

        // Créer une nouvelle partie
        $partie = new Partie();
        $partie->setPlante($plante);
        $partie->setEtat('en_cours');
        $partie->setScore(0);

        $entityManager->persist($partie);
        $entityManager->flush();

        $session->set('plante_id', $plante->getId());
        $session->set('partie_id', $partie->getId());
        $session->set('start_time', time());

        return $this->render('jeu/decryptage.html.twig', [
            'plante' => $plante,
            'molecules' => $molecules,
            'timeLimit' => 300, // 5 minutes en secondes
        ]);
    }

    #[Route('/validate-answer', name: 'validate_answer', methods: ['POST'])]
    public function validateAnswer(
        Request $request,
        SessionInterface $session,
        CadenasRepository $cadenasRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $planteId = $session->get('plante_id');
        $startTime = $session->get('start_time');
        $currentTime = time();
        $timeElapsed = $currentTime - $startTime;

        // Vérifier si le temps est écoulé (5 minutes = 300 secondes)
        if ($timeElapsed >= 300) {
            return new JsonResponse([
                'status' => 'timeout',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => false,
                    'timeout' => true
                ])
            ]);
        }

        $cadenas = $cadenasRepository->findOneBy(['plante' => $planteId]);
        $motSecret = $cadenas->getMotSecret();

        // Vérifier si le code est correct
        if ($data['code'] === $motSecret) {
            // Calculer le score en fonction du temps restant
            $score = max(0, 100 - floor($timeElapsed / 3));
            $session->set('score', $score);

            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => true,
                    'timeout' => false
                ])
            ]);
        }

        // Si le code est incorrect
        return new JsonResponse([
            'status' => 'failure',
            'redirectUrl' => $this->generateUrl('resultat', [
                'success' => false,
                'timeout' => false
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
        $partieId = $session->get('partie_id');

        $partie = $entityManager->getRepository(Partie::class)->find($partieId);
        $plante = $partie ? $partie->getPlante() : null;
        $score = $partie ? $partie->getScore() : 0;

        return $this->render('jeu/resultat.html.twig', [
            'isSuccess' => $isSuccess,
            'isTimeout' => $isTimeout,
            'score' => $score,
            'plante' => $plante,
            'showReplayButton' => !$isTimeout,
            'message' => $this->getResultMessage($isSuccess, $isTimeout)
        ]);
    }

    private function getResultMessage(bool $isSuccess, bool $isTimeout): string
    {
        if ($isTimeout) {
            return 'Game Over ! Temps écoulé !';
        }
        return $isSuccess ? 'Bien joué ! Tu as trouvé le bon code !' : 'Game Over ! Plante sauvage !';
    }
}