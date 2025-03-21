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

    // Dans JeuController.php

    // Dans JeuController.php

    #[Route('/jeu', name: 'commencer_jeu')]
    public function startGame(
        PlanteRepository $planteRepository,
        MoleculeRepository $moleculeRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        Request $request
    ): Response {
        // Initialiser le chronomètre UNIQUEMENT si on vient de la page d'accueil
        // ou si le chronomètre n'existe pas encore
        if ($request->query->get('from') === 'accueil' || !$session->has('global_start_time')) {
            $session->set('global_start_time', time());
            $session->set('parties_jouees', 0);
            $session->set('formules_trouvees', []); // Initialiser la liste des formules trouvées
        }

        // Calculer le temps restant depuis le début du jeu
        $globalStartTime = $session->get('global_start_time');
        $timeElapsed = time() - $globalStartTime;
        $timeRemaining = 300 - $timeElapsed; // 5 minutes

        // Si le temps est écoulé
        if ($timeRemaining <= 0) {
            return $this->redirectToRoute('resultat', [
                'success' => false,
                'timeout' => true,
                'parties_jouees' => $session->get('parties_jouees', 0)
            ]);
        }

        // Incrémenter le compteur de parties jouées si ce n'est pas une nouvelle partie
        if ($request->query->get('from') !== 'accueil') {
            $session->set('parties_jouees', $session->get('parties_jouees', 0) + 1);
        }
        // Récupérer les IDs des plantes déjà jouées
        $plantesJouees = $session->get('plantes_jouees', []);

        // Sélectionner une plante aléatoire
        $plante = $planteRepository->findRandomPlante($plantesJouees);
        if (!$plante) {
            $this->addFlash('error', 'Aucune plante n\'est disponible pour jouer.');
            return $this->redirectToRoute('accueil');
        }
        // Récupérer l'ID de la plante
        $planteId = $plante->getId();
        // Ajouter la plante à la liste des plantes jouées
        $plantesJouees[] = $plante->getId();
        $session->set('plantes_jouees', $plantesJouees);
         // Récupérer les molécules
        $molecules = $moleculeRepository->findBy(['plante' => $plante->getId()]);
//        $molecule = $moleculeRepository->findRandomMolecule($plante->getId());
//        if (!$molecule) {
//            $this->addFlash('error', 'Aucune molécule n\'est disponible pour cette plante.');
//            return $this->redirectToRoute('accueil');
//        }
        // Créer une nouvelle partie
        $partie = new Partie();
        $partie->setPlante($plante);
        $partie->setEtat('en_cours');
        $partie->setScore(0);

        $entityManager->persist($partie);
        $entityManager->flush();

        // Mettre à jour la session
        $session->set('plante_id', $plante->getId());
        $session->set('partie_id', $partie->getId());
        $session->set('molecules', $molecules); // Stocker les molécules dans la session

        return $this->render('jeu/decryptage.html.twig', [
            'plante' => $plante,
            'molecules' => $molecules,
            'timeRemaining' => $timeRemaining,
            'parties_jouees' => $session->get('parties_jouees', 0)
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


        $globalStartTime = $session->get('global_start_time');
        $currentTime = time();
        $timeElapsed = $currentTime - $globalStartTime;
        $timeRemaining = 300 - $timeElapsed;

        // Vérifier si le temps est écoulé (5 minutes = 300 secondes)
        if ($timeRemaining <= 0) {
            return new JsonResponse([
                'status' => 'timeout',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => false,
                    'timeout' => true,
                    'parties_jouees' => $session->get('parties_jouees', 0)
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

            // Incrémenter le compteur de parties
            $partiesJouees = $session->get('parties_jouees', 0);
            $session->set('parties_jouees', $partiesJouees + 1);

            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => true,
                    'timeout' => false,
                    'parties_jouees' => $session->get('parties_jouees')
                ])
            ]);
        }

        // Si le code est incorrect
        return new JsonResponse([
            'status' => 'failure',
            'redirectUrl' => $this->generateUrl('resultat', [
                'success' => false,
                'timeout' => false,
                'parties_jouees' => $session->get('parties_jouees')
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
        $score = $request->query->get('score', 0);
        $partieId = $session->get('partie_id');

        $partie = $entityManager->getRepository(Partie::class)->find($partieId);
        $plante = $partie ? $partie->getPlante() : null;

        return $this->render('jeu/resultat.html.twig', [
            'isSuccess' => $isSuccess,
            'isTimeout' => $isTimeout,
            'score' => $score,
            'plante' => $plante,
            'parties_jouees' => $session->get('parties_jouees', 0),
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