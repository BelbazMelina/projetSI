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
    public function findRandomPlante(): ?Plante
    {
        $conn = $this->getEntityManager()->getConnection();

        // Cette requête fonctionne avec PostgreSQL et MySQL
        $sql = 'SELECT id_plante FROM plante ORDER BY RANDOM() LIMIT 1';

        try {
            $result = $conn->executeQuery($sql)->fetchAssociative();

            if ($result === false) {
                return null;
            }

            return $this->find($result['id_plante']);
        } catch (\Exception $e) {

            $sql = 'SELECT id_plante FROM plante';
            $result = $conn->executeQuery($sql)->fetchAllAssociative();

            if (empty($result)) {
                return null;
            }

            $randomIndex = array_rand($result);
            return $this->find($result[$randomIndex]['id_plante']);
        }
    }
    #[Route('/jeu', name: 'commencer_jeu')]
    public function startGame(
        PlanteRepository $planteRepository,
        MoleculeRepository $moleculeRepository,
        SessionInterface $session
    ): Response {
        // Sélectionner une plante aléatoire
        $plantes = $planteRepository->findAll();
        $plante = $plantes[array_rand($plantes)];

        // Récupérer les molécules associées à cette plante
        $molecules = $moleculeRepository->findBy(['idPlante' => $plante]);

        // Stocker l'ID de la plante en session
        $session->set('plante_id', $plante->getIdPlante());
        $session->set('score', 0);

        // Passer les données au template
        return $this->render('jeu/decryptage.html.twig', [
            'plante' => $plante,
            'molecules' => $molecules,
        ]);
    }
    #[Route('/validate-answer', name: 'validate_answer', methods: ['POST'])]
    public function validateAnswer(Request $request, SessionInterface $session, CadenasRepository $cadenasRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $planteId = $data['planteId'];
        $userCode = $data['code'];

        // Récupérer le code secret de la plante
        $cadenas = $cadenasRepository->findOneBy(['idPlante' => $planteId]);
        $secretCode = $cadenas->getMotSecret();

        // Vérifier si le code est correct
        if ($userCode === $secretCode) {
            $session->set('score', 100); // Score maximum
            return new JsonResponse([
                'status' => 'success',
                'redirectUrl' => $this->generateUrl('resultat', ['id' => $planteId, 'success' => true])
            ]);
        }

        return new JsonResponse(['status' => 'continue']);
    }

    #[Route('/resultat/{id}', name: 'resultat')]
    public function showResult(int $id, Request $request, PlanteRepository $planteRepository, SessionInterface $session): Response
    {
        $isSuccess = $request->query->get('success', false);
        $score = $session->get('score', 0);
        $plante = $planteRepository->find($id);

        return $this->render('jeu/resultat.html.twig', [
            'isSuccess' => $isSuccess,
            'score' => $score,
            'plante' => $plante,
        ]);
    }
}