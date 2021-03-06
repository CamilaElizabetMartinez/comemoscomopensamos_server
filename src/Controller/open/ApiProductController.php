<?php

namespace App\Controller\open;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductNormalize;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserNormalize;
use App\Controller\ValidatorInterface;
use App\Controller\UserPasswordHasherInterface;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/api/product", name="api_product")
 */
class ApiProductController extends AbstractController
{
    /**
     * @Route(
     *      "",
     *      name="cget",
     *      methods={"GET"}
     * )
     */
    public function index(
        Request $request,
        ProductRepository $productRepository,
        ProductNormalize $productNormalize
    ): Response {
        //Guardo los datos que llegan de la solicitud
        $data = $request->request;

        //Recupero los valores del parametro por GET
        $filterName =$request->query->get('filterName');
        $filterCategory =$request->query->get('filterCategory');
        
        //Guardo el numero de la pagina
        $pageNumber = $request->query->get('pageNumber');
        
        //Declaro el numero de producto por pagina
        $quantityProductForPage = 8;

        //Consulto cuántas filas hay en la tabla Producto por filtrado
        $qb = $productRepository
            ->createQueryBuilder('tableProduct')
            ->select('count(tableProduct.id)')
        ;
        $quantityTheProduct = [];

        if ($filterName) {
            $quantityTheProduct= $qb->where('tableProduct.name like :filterName')
            ->setParameter('filterName', "%$filterName%");
        }
        if ($filterCategory) {
            $quantityTheProduct= $qb->andWhere('tableProduct.category = :filterCategory')
            ->setParameter('filterCategory', $filterCategory);
        }

        $quantityTheProduct = $qb->getQuery()->getSingleScalarResult();

        //Si pageNumber es verdadero devuelve el numero de la primer posición
        if ($pageNumber == true) {
            $fromPosition = ($pageNumber -1) * $quantityProductForPage;
        } else {
            $fromPosition = 0;
        }
    
        //Recupero los productos por nombre,categoria y posicion segun el intervalo.
        $qb = $productRepository->createQueryBuilder('tableProduct');

        $productEntities = [];

        if ($filterName) {
            $productEntities= $qb->where('tableProduct.name like :filterName')
            ->setParameter('filterName', "%$filterName%");
        }
        if ($filterCategory) {
            $productEntities= $qb->andWhere('tableProduct.category = :filterCategory')
            ->setParameter('filterCategory', $filterCategory);
        }

        $productEntities = $qb->setFirstResult($fromPosition)
            ->setMaxResults($quantityProductForPage)
            ->getQuery()
            ->getResult()
        ;
                
        //Declaro un array vacio para guardar los datos normalizados
        $data = [];

        //Se normaliza cada entidad de producto y se guarda en un array vacio
        foreach ($productEntities as $theProductEntity) {
            $data[] = $productNormalize->ProductNormalize($theProductEntity);
        }

        //Declaro el numero total de paginas
        $totalPages = ceil($quantityTheProduct/$quantityProductForPage);

        //Declaro los valores y lo retorno
        $response = [
            'pageNumber' => $pageNumber,
            'productEntities' => $data,
            'filterName' => $filterName,
            'filterCategory' => $filterCategory,
            'totalPage' => $totalPages
        ];
     
        return $this->json($response, Response::HTTP_OK);
    }

    /**
     * @Route(
     *      "/detail/{slug}",
     *      name="get",
     *      methods={"GET"}
     * )
     */
    public function details(
        string $slug,
        productRepository $productRepository,
        ProductNormalize $productNormalize
    ): Response {
        $theProductEntity = $productRepository->findOneBy(['slug' => $slug]);
        
        $theProductEntityNormalize = $productNormalize->ProductNormalize($theProductEntity);

        return $this->json($theProductEntityNormalize);
    }

   ## /**
   ##  * @Route(
   ##  *      "/register",
    ## *      name="post",
    ## *      methods={"POST"}
    ## * )
    ## */
    ##public function add(
    ##   UserNormalize $userNormalize,
    ##    Request $request,
    ##    EntityManagerInterface $entityManager,
    ##    ValidatorInterface $validator,     
    ##    UserPasswordHasherInterface $hasher
    ##    ): Response {
    ##    $data = json_decode($request->getContent());
    ##             
    ##    $user = new User();

    ##    $user->setName($data->name);
    ##    $user->setSurname1($data->surname1);
    ##    $user->setCity($data->city);
    ##    $user->setAddress($data->address);
    ##    $user->setPhoneNumber($data->phoneNumber);
    ##    $user->setEmail($data->email);

    ##    $hash = $hasher->hashPassword($user, $data->password);
    ##    $user->setPassword($hash);                        

    ##    $errors = $validator->validate($user);

    ##    if(count($errors) > 0) {
    ##        $dataErrors = [];
    ##        /** @var \Symfony\Component\Validator\ConstraintViolation $error */
    ##        foreach($errors as $error) {
    ##            $dataErrors[] = $error->getMessage();
    ##        }
    ##        
    ##        return $this->json([
    ##            'status' => 'error',
    ##            'data' => [
    ##                'errors' => $dataErrors
    ##            ],
    ##        ],
    ##        Response::HTTP_BAD_REQUEST);
    ##    }

    ##    $entityManager->persist($user);
    ##    
    ##    $entityManager->flush();

    ##   
    ##    return $this->json(
    ##        $userNormalize->UserNormalize($user),
    ##        Response::HTTP_CREATED,        

    ##    );
    ##
    ##}
}
