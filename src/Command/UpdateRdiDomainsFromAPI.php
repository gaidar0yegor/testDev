<?php

namespace App\Command;

use App\DTO\CronSchedule;
use App\Entity\RdiDomain;
use Cron\CronBundle\Entity\CronJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateRdiDomainsFromAPI extends Command
{
    protected static $defaultName = 'app:update-rdi-domains-from-api';

    private HttpClientInterface $client;
    private EntityManagerInterface $em;
    private string $cronJobName;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->client = $client;
        $this->em = $em;
        $this->cronJobName = "update-projets-rdi-scores";
    }

    protected function configure()
    {
        $this
            ->setDescription('Met à jour domaines d\'activité from API : api.archives-ouvertes.fr')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emptyDomainTable = count($this->em->getRepository(RdiDomain::class)->findAll()) === 0;

        if (!$emptyDomainTable) {
            $io->info('Rdi domains are already generated !!');
            return Command::SUCCESS;
        }

        $io->info('Start :: Call API');

        $response = $this->client->request('GET', "https://api.archives-ouvertes.fr/ref/domain/?q=*:*&wt=json&rows=100000000000");

        $response = json_decode($response->getContent(),true);

        if (!isset($response['response']) || !isset($response['response']['docs'])) {
            $io->error('Communication problem with the API.');
            return Command::FAILURE;
        }

        $io->info('Get API response');

        foreach ($response['response']['docs'] as $domain) {
            $cle = explode(' = ', $domain['label_s'])[0];
            $nom = explode(' = ', $domain['label_s'])[1];

            $this->em->persist(RdiDomain::create(substr_count($cle, '.'), $cle, preg_replace('/\[(.*?)\]/', '', $nom)));
        }

        $this->em->flush();

        $io->success('All RDI domains are generated !!');

        $cronJob = $this->em->getRepository(CronJob::class)->findOneBy(['name' => $this->cronJobName]);

        if (null === $cronJob){
            $cronJob = (new CronJob())
                ->setDescription('Mettre à jour les scores RDI des projets')
                ->setCommand(UpdateProjetsRdiScoreCommand::getDefaultName())
                ->setName($this->cronJobName)
                ->setEnabled(true)
                ->setSchedule("*/5 * * * *");

            $this->em->persist($cronJob);
        }

        $this->em->flush();

        $io->success('Cron Job is created !!');

        return Command::SUCCESS;
    }
}
