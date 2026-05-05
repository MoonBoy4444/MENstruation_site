<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ShopService;
use Throwable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ApiController
{
    public function __construct(private readonly ShopService $shop)
    {
    }

    #[Route('/home', methods: ['GET'])]
    public function home(): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->homePayload());
    }

    #[Route('/catalog', methods: ['GET'])]
    public function catalog(Request $request): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->catalog($request->query->all()));
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function product(int $id): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->productDetail($id));
    }

    #[Route('/auth/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        return $this->run(function () use ($request): array {
            $payload = $this->payload($request);

            return $this->shop->login((string) ($payload['email'] ?? ''), (string) ($payload['password'] ?? ''));
        });
    }

    #[Route('/auth/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        return $this->run(
            fn (): array => $this->shop->register($this->payload($request)),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/reviews', methods: ['POST'])]
    public function review(Request $request): JsonResponse
    {
        return $this->run(
            fn (): array => $this->shop->addReview($this->payload($request)),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/profile/{id}', methods: ['GET'])]
    public function profile(int $id): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->profile($id));
    }

    #[Route('/profile/{id}', methods: ['PUT'])]
    public function updateProfile(int $id, Request $request): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->updateProfile($id, $this->payload($request)));
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function orders(int $id): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->orders($id));
    }

    #[Route('/orders', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        return $this->run(
            fn (): array => $this->shop->createOrder($this->payload($request)),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/admin/dashboard', methods: ['GET'])]
    public function adminDashboard(): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->adminDashboard());
    }

    #[Route('/admin/clients', methods: ['GET'])]
    public function adminClients(): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->adminClients());
    }

    #[Route('/admin/products', methods: ['GET'])]
    public function adminProducts(): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->adminProducts());
    }

    #[Route('/admin/products', methods: ['POST'])]
    public function createAdminProduct(Request $request): JsonResponse
    {
        return $this->run(
            fn (): array => $this->shop->saveProduct($this->payload($request)),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/admin/products', methods: ['PUT'])]
    public function updateAdminProduct(Request $request): JsonResponse
    {
        return $this->run(fn (): array => $this->shop->saveProduct($this->payload($request)));
    }

    private function payload(Request $request): array
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($data)) {
            throw new \RuntimeException('Le corps JSON est invalide.');
        }

        return $data;
    }

    private function run(callable $callback, int $successStatus = JsonResponse::HTTP_OK): JsonResponse
    {
        try {
            return $this->json($callback(), $successStatus);
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    private function json(array $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $status, [], false);
    }
}
