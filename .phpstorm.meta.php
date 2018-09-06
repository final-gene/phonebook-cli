<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@final-gene.de>
 */

namespace PHPSTORM_META {

    use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface as LeagueAccessTokenRepositoryInterface;
    use League\OAuth2\Server\Repositories\ClientRepositoryInterface as LeagueClientRepositoryInterface;
    use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface as LeagueRefreshTokenRepositoryInterface;
    use League\OAuth2\Server\Repositories\ScopeRepositoryInterface as LeagueScopeRepositoryInterface;
    use League\OAuth2\Server\Repositories\UserRepositoryInterface as LeagueUserRepositoryInterface;
    use PHPUnit\Framework\TestCase;
    use Prophecy\Argument;
    use Psr\Container\ContainerInterface;
    use Symfony\Component\Console\Command\Command;
    use Vivamera\Repository\AccessTokenRepositoryInterface;
    use Vivamera\Repository\ClientRepositoryInterface;
    use Vivamera\Repository\RefreshTokenRepositoryInterface;
    use Vivamera\Repository\ScopeRepositoryInterface;
    use Vivamera\Repository\UserRepositoryInterface;

    override(
        ContainerInterface::get(),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Argument::type(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Command::getHelper(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        TestCase::createMock(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        TestCase::createPartialMock(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
