<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {    

        
        // -------------------------
        // THEME : MUSIQUE
        // -------------------------
        $musique = new Theme();
        $musique->setName('Musique');
        $manager->persist($musique);

        // Cursus guitare
        $guitare = new Cursus();
        $guitare->setName("Cursus d’initiation à la guitare");
        $guitare->setPrice(50);
        $guitare->setTheme($musique);
        $manager->persist($guitare);

        $lesson = new Lesson();
        $lesson->setName("Découverte de l’instrument");
        $lesson->setPrice(26);
        $lesson->setContent("Contenu de la leçon : découverte de l’instrument.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($guitare);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Les accords et les gammes");
        $lesson->setPrice(26);
        $lesson->setContent("Contenu de la leçon : les accords et les gammes.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($guitare);
        $manager->persist($lesson);

        // Cursus piano
        $piano = new Cursus();
        $piano->setName("Cursus d’initiation au piano");
        $piano->setPrice(50);
        $piano->setTheme($musique);
        $manager->persist($piano);

        $lesson = new Lesson();
        $lesson->setName("Découverte de l’instrument");
        $lesson->setPrice(26);
        $lesson->setContent("Contenu de la leçon : découverte du piano.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($piano);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Les accords et les gammes");
        $lesson->setPrice(26);
        $lesson->setContent("Contenu de la leçon : les accords et les gammes au piano.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($piano);
        $manager->persist($lesson);


        // -------------------------
        // THEME : INFORMATIQUE
        // -------------------------
        $info = new Theme();
        $info->setName('Informatique');
        $manager->persist($info);

        $devweb = new Cursus();
        $devweb->setName("Cursus d’initiation au développement web");
        $devweb->setPrice(60);
        $devweb->setTheme($info);
        $manager->persist($devweb);

        $lesson = new Lesson();
        $lesson->setName("Les langages Html et CSS");
        $lesson->setPrice(32);
        $lesson->setContent("Introduction aux langages Html et CSS.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($devweb);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Dynamiser votre site avec Javascript");
        $lesson->setPrice(32);
        $lesson->setContent("Introduction à Javascript.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($devweb);
        $manager->persist($lesson);


        // -------------------------
        // THEME : JARDINAGE
        // -------------------------
        $jardin = new Theme();
        $jardin->setName('Jardinage');
        $manager->persist($jardin);

        $initJardin = new Cursus();
        $initJardin->setName("Cursus d’initiation au jardinage");
        $initJardin->setPrice(30);
        $initJardin->setTheme($jardin);
        $manager->persist($initJardin);

        $lesson = new Lesson();
        $lesson->setName("Les outils du jardinier");
        $lesson->setPrice(16);
        $lesson->setContent("Présentation des outils nécessaires au jardinier.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($initJardin);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Jardiner avec la lune");
        $lesson->setPrice(16);
        $lesson->setContent("Introduction au jardinage avec la lune.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($initJardin);
        $manager->persist($lesson);


        // -------------------------
        // THEME : CUISINE
        // -------------------------
        $cuisine = new Theme();
        $cuisine->setName('Cuisine');
        $manager->persist($cuisine);

        $initCuisine = new Cursus();
        $initCuisine->setName("Cursus d’initiation à la cuisine");
        $initCuisine->setPrice(44);
        $initCuisine->setTheme($cuisine);
        $manager->persist($initCuisine);

        $lesson = new Lesson();
        $lesson->setName("Les modes de cuisson");
        $lesson->setPrice(23);
        $lesson->setContent("Présentation des modes de cuisson.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($initCuisine);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Les saveurs");
        $lesson->setPrice(23);
        $lesson->setContent("Découverte des saveurs.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($initCuisine);
        $manager->persist($lesson);

        $dressage = new Cursus();
        $dressage->setName("Cursus d’initiation à l’art du dressage culinaire");
        $dressage->setPrice(48);
        $dressage->setTheme($cuisine);
        $manager->persist($dressage);

        $lesson = new Lesson();
        $lesson->setName("Mettre en œuvre le style dans l’assiette");
        $lesson->setPrice(26);
        $lesson->setContent("Apprendre à styliser une assiette.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($dressage);
        $manager->persist($lesson);

        $lesson = new Lesson();
        $lesson->setName("Harmoniser un repas à quatre plats");
        $lesson->setPrice(26);
        $lesson->setContent("Créer un repas harmonieux en quatre plats.");
        $lesson->setVideoUrl("https://www.w3schools.com/html/mov_bbb.mp4");
        $lesson->setCursus($dressage);
        $manager->persist($lesson);


        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
