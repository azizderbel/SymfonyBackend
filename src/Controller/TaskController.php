<?php

namespace App\Controller;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TaskRepository;
use App\Repository\TodolistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TaskController extends AbstractController
{
    /**
     * @Route("/gettasks", name="task",methods= {"GET"} )
     */
    public function getAll(TaskRepository $TaskRepository,NormalizerInterface $normalizer)
    {
        $tasks=$TaskRepository->findAll();
        if (!$tasks) { # IF the table is empty
            return $this->json(['status' => 400,'message'=>"No tasks found"],400);
        }

        else
        {#Normalisation is essential to tranform objects to associative tables(since objects attributes are private)
        $tasksNormalise=$normalizer->normalize($tasks,null,['groups'=>'extract']);
        #Encode them with Json format
        $json=json_encode($tasksNormalise);
        return new Response($json,200,["content-Type"=>"application/json"]);
        #une autre facon de faire ça serait
        #return $this->json($todolistRepository->findAll(),200,[],['groups'=>'extract']); 
        }
    }


    /**
     * @Route("/gettask/{id}", name="onetask",methods={"GET"})
     */
    public function getOne(int $id,TaskRepository $TaskRepository,NormalizerInterface $normalizer)
    {
        $task=$TaskRepository->find($id);
        if (!$task) { # IF the table is empty
            return $this->json(['status' => 400,'message'=>"No task found"],400);
        }

        else
        {#Normalisation is essential to tranform objects to associative tables(since objects attributes are private)
        $taskNormalis=$normalizer->normalize($task,null,['groups'=>'extract']);
        #Encode them with Json format
        $json=json_encode($taskNormalis);
        return new Response($json,200,["content-Type"=>"application/json"]);
        #une autre facon de faire ça serait
        #return $this->json($todolistRepository->findAll(),200,[],['groups'=>'extract']); 
        }
    }


    /**
     * @Route("/addtask/{id}", name="addtask", methods={"POST"})
     */

    public function addtask(int $id,Request $request, SerializerInterface $Serializer, EntityManagerInterface $em,ValidatorInterface $val,TodolistRepository $todolistRepository)
    {

        #find the appropriate todolist
        $todolist=$todolistRepository->find($id);
        #catch the Json Object
        $receivedJson= $request->getContent();
        try {
        #change it to task Object and linked it with todolist
        $task = $Serializer->deserialize($receivedJson,Task::class,'json');
        $task->setTodolist($todolist);
        $task->setState(false);
        #valider les champs
        $er=$val->validate($task);
        if(count($er)>0)
        {
            return $this->json($er,400);
        }
        #add it to the DB
        $em->persist($task);$em->flush();
        #return it in a JsonFormat
        return $this->json($task,201,[],['groups'=>'extract']);
        }
        catch(NotEncodableValueException $e){
            return $this->json(['status' => 400,'message'=> $e->getMessage()],400);
        }

    }

    /**
     * @Route("removetask/{id}", name="removetask", methods={"DELETE"})
     */
    public function deleteTask(int $id,EntityManagerInterface $em,TaskRepository $TaskRepository)
    {

        
        # look for the specified object in the DB
        $task=$TaskRepository->find($id);

        if (!$task) {
            return $this->json(['status' => 400,'message'=>"No task found"],400);
        }
        else
        
        {# delete
        $em->remove($task);
        $em->flush();
        #return Json confiramtion
        return $this->json(['status' => 200,'message'=>"Task deleted"],200);}
    }


    /**
     * @Route("/uptask", name="uptask", methods={"PUT"})
     */
    public function upTask(Request $request,EntityManagerInterface $em,TaskRepository $TaskRepository)
    {

        
        # look for the specified object in the DB
        $task=$TaskRepository->find(json_decode($request->getContent(),true)['id']);

        if (!$task) {
            return $this->json(['status' => 400,'message'=>"No task found"],400);
        }
        else
        
        {# set changes
            
        $task->setTitle(json_decode($request->getContent(),true)['title']);
        $task->setResponsible(json_decode($request->getContent(),true)['responsible']);
        $task->setState(json_decode($request->getContent(),true)['state']);
        $em->flush();
        
        #return Json confiramtion
        return $this->json($task,201,[],['groups'=>'extract']);}
    }




}
