<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

/**
 * @internal
 */
final class Setting extends ApiWebTestCase
{
    /**
     * @return array<int, array{
     *     id: string,
     *     type: string,
     *     value: string,
     *     isPublic: bool,
     *     createdAt: string,
     *     updatedAt: string|null,
     * }>
     */
    public static function list(): array
    {
        $token = User::auth();

        $response = self::request('GET', '/api/admin/settings', token: $token);

        self::assertSuccessResponse($response);

        /** @var array<int, array{
         *     id: string,
         *     type: string,
         *     value: string,
         *     isPublic: bool,
         *     createdAt: string,
         *     updatedAt: string|null,
         * }> $settings */
        $settings = self::jsonDecode($response->getContent());

        return $settings;
    }

    /**
     * @return array<int, array{
     *     type: string,
     *     value: string,
     * }>
     */
    public static function publicList(): array
    {
        $response = self::request('GET', '/api/settings');

        self::assertSuccessResponse($response);

        /** @var array<int, array{
         *     type: string,
         *     value: string,
         * }> $settings */
        $settings = self::jsonDecode($response->getContent());

        return $settings;
    }
}
