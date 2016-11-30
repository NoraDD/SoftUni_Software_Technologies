<?php

namespace SoftUniBlogBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SoftUniBlogBundle\Entity\Category;
use SoftUniBlogBundle\Entity\Role;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\CategoryType;
use SoftUniBlogBundle\Form\UserEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/categories")
 * Class CategoryController
 * @package SoftUniBlogBundle\Controller\Admin
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="admin_categories")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('admin/category/list.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/create", name="admin_categories_create")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCategory(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/category/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="admin_categories_edit")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editCategory($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/category/edit.html.twig', ['category' => $category, 'form' => $form->createView()]);
    }

    /**
     * @Route("/delete/{id}", name="admin_categories_delete")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCategory($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($category->getArticles() as $article) {
                $em->remove($article);
            }

            $em->remove($category);
            $em->flush();

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/category/delete.html.twig', ['category' => $category, 'form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="admin_user_edit")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editUser($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user === null){
            return $this->redirectToRoute("admin_users");
        }

        $originalPassword = $user->getPassword();

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $rolesRequest = $user->getRoles();
            $roleRepository = $this->getDoctrine()->getRepository(Role::class);
            $roles = [];

            foreach ($rolesRequest as $roleName){
                $roles[] = $roleRepository->findOneBy(['name' => $roleName]);
            }

            $user->setRoles($roles);

            if ($user->getPassword()){
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
            }
            else{
                $user->setPassword($originalPassword);
            }

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    /**
     * @Route("/delete/{id}", name="admin_user_delete")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user === null){
            return $this->redirectToRoute("admin_users");
        }

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();

            foreach ($user->getArticles() as $article){
                $em->remove($article);
            }

            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/delete.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }




}

