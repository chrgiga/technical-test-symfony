<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddMediaToUserEventCommand extends Command
{
    private $entityManager;
    protected static $defaultName = 'event:add-media';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Associates a file with an event and user')
            ->addArgument('userId', InputArgument::REQUIRED, 'The user id')
            ->addArgument('eventId', InputArgument::REQUIRED, 'The event id')
            ->addArgument('uri', InputArgument::REQUIRED, 'The file path or url')
            ->addOption('printMetaData', null, InputOption::VALUE_NONE, 'Print meta data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $printMetaData = $input->getOption('printMetaData') ?: 0;
        $userId = $input->getArgument('userId');
        $eventId = $input->getArgument('eventId');
        $uri = $input->getArgument('uri');

        if ($uri) {
            $io->note(sprintf('Reading file from : %s', $uri));
        }

        $metaData = $this->getMetadata($uri);
        $user = $this->getUserById($userId);

        if (is_null($user) ) {
            $io->error('The indicated user was not found.');

            return 0;
        }

        $event = $this->getEventById($eventId);

        if (is_null($user) || is_null($event)) {
            $io->error('The indicated event was not found.');

            return 0;
        }

        $this->addMediaToEvent($user, $event, $metaData);

        if ($printMetaData) {
            $io->block(json_encode($metaData));
        }

        $io->success('The file has been processed successfully.');

        return 0;
    }

    private function getMetadata($fileName)
    {
        if (!$fp = fopen($fileName, 'r')) {
            trigger_error("Incapaz de abrir la URL ($fileName)", E_USER_ERROR);
        }

        $meta = stream_get_meta_data($fp);

        fclose($fp);

        return $meta;
    }

    private function getUserById($userId) {
        $repo = $this->entityManager->getRepository(User::class);

        return $repo->find($userId);
    }

    private function getEventById($eventId) {
        $repo = $this->entityManager->getRepository(Event::class);

        return $repo->find($eventId);
    }

    private function addMediaToEvent($user, $event, $metaData)
    {
        $media = new Media();
        $media->setMetadata($metaData)
            ->setName(basename($metaData['uri']))
            ->setUser($user)
            ->setEvent($event)
        ;

        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }
}
