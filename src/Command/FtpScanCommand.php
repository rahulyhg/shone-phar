<?php
/**
 * The Shone Security Scanner is used to help a developer determine if the versions of the
 * dependencies he is using are vulnerable to known exploits.
 *
 * @category Shone
 * @package  Scanner
 * @author   Thomas Shone <xsist10@gmail.com>
 */

namespace Shone\Scanner\Command;

use Shone\Scanner\Command\ScanCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use League\Flysystem\Adapter\Ftp;

use Shone\Scanner\Config;

/**
 * The scan command performs the security scan
 *
 * @category Shone
 * @package  Scanner\Command
 * @author   Thomas Shone <xsist10@gmail.com>
 */
class FtpScanCommand extends ScanCommand
{
    protected $ftp_password = '';

    /**
     * Configure our command call
     *
     * @return void
     */
    protected function configure()
    {
        $help = <<<EOT
The scan command starts and submits a scan of a remote ftp folder and uploads a fingerprint file to
the Shone Security servers.

EOT;

        $this
            ->setName('ftpscan')
            ->setDescription('Scan a local project for known software vulnerabilities')
            ->setHelp($help)
            ->setDefinition(array(
                new InputArgument('host', InputArgument::REQUIRED, 'Specify the host of the FTP server.'),
                new InputArgument('path', InputArgument::OPTIONAL, 'Specify a customer path to examine.', '.'),

                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'FTP Port (default is 21).'),
                new InputOption('username', null, InputOption::VALUE_REQUIRED, 'FTP Username (defaults to anonymous).'),
                new InputOption('password', 'p', InputOption::VALUE_NONE, 'FTP Password.'),
                new InputOption('passive', null, InputOption::VALUE_NONE, 'Enable passive FTP.'),
                new InputOption('disable-ssl', 's', InputOption::VALUE_NONE, 'Disable SSL for FTP.'),

                new InputOption('label', null, InputOption::VALUE_REQUIRED, 'Set the label for this scan.'),
                new InputOption('key', null, InputOption::VALUE_REQUIRED, 'Pass an API key.'),
                new InputOption('common-checksum', 'c', InputOption::VALUE_NONE, 'Ignore files that are very common.'),
                new InputOption('no-cert-check', null, InputOption::VALUE_NONE, 'Disable CA certificate checks.'),
            ));
    }

    /**
     * Get the FTP filesystem for this command
     *
     * @param array $config The configuration provided.
     *
     * @return \League\Flysystem\Adapter\Ftp
     * @codeCoverageIgnore
     */
    protected function getFilesystem(array $config)
    {
        return new Ftp($config['ftp']);
    }

    /**
     * Determine configuration for the FTP scan
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input  User inputs
     * @param \Shone\Scanner\Config                           $config Pre-loaded configurations
     *
     * @return array
     */
    protected function getConfig(InputInterface $input, Config $config)
    {
        parent::getConfig($input, $config);

        $this->config['ftp'] = array(
            'host'      => $input->getArgument('host'),
            'username'  => $input->getOption('username') ? $input->getOption('username') : '',
            'password'  => $this->ftp_password,
            'port'      => $input->getOption('port') ? $input->getOption('port') : 21,
            'root'      => $this->config['path'],
            'passive'   => $input->getOption('passive'),
            'ssl'       => !$input->getOption('disable-ssl'),
        );

        return $this->config;
    }

    /**
     * Execute our command call
     *
     * @param Symfony\Component\Console\Input\InputInterface   $input  Input source
     * @param Symfony\Component\Console\Output\OutputInterface $output Output source
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('password')) {
            $dialog = $this->getHelperSet()->get('dialog');
            $this->ftp_password = $dialog->askHiddenResponse($output, 'FTP password:');
        }
        return parent::execute($input, $output);
    }
}
