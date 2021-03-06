<?php /**
 * editor : victor fau
 * contact : victorrfau@gmail.com
 * context : school
 */



namespace App\Controller;


use App\Assets\Response;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category")
 */
class CategoryController extends BackendController {

    /**
	 * @Route("/")
	 * @param CategoryRepository $category
	 * @return Response
	 */
	public function index(CategoryRepository $category){
		$categories = $category->findAll();

		return new Response(0, $categories);
	}

    /**
     * @Route("/view/{id}")
     * @param $id
     */
    public function view ($id, CategoryRepository $category, RecetteRepository $recette){
        $categories = $category->find($id);
        $recettesTable = $categories->getRecettes();
        $recettes = $recettesTable->toArray();

        if($categories == null){
            return new Response(15);
        }

        $response = [
            'categories' => $categories,
            'recettes'   => $recettes
        ];

        return new Response(0, $response);
	}

    /**
     * @Route("/delete/{id}")
     */
    public function delete (CategoryRepository $categoryRepository, EntityManagerInterface $em, $id){
        $category = $categoryRepository->find($id);
        $em->remove($category);
        $em->flush();
        return new Response(0);
    }

    /**
     * @Route("/edit/{id}", methods="POST", name="category_edit")
     * @param CategoryRepository     $categoryRepository
     * @param EntityManagerInterface $em
     * @param Request                $request
     * @param                        $id
     * @return Response
     */
    public function edit (CategoryRepository $categoryRepository, EntityManagerInterface $em, Request $request, $id): Response{

        $name = $request->get('name', null);

        if($name === null OR empty($name)){
            return new Response(8);
        }
        if(count($categoryRepository->findBy(["name" => $name])) > 0){
            return new Response(10);
        }

        $category = $categoryRepository->find($id);
        $category->setName($name);
        $em->persist($category);
        $em->flush();


        return new Response(0);
    }

    /**
     * @Route("/add", methods={"POST"})
     * @param CategoryRepository     $categories
     * @param EntityManagerInterface $em
     * @param Request                $request
     * @return Response
     */
	public function add(CategoryRepository $categories, EntityManagerInterface $em, Request $request): Response{

		$name = $request->get('name', null);

		if(sizeof($categories->findBy(["name" => $name])) > 0){
			return new Response(10);
		};

		$category = new Category();

		$category->setName($name);
		$em->persist($category);
		$em->flush();

		$response = $categories->findBy(['name' => $name]);

		return new Response(0, $response);
	}

    /**
     * @Route("/add/recette")
     * @param CategoryRepository     $category
     * @param RecetteRepository      $recette
     * @param EntityManagerInterface $em
     * @param Request                $request
     * @return Response
     */
	public function addRecette(CategoryRepository $category, RecetteRepository $recette, EntityManagerInterface $em, Request $request): Response{
		$idCat = $request->get('category', null);
		$idRecette = $request->get('recette', null);

		if($recette->find($idRecette) == null OR $category->find($idCat) == null){
			return new Response(15);
		}
		$categorie = $category->find($idCat);
		$recettes = $recette->find($idRecette);

		$recettes->addRelation($categorie);
		$em->persist($recettes);
		$em->flush();
		return new Response(0);
	}
	/**
	 * @Route("/remove/recette")
	 * @param CategoryRepository $category
	 * @param RecetteRepository $recette
	 * @param Request $request
	 * @return Response
	 */
	public function removeRecette(CategoryRepository $category, RecetteRepository $recette, EntityManagerInterface $em, Request $request){
		$idCat = $request->get('category', null);
		$idRecette = $request->get('recette', null);

		if($recette->find($idRecette) == null || $category->find($idCat) == null){
			return new Response(15);
		}
		$categorie = $category->find($idCat);
		$recettes = $recette->find($idRecette);

		$recettes->removeRelation($categorie);
		$em->persist($recettes);
		$em->flush();
		return new Response(0);
	}
}
