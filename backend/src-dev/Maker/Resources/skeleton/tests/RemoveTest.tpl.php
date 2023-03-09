<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 *
 * @testdox Создание <?php echo $entity_classname."\n"; ?>
 */
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    /**
     * @testdox <?php echo $entity_classname; ?> удален
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        $<?php echo $entity_classname_small; ?>Id1 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);
        $<?php echo $entity_classname_small; ?>Id2 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id1}/remove", token: $token);
        self::assertSuccessContentResponse($response);

        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id1}", token: $token);
        self::assertNotFound($response);

        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id2}", token: $token);
        self::assertSuccessResponse($response);
    }

    /**
     * @testdox <?php echo $entity_classname; ?> не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $token);

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();
        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}/remove", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}/remove", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
