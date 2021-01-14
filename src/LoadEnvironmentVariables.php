<?php

namespace App;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
    /**
     * The directory containing the environment file.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The name of the environment file.
     *
     * @var string|null
     */
    protected $fileName;

    /**
     * Create a new loads environment variables instance.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @return void
     */
    public function __construct($path, $name = null)
    {
        $this->filePath = $path;
        $this->fileName = $name;
    }

    /**
     * Setup the environment variables.
     *
     * If no environment file exists, we continue silently.
     *
     * @return void
     */
    public function bootstrap()
    {
        try {
            $this->createDotenv()->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeErrorAndDie([
                'The environment file is invalid!',
                $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a Dotenv instance.
     *
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv()
    {
        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(EnvConstAdapter::class)
            ->addWriter(PutenvAdapter::class)
            ->immutable()
            ->make();


        return Dotenv::create($repository, $this->filePath, $this->fileName);
    }

    /**
     * Write the error information to the screen and exit.
     *
     * @param  string[]  $errors
     * @return void
     */
    protected function writeErrorAndDie(array $errors)
    {
        $output = (new ConsoleOutput)->getErrorOutput();

        foreach ($errors as $error) {
            $output->writeln($error);
        }
    }
}
