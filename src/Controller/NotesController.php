<?php

namespace App\Controller;

use App\Entity\Notes;
use App\Entity\TodoList;
use App\Form\AddNoteType;
use App\Form\EditFormType;
use App\Form\TodoListType;
use App\Form\DoneNoteType;
use App\Repository\NotesRepository;
use App\Repository\TodoListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class NotesController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/', name: 'app_notes')]
    public function index(): Response
    {
        $currentUser = $this->getUser();

        $todoLists = $this->em->getRepository(TodoList::class)->findBy(['user' => $currentUser ? $currentUser : null]);

        if ($currentUser) {
            $doneNotes = $this->em->getRepository(Notes::class)->findBy(['user' => $currentUser, 'done' => true]);
        } else {
            $doneNotes = $this->em->getRepository(Notes::class)->findBy(['user' => null, 'done' => true]);
        }

        $form = $this->createForm(AddNoteType::class);
        $doneNoteForm = $this->createForm(DoneNoteType::class);
        $todoForm = $this->createForm(TodoListType::class);
        $editForm = $this->createForm(EditFormType::class);

        return $this->render('index.html.twig', [
            'doneForm' => $doneNoteForm,
            'todoForm' => $todoForm,
            'form' => $form,
            'doneNotes' => $doneNotes,
            'todoLists' => $todoLists,
            'editForm' => $editForm,
        ]);
    }

    #[Route('/add-notes', name: 'add_notes', methods: ['POST'])]
    public function addNotes(Request $request): Response
    {
        $notes = new Notes();
        $currentUser = $this->getUser();
        $todoid = $request->get('todoid');
        if ($currentUser) {
            $todolist = $this->em->getRepository(TodoList::class)->findOneBy(['user' => $currentUser, 'id' => $todoid]);
        } else {
            $todolist = $this->em->getRepository(TodoList::class)->findOneBy(["user" => null, 'id' => $todoid]);
        }

        $notes->setDone(false);

        if ($currentUser) {
            $notes->setUserId($currentUser);
        }
        $notes->setTodo($todolist);
        $form = $this->createForm(AddNoteType::class, $notes);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($notes);
            $this->em->flush();
            $this->addFlash("message", "Inserted Successfully");
            return $this->redirectToRoute('app_notes');
        }
        return $this->redirectToRoute('app_notes');
    }
    #[Route('/add-done-notes', name: 'add_done_notes', methods: ['POST'])]
    public function addDoneNotes(Request $request): Response
    {
        $notes = new Notes();
        $currentUser = $this->getUser();
        // if ($currentUser) {
        //     $todoList = $this->em->getRepository(TodoList::class)->findOneBy(['user' => $currentUser]);
        // } else {
        //     $todoList = $this->em->getRepository(TodoList::class)->findOneBy(["user" => null]);
        // }

        $notes->setDone(true);

        if ($currentUser) {
            $notes->setUserId($currentUser);
        }
        // $notes->setTodo($todoList);
        $form = $this->createForm(DoneNoteType::class, $notes);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($notes);
            $this->em->flush();
            $this->addFlash("message", "Inserted Done notes Successfully");
            return $this->redirectToRoute('app_notes');
        }
        return $this->redirectToRoute('app_notes');
    }

    #[Route('/addTodo', name: 'add_todo', methods: ['POST'])]
    public function addTodo(Request $request): Response
    {
        $currentUser = $this->getUser();
        $todoList = new TodoList();
        if ($currentUser) {
            $todoList->setUser($currentUser);
        }
        $currentDateTime = new \DateTimeImmutable();
        $todoList->setCreatedAt($currentDateTime);
        $todoList->setUpdatedAt($currentDateTime);
        $form = $this->createForm(TodoListType::class, $todoList);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($todoList);
            $this->em->flush();
            $this->addFlash("message", "Add new todolist Successfully");
            return $this->redirectToRoute('app_notes');
        }
        return $this->redirectToRoute('app_notes');
    }
    #[Route("/edit/{id}", name: "edit-note")]
    public function editNote(Request $request, $id): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser) {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => $currentUser, 'id' => $id]);
        } else {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => null, 'id' => $id]);
        }

        $editForm = $this->createForm(AddNoteType::class, $note);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->persist($note);
            $this->em->flush();
            $this->addFlash('message', 'Note updated successfully.');

            return $this->redirectToRoute('app_notes');
        }

        return $this->redirectToRoute("app_notes");
    }
    #[Route('/done-note/{id}', name: 'done_note', methods: ['POST'])]
    public function doneNote(Request $request, $id): Response
    {
        $currentUser = $this->getUser();
        $doneNotes = $request->getContent();

        if ($currentUser) {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => $currentUser, 'id' => $id]);
        } else {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => null, 'id' => $id]);
        }

        return $this->redirectToRoute("app_notes");
    }

    #[Route("/delete-note/{id}", name: "delete-note")]
    public function deleteNote($id): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser) {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => $currentUser, 'id' => $id]);
        } else {
            $note = $this->em->getRepository(Notes::class)->findOneBy(['user' => null, 'id' => $id]);
        }

        $this->em->remove($note);
        $this->em->flush();
        $this->addFlash('message', 'Successfully deleted');
        return $this->redirectToRoute("app_notes");
    }
    #[Route("/delete-todo/{id}", name: "delete-todo")]
    public function deleteTodoList(TodoList $todoList): Response
    {

        $this->em->remove($todoList);
        $this->em->flush();

        $this->addFlash('message', 'Successfully deleted');
        return $this->redirectToRoute("app_notes");
    }
}
