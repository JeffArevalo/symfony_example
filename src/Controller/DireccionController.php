<?php

namespace App\Controller;

use App\Entity\Direccion;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/direccion', name: 'app_direccion')]
class DireccionController extends AbstractController
{
    #[Route('', name: 'app_direccion_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $direccion = new Direccion();
        $usuario = $entityManager->getRepository(Usuario::class)->find($request->request->get('usuario'));

        if (!$usuario) {
            return $this->json(['error' => 'No se encontro el usuario.'], 404);
        }


        $direccion->setUsuario($usuario);
        $direccion->setDepartamento($request->request->get('departamento'));
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($direccion);

        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();

        return $this->json([
            'message' => 'Se guardo la nuevo direccion con id ' . $direccion->getId()
        ]);
    }

    #[Route('', name: 'app_direccion_read_all', methods: ['GET'])]
    public function readAll(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $repositorio = $entityManager->getRepository(Direccion::class);

        $limit = $request->get('limit', 5);

        $page = $request->get('page', 1);

        $direccions = $repositorio->findAllWithPagination($page, $limit);

        $total = $direccions->count();

        $lastPage = (int) ceil($total / $limit);

        $data = [];

        foreach ($direccions as $direccion) {
            $data[] = [
                'id' => $direccion->getId(),
                'usuario' => $direccion->getUsuario(),
                'departamento' => $direccion->getDepartamento(),
            ];
        }

        return $this->json(['data' => $data, 'total' => $total, 'lastPage' => $lastPage]);
    }

    #[Route('/{id}', name: 'app_direccion_read_one', methods: ['GET'])]
    public function readOne(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $direccion = $entityManager->getRepository(Direccion::class)->find($id);

        if (!$direccion) {
            return $this->json(['error' => 'No se encontro la direccion.'], 404);
        }

        return $this->json([
            'id' => $direccion->getId(),
            'idUsuario' => $direccion->getUsuario()->getId(),
            'nombreUsuario' => $direccion->getUsuario()->getNombre(),
            'edadUsuario' => $direccion->getUsuario()->getEdad(),
            'departamento' => $direccion->getDepartamento(),
        ]);
    }

    #[Route('/{id}', name: 'app_direccion_edit', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {

        // Busca la direccion por id
        $direccion = $entityManager->getRepository(Direccion::class)->find($id);

        // Si no lo encuentra responde con un error 404
        if (!$direccion) {
            return $this->json(['error' => 'No se encontro la direccion con id: ' . $id], 404);
        }

        // Obtiene los valores del body de la request
        $usuario = $request->request->get('nombre');
        $departamento = $request->request->get('departamento');

        // Si no envia uno responde con un error 422
        if ($usuario == null || $departamento == null) {
            return $this->json(['error' => 'Se debe enviar los datos de la direccion.'], 422);
        }

        // Se actualizan los datos a la entidad
        $direccion->setUsuario($usuario);
        $direccion->setDepartamento($departamento);

        $data = ['id' => $direccion->getId(), 'usuario' => $direccion->getUsuario(), 'departamento' => $direccion->getDepartamento()];

        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();

        return $this->json(['message' => 'Se actualizaron los datos de la direccion.', 'data' => $data]);
    }

    #[Route('/{id}', name: 'app_direccion_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {

        // Busca la direccion por id
        $direccion = $entityManager->getRepository(Direccion::class)->find($id);

        // Si no lo encuentra responde con un error 404
        if (!$direccion) {
            return $this->json(['error' => 'No se encontro la direccion con id: ' . $id], 404);
        }

        // Remueve la entidad
        $entityManager->remove($direccion);

        $data = ['id' => $direccion->getId(), 'usuario' => $direccion->getUsuario(), 'departamento' => $direccion->getDepartamento()];

        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();

        return $this->json(['message' => 'Se elimino la direccion.', 'data' => $data]);
    }
}
