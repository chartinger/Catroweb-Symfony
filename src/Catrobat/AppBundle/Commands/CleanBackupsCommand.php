<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\ApkRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CleanBackupsCommand extends ContainerAwareCommand
{
  private $apk_repository;
  private $program_manager;
  private $output;

  public function __construct()
  {
    parent::__construct();
  }

  protected function configure()
  {
    $this->setName('catrobat:clean:backup')
         ->setDescription('Delete all Backups')
        ->addArgument('backupfile', InputArgument::OPTIONAL, 'backup file (tar.gz)')
        ->addOption('all',null, InputOption::VALUE_NONE, 'all backups are deleted');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $backupdir = realpath($this->getContainer()->getParameter('catrobat.backup.dir'));
    if($input->getOption("all"))
    {
      $files = glob($backupdir.'/*'); // get all file names
      foreach($files as $file){ // iterate files
        $ext = pathinfo($file,PATHINFO_EXTENSION);
        if($ext == "gz" && is_file($file))
          unlink($file); // delete file
      }
      $this->output->writeln('All backups deleted!');
    }else if($input->getArgument("backupfile")){

      $backupfile = $input->getArgument("backupfile");
      $files = scandir($backupdir);
      if(!in_array($backupfile,$files))
      {
        throw new \Exception('Backupfile not found.');
      }
      if(is_file($backupdir."/".$backupfile))
        unlink($backupdir."/".$backupfile);

      $this->output->writeln('Backup deleted!');
    }else{
        throw new \Exception('No Arguments');
    }

    return 0;
  }
} 