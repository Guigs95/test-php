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
use Symfony\Component\HttpFoundation\Request;
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

        $allShops = $entityManager->getRepository(Shop::class)->findAll();

        return $this->render('home/index.html.twig', [
            'number_imported' => count($allShops),
        ]);
    }

    /**
     * @Route("/shop/", methods={"POST"}, name="addShop")
     */
    public function addShop(Request $request)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();

        $category = $entityManager->getRepository(Category::class)->find($data['category_id']);
        
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

        return $shop;
    }

    /**
     * @Route("/shop/{id}", methods={"POST"}, name="editShop")
     */
    public function editShop(Request $request, $id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();

        $shop = $entityManager->getRepository(Shop::class)->find($id);

        $category = $entityManager->getRepository(Category::class)->find($data['category_id']);
        
        $shop->setChain($data['chain']);
        $shop->setCategory($category);
        $shop->setActive($data['active']);
        $shop->setCreateAt($data['created_at']);
        $shop->setUpdatedAt($data['updated_at']);
        $shop->setSlug($data['slug']);
        $shop->setPictureUrl($data['picture_url']);

        $entityManager->persist($shop);
        $entityManager->flush();

        return $shop;
    }

    /**
     * @Route("/shop/delete/{id}", name="deleteShop")
     */
    public function deleteShop($id)
    {
        $entityManager = $doctrine->getManager();

        $shop = $entityManager->getRepository(Shop::class)->find($id);
    
        if ($offers) { // si des offres sont lié 
            $offers = $entityManager->getRepository(Offer::class)->findBy([
                'shop_id' => $shop->getId()
            ]);

            $entityManager->remove($offers);
            $entityManager->flush();
        }

        $entityManager->remove($shop);
        $entityManager->flush();

        return true;
    }

    /**
     * @Route("/category/", methods={"POST"}, name="addCategory")
     */
    public function addCategory(Request $request)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        
        $category = new Category();
        $category->setName($data['name']);

        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    /**
     * @Route("/category/{id}", methods={"POST"}, name="editCategory")
     */
    public function editCategory(Request $request, $id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();

        $category = $entityManager->getRepository(Category::class)->find($id);
        
        $category->setName($data['name']);

        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    /**
     * @Route("/category/delete/{id}", name="deleteCategory")
     */
    public function deleteCategory($id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();

        $category = $entityManager->getRepository(Category::class)->find($id);

        $entityManager->remove($category);
        $entityManager->flush();

        return true;
    }

    /**
     * @Route("/offer/", methods={"POST"}, name="addOffer")
     */
    public function addOffer(Request $request)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        $shop = $entityManager->getRepository(Shop::class)->find($data['shop_id']);

        $offer = new Offer();
        $offer->setShop($shop);
        $offer->setAmount($data['amount']);
        $offer->setReduction($data['reduction']);

        $entityManager->persist($offer);
        $entityManager->flush();

        return $offer;
    }

    /**
     * @Route("/offer/{id}", methods={"POST"}, name="editOffer")
     */
    public function editOffer(Request $request, $id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        $shop = $entityManager->getRepository(Shop::class)->find($data['shop_id']);
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        $offer->setShop($shop);
        $offer->setAmount($data['amount']);
        $offer->setReduction($data['reduction']);

        $entityManager->persist($offer);
        $entityManager->flush();

        return $offer;
    }

    /**
     * @Route("/offer/delete/{id}", name="deleteOffer")
     */
    public function deleteOffer($id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        $entityManager->remove($offer);
        $entityManager->flush();

        return true;
    }

    /**
     * @Route("/localisation/", methods={"POST"}, name="addLocalisation")
     */
    public function addLocalisation(Request $request)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        $shop = $entityManager->getRepository(Localisation::class)->find($data['shop_id']);

        $localisation = new Localisation();
        $localisation->setShop($shop);
        $localisation->setName($data['name']);
        $localisation->setAddress($data['address']);
        $localisation->setZipcode($data['zipcode']);
        $localisation->setCity($data['city']);

        $entityManager->persist($localisation);
        $entityManager->flush();

        return $localisation;
    }

    /**
     * @Route("/localisation/{id}", methods={"POST"}, name="editLocalisation")
     */
    public function editLocalisation(Request $request, $id)
    {
        $entityManager = $doctrine->getManager();

        $data = $request->request->get();
        $shop = $entityManager->getRepository(Localisation::class)->find($data['shop_id']);

        $localisation = $entityManager->getRepository(Localisation::class)->find($id);
        $localisation->setShop($shop);
        $localisation->setName($data['name']);
        $localisation->setAddress($data['address']);
        $localisation->setZipcode($data['zipcode']);
        $localisation->setCity($data['city']);

        $entityManager->persist($localisation);
        $entityManager->flush();

        return $localisation;
    }

    /**
     * @Route("/localisation/delete/{id}", name="deleteLocalisation")
     */
    public function deleteLocalisation($id)
    {
        $entityManager = $doctrine->getManager();

        $localisation = $entityManager->getRepository(Localisation::class)->find($id);

        $entityManager->remove($localisation);
        $entityManager->flush();

        return true;
    }
}
