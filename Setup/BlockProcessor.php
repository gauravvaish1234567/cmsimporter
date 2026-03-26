<?php

namespace Pinpoint\CmsImporter\Setup;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BlockProcessor
 * @package Pinpoint\CmsImporter\Setup
 */
class BlockProcessor extends Processor
{
    /**
     * @var string
     */
    const CMS_TYPE = 'block';

    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    protected $blockRepository;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $cmsBlockFactory;

    /**
     * BlockProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     * @param \Magento\Cms\Model\BlockFactory $cmsBlockFactory
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Cms\Model\BlockRepository $blockRepository,
        \Magento\Cms\Model\BlockFactory $cmsBlockFactory,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->blockRepository = $blockRepository;
        $this->cmsBlockFactory = $cmsBlockFactory;

        parent::__construct($scopeConfig, $storeManager, $themeProvider, $directoryList, $io);
    }

    /**
     * @param OutputInterface $output
     * @param $theme
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process(OutputInterface $output, $theme)
    {
        $this->setTheme($theme);
        if ($this->folderHasFiles(self::CMS_TYPE)) {
            foreach ($this->getFolderFiles(self::CMS_TYPE) as $filePath) {
                $cmsName = $this->getCmsNameFromFileName($filePath);
                $updated = false;

                $blockData = $this->parseBlockInfo($this->getCmsContent($filePath));
                try {
                    $block = $this->blockRepository->getById($cmsName);
                    if ($this->getFileUpdatedAt($filePath) > strtotime($block->getUpdateTime())) {
                        $updated = true;
                    }
                } catch (\Exception $ex) {
                    $block = $this->cmsBlockFactory->create();
                    $block->setData([
                        'identifier' => $cmsName,
                        'stores' => ['0'],
                        'is_active' => 1,
                        'title' => ucwords(str_replace('-', ' ', $cmsName))
                    ]);
                    $updated = true;
                }

                if ($updated) {
                    foreach ($blockData as $key => $value) {
                        $block->setData($key, $value);
                    }

                    $output->writeln('Updated/Created CMS Block: ' . $cmsName);
                    $this->blockRepository->save($block);
                }
            }
        }
    }

    /**
     * @param $content
     * @return array
     */
    protected function parseBlockInfo($content)
    {
        preg_match_all('/\<\?php(.*?)\?\>/ms', $content, $matches, PREG_SET_ORDER, 0);
        eval($matches[0][1]);
        $variables = [];

        if (isset($title)) {
            $variables['title'] = $title;
        }

        if (isset($isActive)) {
            $variables['is_active'] = $isActive;
        } else {
            $variables['is_active'] = 1;
        }

        if (isset($stores)) {
            $variables['stores'] = $stores;
        } else {
            $variables['stores'] = ['0'];
        }

        preg_match_all('/\?\>\n(.+)/ms', $content, $matches, PREG_SET_ORDER, 0);
        $variables['content'] = $matches[0][1];

        return $variables;
    }
}
