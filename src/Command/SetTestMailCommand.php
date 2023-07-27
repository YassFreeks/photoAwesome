<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:set-test-mail',
    description: 'Add a short description for your command',
)]
class SetTestMailCommand extends Command
{

    public function __construct(
        private HttpClientInterface $httpClient
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->httpClient->request('POST', "https://api.brevo.com/v3/smtp/email", [
            "headers" => [
                "accept" => "application/json",
                "api-key" => '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',
                "content-type" => "application/json",
            ],

            'json' => [
                "sender" => [
                    'name' => 'John',
                    'email' => 'yasso1992@hotmail.it',
                ],
                "to" => [
                    [
                        'email' => 'yasso1992@hotmail.it',
                        'name' => 'Yass',
                    ]
                ],
                "subject" => "warning",
                "htmlContent" => '<p>bonjour</p>'
            ]
        ]);
        
                

        return Command::SUCCESS;
    }
}
