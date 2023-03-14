<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка удаления статьи
 */
#[IsGranted('ROLE_USER')]
#[Route('/admin/articles/{id}/remove', methods: ['POST'])]
#[AsController]
final readonly class RemoveAction
{
    public function __construct(private Articles $articles, private Flush $flush)
    {
    }

    public function __invoke(Article $article): SuccessResponse
    {
        $this->articles->remove($article);
        ($this->flush)();

        return new SuccessResponse();
    }
}
