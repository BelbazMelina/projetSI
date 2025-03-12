<?php

namespace App\DataFixtures;

use App\Entity\Plante;
use App\Entity\Molecule;
use App\Entity\Cadenas;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $plante1 = new Plante();
        $plante1->setId(1);
        $plante1->setNom('Camomille');
        $plante1->setImage('Camomille.png');
        $manager->persist($plante1);

        $plante2 = new Plante();
        $plante2->setId(2);
        $plante2->setNom('Caféier');
        $plante2->setImage('Caféier.png');
        $manager->persist($plante2);

        $plante3 = new Plante();
        $plante3->setId(3);
        $plante3->setNom('Romarin');
        $plante3->setImage('Romarin.png');
        $manager->persist($plante3);

        $plante4 = new Plante();
        $plante4->setId(4);
        $plante4->setNom('Lavande');
        $plante4->setImage('Lavande.png');
        $manager->persist($plante4);

        // Création des cadenas
        $cadenas1 = new Cadenas();
        $cadenas1->setId(1);
        $cadenas1->setMotSecret('dpdml');
        $cadenas1->setImage('cadenas1.png');
        $cadenas1->setPlante($plante1);
        $manager->persist($cadenas1);

        $cadenas2 = new Cadenas();
        $cadenas2->setId(2);
        $cadenas2->setMotSecret('XaRXT');
        $cadenas2->setImage('cadenas2.png');
        $cadenas2->setPlante($plante2);
        $manager->persist($cadenas2);

        $cadenas3 = new Cadenas();
        $cadenas3->setId(3);
        $cadenas3->setMotSecret('jRAtO');
        $cadenas3->setImage('cadenas3.png');
        $cadenas3->setPlante($plante3);
        $manager->persist($cadenas3);

        $cadenas4 = new Cadenas();
        $cadenas4->setId(4);
        $cadenas4->setMotSecret('chacZ');
        $cadenas4->setImage('cadenas4.png');
        $cadenas4->setPlante($plante4);
        $manager->persist($cadenas4);

        // Création des molécules pour chaque plante
        $this->createMolecule($manager, 1, 'C14H16', 'Chamazulene.png', 'Donne la couleur bleue à l\'huile essentielle, anti-inflammatoire', $plante1);
        $this->createMolecule($manager, 2, 'C15H26O', 'Bisabolol.png', 'Apaise, cicatrise, très utilisé en cosmétique', $plante1);
        $this->createMolecule($manager, 3, 'C15H10O5', 'Apigenine.png', 'Effet relaxant, antioxydant, favorise le sommeil', $plante1);
        $this->createMolecule($manager, 4, 'C15H24', 'Alpha-Farnesene.png', 'Protège contre les insectes, antimicrobien', $plante1);
        $this->createMolecule($manager, 5, 'C15H20O3', 'Matricine.png', 'Précurseur du chamazulène, puissant anti-inflammatoire', $plante1);

        $this->createMolecule($manager, 6, 'C8H10N4O2', 'Caféine.png', 'Stimulant, améliore la concentration', $plante2);
        $this->createMolecule($manager, 7, 'C16H18O9', 'chlorogenic acide.png', 'Antioxydant, régule le métabolisme', $plante2);
        $this->createMolecule($manager, 8, 'C7H7NO2', 'Trigonelline.png', 'Donne l\'arôme du café, neuroprotecteur', $plante2);
        $this->createMolecule($manager, 9, 'C7H8N4O2', 'Théobromine.png', 'Effet relaxant, dilate les vaisseaux sanguins', $plante2);
        $this->createMolecule($manager, 10, 'C7H6O5', 'quinic acid.png', 'Contribue à l\'acidité du café, antioxydant', $plante2);

        $this->createMolecule($manager, 11, 'C18H16O8', 'Acide rosmarinique.png', 'Antioxydant puissant, anti-inflammatoire', $plante3);
        $this->createMolecule($manager, 12, 'C10H18O', 'Cinéole.png', 'Améliore la concentration, expectorant', $plante3);
        $this->createMolecule($manager, 13, 'C10H16O', 'Camphre.png', 'Stimulant, favorise la circulation sanguine', $plante3);
        $this->createMolecule($manager, 14, 'C10H16', 'Alpha-pinène.png', 'Antimicrobien, améliore la respiration', $plante3);
        $this->createMolecule($manager, 15, 'C10H18O', 'Bornéol.png', 'Apaisant, antibactérien', $plante3);

        $this->createMolecule($manager, 16, 'C10H18O', 'Linalool.png', 'Antiseptique, attire les pollinisateurs', $plante4);
        $this->createMolecule($manager, 17, 'C12H20O2', 'Linalyl acétate.png', 'Apaisant, contribue à l\'odeur', $plante4);
        $this->createMolecule($manager, 18, 'C10H16O', 'Camphre.png', 'Défense contre les insectes et antifongique', $plante4);
        $this->createMolecule($manager, 19, 'C10H18O', 'Géraniol.png', 'Antibactérien, attire les pollinisateurs', $plante4);
        $this->createMolecule($manager, 20, 'C10H16', 'Ocimène.png', 'Protection contre les parasites et production d\'arômes', $plante4);

        $manager->flush();
    }

    private function createMolecule(ObjectManager $manager, int $id, string $formule, string $image, string $information, Plante $plante): void
    {
        $molecule = new Molecule();
        $molecule->setId($id);
        $molecule->setFormuleChimique($formule);
        $molecule->setImage($image);
        $molecule->setInformation($information);
        $molecule->setPlante($plante);
        $manager->persist($molecule);
    }
}