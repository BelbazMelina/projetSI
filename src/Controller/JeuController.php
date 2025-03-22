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
        try {
            // Initialisation de la session
            if ($request->query->get('from') === 'accueil' || !$session->has('global_start_time')) {
                $session->set('global_start_time', time());
                $session->set('parties_jouees', 0);
                $session->set('formules_trouvees', []);
                $session->set('plantes_jouees', []);
                $session->set('score', 0);// Réinitialiser les plantes jouées
            }

            // Vérification du temps
            $globalStartTime = $session->get('global_start_time');
            $timeElapsed = time() - $globalStartTime;
            $timeRemaining = 300 - $timeElapsed;

            // Si le temps est écoulé
            if ($timeRemaining <= 0) {
                // Réinitialiser la session pour un nouveau jeu
                $session->set('plantes_jouees', []);
                return $this->redirectToRoute('resultat', [
                    'success' => false,
                    'timeout' => true,
                    'parties_jouees' => $session->get('parties_jouees', 0)
                ]);
            }

            // Sélection de la plante
            $plantesJouees = $session->get('plantes_jouees', []);
            $plante = $planteRepository->findRandomPlante($plantesJouees);

            // Si toutes les plantes ont été jouées
            if (!$plante) {
                if ($timeRemaining > 0) {
                    // S'il reste du temps mais toutes les plantes ont été jouées
                    $this->addFlash('info', 'Vous avez joué toutes les plantes disponibles !');
                    return $this->redirectToRoute('reultat');
                } else {
                    // Si le temps est écoulé, réinitialiser les plantes jouées
                    $session->set('plantes_jouees', []);
                    $plante = $planteRepository->findRandomPlante([]);
                }
            }

            // Récupération des molécules
            $molecules = $moleculeRepository->findBy(['plante' => $plante->getId()]);

            // Création de la partie
            $partie = new Partie();
            $partie->setPlante($plante);
            $partie->setEtat('en_cours');
            $partie->setScore(0);

            $entityManager->persist($partie);
            $entityManager->flush();

            // Mise à jour de la session
            $plantesJouees[] = $plante->getId();
            $session->set('plantes_jouees', $plantesJouees);
            $session->set('plante_id', $plante->getId());
            $session->set('partie_id', $partie->getId());
            $session->set('molecules', $molecules);

            $partiesJouees = $session->get('parties_jouees', 0);
            if ($partiesJouees >= 4) {
                return $this->render('jeu/resultat.html.twig', [
                    'isSuccess' => true,
                    'score' => $session->get('score', 0),
                    'message' => 'Vous avez joué avec les 4 plantes disponibles. Voici votre score final :',
                    'parties_jouees' => $partiesJouees,
                    'showRejouerButton' => false, // Masquer le bouton "Rejouer"
                ]);
            }

            return $this->render('jeu/decryptage.html.twig', [
                'plante' => $plante,
                'molecules' => $molecules,
                'timeRemaining' => $timeRemaining,
                'parties_jouees' => $session->get('parties_jouees', 0)
            ]);

        } catch (\Exception $e) {
            // Log l'erreur
            $this->addFlash('error', 'Une erreur est survenue lors du démarrage du jeu.');
            return $this->redirectToRoute('accueil');
        }
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

        // Récupérer le score existant depuis la session
        $score = $session->get('score', 0);

        // Vérifier si le temps est écoulé
        if ($timeRemaining <= 0) {
            // Réinitialiser le score à 0 quand le temps est écoulé
            $session->set('score', 0);

            return new JsonResponse([
                'status' => 'timeout',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => false,
                    'timeout' => true,
                    'parties_jouees' => $session->get('parties_jouees', 0),
                    'score' => 0 // Score réinitialisé
                ])
            ]);
        }

        $cadenas = $cadenasRepository->findOneBy(['plante' => $planteId]);
        $motSecret = $cadenas->getMotSecret();

        // Récupérer la partie en cours
        $partie = $entityManager->getRepository(Partie::class)->find($partieId);
        $codeSaisi = trim(strtolower($data['code']));
        $motSecret = trim(strtolower($motSecret));

        // Vérifier si le code est correct
        if ($codeSaisi === $motSecret) {
            // Incrémenter le score
            $score += 1;

            // Mettre à jour le score et l'état de la partie
            if ($partie) {
                $partie->setScore($score);
                $partie->setEtat('terminee');
                $entityManager->persist($partie);
                $entityManager->flush();
            }

            // Mettre à jour le score dans la session
            $session->set('score', $score);

            // Incrémenter le compteur de parties
            $partiesJouees = $session->get('parties_jouees', 0);
            $session->set('parties_jouees', $partiesJouees + 1);

            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', [
                    'success' => true,
                    'timeout' => false,
                    'parties_jouees' => $session->get('parties_jouees'),
                    'score' => $score
                ])
            ]);
        }

        // Si le code est incorrect
        if ($partie) {
            $partie->setEtat('echec');
            $partie->setScore($score); // Garder le score actuel
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