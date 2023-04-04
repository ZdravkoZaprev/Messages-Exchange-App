<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RabbitMQService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends  AbstractController
{
    /**
     * @Route("/api/login", methods={"POST"})
     */
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        JWTTokenManagerInterface $jwtManager
    ): Response {
        // Retrieve user input from request body
        $input = json_decode($request->getContent(), true);
        $email = $input['email'];
        $password = $input['password'];

        // Validate user input
        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        // Find user by email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $isPasswordValid = $passwordEncoder->isPasswordValid($user, $password);
        if (!$isPasswordValid) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Generate JWT token
        $token = $jwtManager->create($user);

        // Return success response with token
        return new Response($token, Response::HTTP_OK);
    }

    /**
     * @Route("/api/register", methods={"POST"})
     */
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        RabbitMQService $rabbitMQService
    ): Response {
        $input = json_decode($request->getContent(), true);
        $email = trim($input['email']);
        $password = $input['password'];
        $firstName = trim($input['firstName']);
        $lastName = trim($input['lastName']);

        // Validate user input
        if (!$email || !$password || !$firstName || !$lastName) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        // Check if  email already exist
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if ($user) {
            return new JsonResponse(['error' => 'Email already taken'], Response::HTTP_CONFLICT);
        }

        // Create new user object
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        // Encrypt user password
        $encodedPassword = $passwordEncoder->hashPassword($user, $password);
        $user->setPassword($encodedPassword);

        // Save user to database
        $entityManager->persist($user);
        $entityManager->flush();

        // Send message to RabbitMQ
        $message = [
            'user_id' => $user->getId(),
            'text' => 'Welcome to our platform',
            'type' => 'System',
            'recipient' => $user->getEmail()
        ];
        $routingKey = 'registered_users_routing';
        $rabbitMQService->publishMessage('notification_exchange', $routingKey, $message);

        // Return success response
        return new JsonResponse(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/search/{term}", methods={"POST"})
     */
    public function search(string $term, EntityManagerInterface $entityManager): JsonResponse
    {
        // Search for users that match the given search term
        $users = $entityManager->getRepository(User::class)->findyByTerm($term);

        $data = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
            ];
        }, $users);

        return $this->json($data);
    }
}
