<?php

use App\Domain\Card\CardRepository;
use App\Domain\Reddit\RedditClientId;
use App\Domain\Reddit\RedditClientSecret;
use App\Domain\Reddit\RedditUsername;
use App\Domain\Reddit\RedditUserPassword;
use App\Domain\Slack\SlackWebhookUrl;
use App\Infrastructure\Console\ConsoleCommandContainer;
use App\Infrastructure\Environment\Environment;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Twig\TwigBuilder;
use Dotenv\Dotenv;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use SleekDB\Store;
use Symfony\Component\Console\Application;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

$appRoot = Settings::getAppRoot();

$dotenv = Dotenv::createImmutable($appRoot);
$dotenv->load();

return [
    CardRepository::class => DI\autowire()->constructorParameter('store', new Store('cards', $appRoot.'/database', [
        'auto_cache' => false,
        'timeout' => false,
    ])),
    SlackWebhookUrl::class => DI\factory([SlackWebhookUrl::class, 'fromString'])->parameter('string', $_ENV['SLACK_WEBHOOK_URL_CC']),
    RedditUsername::class => DI\factory([RedditUsername::class, 'fromString'])->parameter('string', $_ENV['REDDIT_USER_NAME']),
    RedditUserPassword::class => DI\factory([RedditUserPassword::class, 'fromString'])->parameter('string', $_ENV['REDDIT_USER_PASSWORD']),
    RedditClientId::class => DI\factory([RedditClientId::class, 'fromString'])->parameter('string', $_ENV['REDDIT_CLIENT_ID']),
    RedditClientSecret::class => DI\factory([RedditClientSecret::class, 'fromString'])->parameter('string', $_ENV['REDDIT_CLIENT_SECRET']),
    // Clock.
    Clock::class => DI\factory([SystemClock::class, 'fromSystemTimezone']),
    // Twig Environment.
    FilesystemLoader::class => DI\create(FilesystemLoader::class)->constructor($appRoot.'/templates'),
    TwigEnvironment::class => DI\factory([TwigBuilder::class, 'build']),
    // Console command application.
    Application::class => function (ConsoleCommandContainer $consoleCommandContainer) {
        $application = new Application();
        foreach ($consoleCommandContainer->getCommands() as $command) {
            $application->add($command);
        }

        return $application;
    },
    // Environment.
    Environment::class => fn () => Environment::from($_ENV['ENVIRONMENT']),
    // Settings.
    Settings::class => DI\factory([Settings::class, 'load']),
];
