<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Shop;
use App\Entity\Localisation;
use App\Entity\Geolocalisation;
use App\Entity\Offer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{
    public $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/data", name="app_data")
     */
    public function saveDataFromApi(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $response = $this->client->request(
            'GET',
            'https://www.leshabitues.fr/testapi/shops'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $content = $response->toArray();

        foreach($content['data'] as $data) {
            $category = $entityManager->getRepository(Category::class)->findOneBy([
                'name' => $data['category']
            ]);

            if (!$category) {
                $category = new Category();
                $category->setName($data['category']);
                $entityManager->persist($category);
                $entityManager->flush();

                $category = $entityManager->getRepository(Category::class)->find($category->getId());
            }

            $shop = new Shop();
            $shop->setChain($data['chain']);
            $shop->setCategory($category);
            $shop->setActive($data['active']);
            $shop->setCreateAt($data['created_at']);
            $shop->setUpdatedAt($data['updated_at']);
            $shop->setSlug($data['slug']);
            $shop->setPictureUrl($data['picture_url']);

            $entityManager->persist($shop);
            $entityManager->flush();

            $shop_id = $shop->getId();

            foreach ($data['localisations'] as $value) {
                $localisation = new Localisation();
                $localisation->setShop($shop);
                $localisation->setName($value['name']);
                $localisation->setAddress($value['address']);
                $localisation->setZipcode($value['zipcode']);
                $localisation->setCity($value['city']);

                $entityManager->persist($localisation);
                $entityManager->flush();
                
                if ($localisation->getId()) {
                    $geoLoc = new Geolocalisation();
                    $geoLoc->setLocalisation($localisation);
                    $geoLoc->setLat($value['geoloc']['lat']);
                    $geoLoc->setLng($value['geoloc']['lng']);
    
                    $entityManager->persist($geoLoc);
                    $entityManager->flush();
                }
            }   

            foreach ($data['offers'] as $value) {
                $offer = new Offer();
                $offer->setShop($shop);
                $offer->setAmount($value['amount']);
                $offer->setReduction($value['reduction']);

                $entityManager->persist($offer);
                $entityManager->flush();
            }
        }
        die;
        return $content;
    }
}
