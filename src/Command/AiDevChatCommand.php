<?php

declare(strict_types=1);

namespace Dantweb\AiDevChat\Command;

use OpenAI\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AiDevChatCommand extends Command
{
    protected static $defaultName = 'oe:ai:devchat';

    private Client $openAiClient;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->openAiClient = $client;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Chat with an AI model using OpenAI SDK')
            ->setHelp('Use this command to interact with an AI model directly from your terminal.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('AI Dev Chat - OpenAI Integration');

        $io->writeln("Type your message below to start chatting with AI. Type 'exit' to quit.");

        while (true) {
            $userInput = $io->ask('You');

            if (strtolower(trim($userInput)) === 'exit') {
                $io->success('Exiting AI Dev Chat. Goodbye!');
                break;
            }

            try {
                $response = $this->openAiClient->completions()->create([
                    'model' => 'text-davinci-003',
                    'prompt' => $userInput,
                    'max_tokens' => 200,
                ]);

                $aiResponse = $response['choices'][0]['text'] ?? 'No response from AI.';

                // Colorize output if it contains code
                if (preg_match('/```/', $aiResponse)) {
                    $aiResponse = preg_replace_callback(
                        '/```(.*?)```/s',
                        function ($matches) {
                            return "\033[32m" . $matches[1] . "\033[0m";
                        },
                        $aiResponse
                    );
                }

                $output->writeln("\n<info>AI:</info> $aiResponse\n");
            } catch (\Exception $e) {
                $io->error('Error communicating with the AI: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
