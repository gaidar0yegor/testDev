<?php

namespace App\Command;

use App\File\DefaultAvatarGenerator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AvatarGenerateInitialsCommand extends Command
{
    protected static $defaultName = 'app:avatar:generate-initials';
    protected static $defaultDescription = 'Génère des avatars par défaut pour les users qui n\'en ont pas encore';

    private DefaultAvatarGenerator $defaultAvatarGenerator;

    private UserRepository $userRepository;

    private EntityManagerInterface $em;

    public function __construct(
        DefaultAvatarGenerator $defaultAvatarGenerator,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        parent::__construct();

        $this->defaultAvatarGenerator = $defaultAvatarGenerator;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findBy([
            'avatar' => null,
        ]);

        foreach ($users as $user) {
            $io->info('Generating avatar for '.$user->getFullname());

            $avatar = $this->defaultAvatarGenerator->generateDefaultAvatarFor($user);

            $user->setAvatar($avatar);
        }

        $io->info('Persist users into database');

        $this->em->flush();

        $io->success('Initial avatars have been generated !');

        return Command::SUCCESS;
    }
}
