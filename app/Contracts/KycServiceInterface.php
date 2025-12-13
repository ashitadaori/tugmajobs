<?php

namespace App\Contracts;

interface KycServiceInterface
{
    public function createSession(array $payload = []): array;
    public function getSessionStatus(string $sessionId): array;
    public function verifySignature(string $payload, string $signature): bool;
    public function processWebhookEvent(array $event): void;
}
