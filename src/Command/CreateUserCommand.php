<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'creates a new user',
)]
class CreateUserCommand extends Command
{
    private const INPUT_OPTION_EMAIL = 'email';
    private const INPUT_OPTION_PASSWORD = 'password';
    private const INPUT_OPTION_ROLES = 'roles';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;

        parent::__construct($this->getName());
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'password')
            ->addOption('roles', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'roles', ['ROLE_USER']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $input->getOption(self::INPUT_OPTION_EMAIL)])) {
            $io->error(sprintf(
                'user with email <%s> already exists',
                $input->getOption(self::INPUT_OPTION_EMAIL)
            ));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($input->getOption(self::INPUT_OPTION_EMAIL));
        $user->setRoles($input->getOption(self::INPUT_OPTION_ROLES));
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $input->getOption(self::INPUT_OPTION_PASSWORD)));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('user successfully created');

        return Command::SUCCESS;
    }
}
