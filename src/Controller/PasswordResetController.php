<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email requerido'], 400);
        }

        $usuario = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if (!$usuario) {
            return $this->json(['message' => 'Si el correo existe, se han enviado las instrucciones de recuperación.']);
        }

        $resetToken = bin2hex(random_bytes(32));
        $usuario->setResetToken($resetToken);

        $expiresAt = new \DateTime();
        $expiresAt->modify('+1 hour');
        $usuario->setResetTokenExpiresAt($expiresAt);

        $em->flush();

        return $this->json([
            'message' => 'Si el correo existe, se han enviado las instrucciones de recuperación.',
            'debug_token' => $resetToken
        ]);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['error' => 'Token y nueva contraseña son requeridos'], 400);
        }

        $usuario = $em->getRepository(Usuario::class)->findOneBy(['resetToken' => $token]);

        if (!$usuario) {
            return $this->json(['error' => 'Token inválido'], 400);
        }

        if ($usuario->getResetTokenExpiresAt() < new \DateTime()) {
            return $this->json(['error' => 'El token ha expirado'], 400);
        }

        $hashedPassword = $passwordHasher->hashPassword($usuario, $newPassword);
        $usuario->setPassword($hashedPassword);
        $usuario->setResetToken(null);
        $usuario->setResetTokenExpiresAt(null);

        $em->flush();

        return $this->json(['message' => 'Contraseña actualizada exitosamente']);
    }
}
