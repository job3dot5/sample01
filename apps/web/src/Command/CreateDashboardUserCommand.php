<?php

declare(strict_types=1);

namespace App\Command;

use App\Security\DashboardUser;
use App\Security\DashboardUserProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:dashboard-user:create',
    description: 'Create or update a dashboard user.',
)]
final class CreateDashboardUserCommand extends Command
{
    public function __construct(
        private readonly DashboardUserProvider $userProvider,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username to create or update.')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Password (omit to be prompted).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userProvider->ensureTable();

        $username = (string) $input->getArgument('username');
        $password = $input->getOption('password');

        if (!\is_string($password) || '' === $password) {
            $helper = $this->getHelper('question');
            if (!$helper instanceof QuestionHelper) {
                throw new \LogicException('Question helper is not available.');
            }
            $question = new Question('Password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        if (!\is_string($password) || '' === $password) {
            $output->writeln('<error>Password is required.</error>');

            return Command::FAILURE;
        }

        $hash = $this->passwordHasher->hashPassword(new DashboardUser($username, ''), $password);

        $updated = $this->userProvider->saveUser($username, $hash);

        $output->writeln(\sprintf(
            '%s user "%s".',
            $updated ? 'Updated' : 'Created',
            $username,
        ));

        return Command::SUCCESS;
    }
}
