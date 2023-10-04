<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\SignUp\Command\SignUp;
use App\User\SignUp\Command\SignUpCommand;
use App\User\SignUp\Command\UserAlreadyExistException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка регистрации
 */
#[Route('/sign-up', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SignUpAction
{
    public function __construct(
        private SignUp $signUp,
        private Flush $flush,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SignUpCommand $signUpCommand,
    ): SuccessResponse {
        try {
            ($this->signUp)($signUpCommand);
            ($this->flush)();
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException([$e->getMessage()]);
        } catch (UserAlreadyExistException $e) {
            throw new ApiBadResponseException(
                errors: [$e->getMessage()],
                apiCode: ApiErrorCode::UserAlreadyExist,
            );
        }

        return new SuccessResponse();
    }
}
