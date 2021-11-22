<?php

namespace App\Controller;
use App\Entity\Todolist;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TodolistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class TodolistController extends AbstractController
{
    /**
     * @Route("/gettodolist", name="gettodolist", methods={"GET"})
     */
    public function getAll(TodolistRepository $todolistRepository, NormalizerInterface $normalizer)
    {
        #get all todolists from DB
        $todolists=$todolistRepository->findAll();
        if (!$todolists) { # IF the table is empty
            return $this->json(['status' => 400,'message'=>"No list found"],400);
        }
        else
        {#Normalisation is essential to tranform objects to associative tables(since objects attributes are private)
        $todolistsNormalise=$normalizer->normalize($todolists,null,['groups'=>'extract']);
        #Encode them with Json format
        $json=json_encode($todolistsNormalise);
        return new Response($json,200,["content-Type"=>"application/json"]);
        #une autre facon de faire ça serait
        #return $this->json($todolistRepository->findAll(),200,[],['groups'=>'extract']); 
        } 
    }


    /**
     * @Route("/gettodolist/{id}", name="getonetodolist", methods={"GET"})
     */
    public function getOne(int $id,TodolistRepository $todolistRepository, NormalizerInterface $normalizer)
    {
        #get the todolist with the specified Id from DB
        $todolist=$todolistRepository->find($id);

        if (!$todolist) {
            return $this->json(['status' => 400,'message'=>"No list found"],400);
        }
        else
        {
        #Normalisation is essential to tranform objects to associative tables(since objects attributes are private)
        $todolistsNormalise=$normalizer->normalize($todolist,null,['groups'=>'extract']);
        #Encode them with Json format
        $json=json_encode($todolistsNormalise);
        
        return new Response($json,200,["content-Type"=>"application/json"]);
        
        }
    }




    /**
     * @Route("/addtodolist", name="addtodolist", methods={"POST"})
     */
    public function addTodo(Request $request, SerializerInterface $Serializer, EntityManagerInterface $em,ValidatorInterface $val)
    {
        #catch the Json Object
        $receivedJson= $request->getContent();
        try {
        #change it to todolist Object
        $todolist = $Serializer->deserialize($receivedJson,Todolist::class,'json');
        #valider les champs
        $er=$val->validate($todolist);
        if(count($er)>0)
        {
            return $this->json($er,400);
        }
        #add it to the DB
        $em->persist($todolist);$em->flush();
        #return it in a JsonFormat
        return $this->json($todolist,201,[],['groups'=>'extract']);
        }
        catch(NotEncodableValueException $e){
            return $this->json(['status' => 400,'message'=> $e->getMessage()],400);
        }

    }

    /**
     * @Route("/uptodolist", name="uptodolist", methods={"PUT"})
     */
    public function upTodo(Request $request, SerializerInterface $Serializer, EntityManagerInterface $em,ValidatorInterface $val,TodolistRepository $todolistRepository)
    {

        $newtodolist=json_decode($request->getContent(),true);
        # look for the specified object in the DB
        $todolist=$todolistRepository->find($newtodolist['id']);
        if(!$todolist)
        {
            return $this->json(['status' => 400,'message'=>"No list found"],400);
        }
        else
        {# set changes
        $todolist->setName($newtodolist['name']);
        
        #perform updates to the DB
        $em->flush();
        #return it in a JsonFormat
        return $this->json($todolist,201,[],['groups'=>'extract']);}
    }

    /**
     * @Route("removetodolist/{id}", name="removetodolist", methods={"DELETE"})
     */
    public function deleteTodo(int $id, SerializerInterface $Serializer, EntityManagerInterface $em,ValidatorInterface $val,TodolistRepository $todolistRepository)
    {

        
        # look for the specified object in the DB
        $todolist=$todolistRepository->find($id);

        if (!$todolist) {
            return $this->json(['status' => 400,'message'=>"No list found"],400);
        }
        else
        
        {# set changes
        $em->remove($todolist);
        $em->flush();
        #return Json confiramtion
        return $this->json(['status' => 200,'message'=>"Todolist deleted"],200);}
    }


}
