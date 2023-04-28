<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\SignIn;

use App\Infrastructure\ApiException\ApiErrorCode;
use App\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * @internal
 */
#[TestDox('Аутентификация')]
final class SignInTest extends ApiWebTestCase
{
    #[TestDox('Регистрация выполнена, подтвержден email, аутентификация выполнена')]
    public function testCorrectCredentials(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

        self::assertNotEmpty($response['token']);
    }

    #[TestDox('Неверный пароль')]
    public function testInvalidPassword(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertAccessDenied($response);
    }

    #[TestDox('Пользователя с таким email не существует')]
    public function testInvalidEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::request('GET', "/api/confirm-email/{$confirmToken}");

        $body = [];
        $body['email'] = 'invalid@example.com';
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertAccessDenied($response);
    }

    #[TestDox('Неправильный запрос')]
    public function testBadRequest(): void
    {
        $body = json_encode(['email' => 'test', 'password' => ''], JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/sign-in', $body, newClient: true, disableValidateRequestSchema: true);

        self::assertBadRequest($response);
    }

    #[TestDox('Логиниться можно только с подтвержденным Email, повторная отправка письма выполнена')]
    public function testNotConfirmedEmail(): void
    {
        $body = [];
        $userEmail = $body['email'] = 'first@example.com';
        $password = $body['password'] = 'password';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request('POST', '/api/sign-up', $body, newClient: true);

        $body = [];
        $body['email'] = $userEmail;
        $body['password'] = $password;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/sign-in', $body);
        self::assertApiError($response, ApiErrorCode::EmailIsNotConfirmed->value);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
    }
}
