<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class ApiController extends AbstractController
{
    /**
     * @Route("/home",name="apis")
     */
    public function index()
    {
       return $this->render('main.html.twig',['a'=> 'je suis ici','tab'=>[1,2,3,4],'bool'=> false ]);
    }
}





