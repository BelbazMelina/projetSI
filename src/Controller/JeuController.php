<?php

namespace App\Controller;

use App\Entity\Plante;
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
        SessionInterface $session
    ): Response {
        $plante = $planteRepository->findRandomPlante();

        if (!$plante) {

            $this->addFlash('error', 'Aucune plante n\'est disponible pour jouer.');

        }


        $molecules = $moleculeRepository->findBy(['plante' => $plante]);

        $session->set('plante_id', $plante->getId());
        $session->set('start_time', time());
        $session->set('score', 0);

        return $this->render('jeu/decryptage.html.twig', [
            'plante' => $plante,
            'molecules' => $molecules,
        ]);
    }

    #[Route('/validate-answer', name: 'validate_answer', methods: ['POST'])]
    public function validateAnswer(Request $request, SessionInterface $session, CadenasRepository $cadenasRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $planteId = $session->get('plante_id');
        $startTime = $session->get('start_time');
        $currentTime = time();
        $timeElapsed = $currentTime - $startTime;

        if ($timeElapsed >= 300) {
            return new JsonResponse([
                'status' => 'timeout',
                'redirectUrl' => $this->generateUrl('resultat', ['success' => false])
            ]);
        }

        $cadenas = $cadenasRepository->findOneBy(['idPlante' => $planteId]);
        $motSecret = $cadenas->getMotSecret();

        if ($data['code'] === $motSecret) {
            // Calculer le score en fonction du temps restant
            $score = max(0, 100 - floor($timeElapsed / 3));
            $session->set('score', $score);

            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', ['success' => true])
            ]);
        }

        return new JsonResponse([
            'status' => 'failure',
            'redirectUrl' => $this->generateUrl('resultat', ['success' => false])
        ]);
    }

    #[Route('/resultat', name: 'resultat')]
    public function showResult(Request $request, SessionInterface $session): Response
    {
        $isSuccess = $request->query->get('success', false);
        $score = $session->get('score', 0);

        return $this->render('jeu/resultat.html.twig', [
            'isSuccess' => $isSuccess,
            'score' => $score,
        ]);
    }
}