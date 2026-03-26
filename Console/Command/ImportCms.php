<?php

namespace Pinpoint\CmsImporter\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCms
 * @package Pinpoint\CmsImporter\Console\Command
 */
class ImportCms extends Command
{
    /**
     * @var string
     */
    const THEME_OPTION = "theme";

    /**
     * @var \Pinpoint\CmsImporter\Setup\BlockProcessor
     */
    protected $blockProcessor;

    /**
     * @var \Pinpoint\CmsImporter\Setup\PageProcessor
     */
    protected $pageProcessor;

    /**
     * ImportCms constructor.
     * @param \Pinpoint\CmsImporter\Setup\BlockProcessor $blockProcessor
     * @param \Pinpoint\CmsImporter\Setup\PageProcessor $pageProcessor
     * @param null|string $name
     */
    public function __construct(
        \Pinpoint\CmsImporter\Setup\BlockProcessor $blockProcessor,
        \Pinpoint\CmsImporter\Setup\PageProcessor $pageProcessor,
        ?string $name = null
    ) {
        $this->blockProcessor = $blockProcessor;
        $this->pageProcessor = $pageProcessor;

        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $theme = $input->getArgument(self::THEME_OPTION);

        try {
            $this->blockProcessor->process($output, $theme);
            $this->pageProcessor->process($output, $theme);
        } catch (\Exception $ex) {}
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName("pinpoint:importcms");
        $this->setDescription("Adds & Updates CMS Blocks and Pages");

        $this->setDefinition([
            new InputArgument(self::THEME_OPTION, InputArgument::OPTIONAL, "Theme"),
        ]);

        parent::configure();
    }
}