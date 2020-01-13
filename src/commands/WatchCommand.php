<?php

namespace AYashenkov\commands;

use AYashenkov\loggers\FileLogger;
use AYashenkov\LogMessage;
use AYashenkov\services\SenderInterfase;
use AYashenkov\services\SysCallSender;
use AYashenkov\Status;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class WatchCommand extends Command {

    /** @var $logger FileLogger */
    private $logger;
    /** @var $sender SysCallSender */
    private $sender;
    /** @var $factory LockFactory */
    private $factory;

    /**
     * Command constructor.
     */
    public function __construct()
    {
        $this->logger = new FileLogger();
        //init mutex factory (using FlockStore (Filemutex))
        $this->factory = new LockFactory(new FlockStore(sys_get_temp_dir()));

        parent::__construct();
    }

    /**
     *
     */
    public function configure()
    {
        $this->setName('watch')
            ->setDescription('Watching dirs and parsing logs.')
            ->setHelp('Please before start command check you ".env" file.');
    }

    /**
     * @param SenderInterfase $senderInterfase
     * @return WatchCommand
     */
    private function setSender(SenderInterfase $senderInterfase): WatchCommand
    {
        $this->sender = $senderInterfase;
        return $this;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setSender(new SysCallSender($this->logger));

        $lock = $this->factory->createLock('php-noritsu-watch');

        if (!$lock->acquire()) {
            $this->logger->warning('Mutex! Exit.'); die('Mutex! Exit.'.PHP_EOL);
        }
        $baseDir = $_ENV['WATCH_DIR'].DIRECTORY_SEPARATOR;
        $finder = new Finder();
        /*
         * Последовательно пройдем по всем хотфолдерам из ENV
         */
        foreach (explode(',', $_ENV['FOLDERS']) as $hotFolder) {
            /*
             * Просканируем директории, обработанные Noritsu
             */
            $scanned = $finder->in($baseDir.$hotFolder);
            foreach ($scanned->directories() as $dir) {
                if (strpos($dir->getRelativePathname(), '.d') !== false) {
                    /*
                     * Прочитаем файл с информацией о задании для оборудования
                     */
                    $batchItemId = $this->getBatchItemId($output, $dir);
                    if (!$batchItemId) {
                        continue;
                    }
                    $this->sender->send(new LogMessage($batchItemId, 1, "QSS_SUCCESS", "QSS_SUCCESS"));
                    $this->removeDir($dir->getPathName());

                } elseif (strpos($dir->getRelativePathname(), '.e') !== false) {
                    /*
                     * Прочитаем файл с информацией о задании для оборудования
                     */
                    $batchItemId = $this->getBatchItemId($output, $dir);
                    if (!$batchItemId) {
                        continue;
                    }
                    /*
                     * Прочитаем файл с информацией об ошибке
                     */
                    $errorPath = $dir->getPathName().DIRECTORY_SEPARATOR.$_ENV['NORITSU_ERROR'];
                    $errorFile = @fopen($errorPath, 'r');
                    if ($errorFile) {
                        while (($line = fgets($errorFile)) !== false) {
                            if (strlen($line) > 1) {
                                $description = "ERR_UNKN";
                                $errorCode = Status::CODE['ERR_UNKN'];

                                preg_match('/:\s*(\w+)\((\d*)\)/', $line, $outputArray);
                                if (count($outputArray) > 0) {
                                    $errorCode = (int) $outputArray[2];
                                    $description = $outputArray[1];
                                }
                                $line = str_replace(PHP_EOL, '', $line);
                                $this->sender->send(new LogMessage($batchItemId, $errorCode, $description, $line));
                            }
                        }
                        fclose($errorFile);
                        $this->removeDir($dir->getPathName());
                    } else {
                        // если не смогли открыть файл лога ошибок
                        $this->sender->send(
                            new LogMessage(
                                0,
                                Status::CODE['ERR_UNKN'],
                                "ERR_UNKN",
                                "При печати заказа на оборудовании произошла неизвестная ошибка"
                            )
                        );
                        $this->removeDir($dir->getPathName());
                    }
                }
            }
        }
        $lock->release();
    }

    /**
     * @param string $dir
     */
    private function removeDir(string $dir): void
    {
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    /**
     * @param OutputInterface $output
     * @param $dir
     * @return integer|null
     */
    private function getBatchItemId(OutputInterface $output, $dir): ?int
    {
        $taskPath = $dir->getPathName() . DIRECTORY_SEPARATOR . $_ENV['NORITSU_TASK'];
        $orderTask = @parse_ini_file($taskPath, true);
        if (!$orderTask) {
            $this->logger->warning("File {$_ENV['NORITSU_TASK']} not found.");
            $output->writeln("File {$_ENV['NORITSU_TASK']} not found.");
            return null;
        }
        $batchItemId = !empty($orderTask['Order']['JobID']) ? $orderTask['Order']['JobID'] : null;
        return $batchItemId;
    }
}
