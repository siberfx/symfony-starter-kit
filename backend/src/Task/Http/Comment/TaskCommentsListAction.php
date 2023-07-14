<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Task\Query\Comment\FindAll\CommentData;
use App\Task\Query\Comment\FindAll\FindAllCommentQuery;
use App\Task\Query\Comment\FindAll\FindAllCommentsByTaskIdAndUserId;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка списка комментариев для задачи по пользователю
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/comments', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskCommentsListAction
{
    public function __construct(private FindAllCommentsByTaskIdAndUserId $findAllComments)
    {
    }

    /**
     * @return CommentData[]
     */
    public function __invoke(
        #[ValueResolver(UidValueResolver::class)] Uuid $id,
        #[ValueResolver(UserIdArgumentValueResolver::class)] UserId $userId,
    ): array {
        return ($this->findAllComments)(new FindAllCommentQuery($id, $userId->value));
    }
}
