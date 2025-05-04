<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Envoie un email de test pour vérifier la configuration',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::OPTIONAL, 'Adresse email du destinataire', 'test@example.com')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $to = $input->getArgument('to');

        $email = (new Email())
            ->from('stoustou419@gmail.com')
            ->to($to)
            ->subject('Test Email')
            ->text('Ceci est un email de test pour vérifier que le système d\'envoi d\'emails fonctionne correctement.')
            ->html('<p>Ceci est un email de test pour vérifier que le système d\'envoi d\'emails fonctionne correctement.</p>');

        try {
            $this->mailer->send($email);
            $io->success('Email envoyé avec succès à ' . $to);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 