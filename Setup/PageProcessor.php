<?php

namespace Pinpoint\CmsImporter\Setup;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PageProcessor
 * @package Pinpoint\CmsImporter\Setup
 */
class PageProcessor extends Processor
{
    /**
     * @var string
     */
    const CMS_TYPE = 'page';

    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $cmsPageFactory;

    /**
     * PageProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Cms\Model\PageFactory $cmsPageFactory
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Cms\Model\PageFactory $cmsPageFactory,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->pageRepository = $pageRepository;
        $this->cmsPageFactory = $cmsPageFactory;

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

                $pageData = $this->parsePageInfo($this->getCmsContent($filePath));
                try {
                    $page = $this->pageRepository->getById($cmsName);
                    if ($this->getFileUpdatedAt($filePath) > strtotime($page->getUpdateTime())) {
                        $updated = true;
                    }
                } catch (\Exception $ex) {
                    $page = $this->cmsPageFactory->create();
                    $page->setData([
                        'identifier' => $cmsName,
                        'stores' => ['0'],
                        'is_active' => 1,
                        'title' => ucwords(str_replace('-', ' ', $cmsName)),
                        'page_layout' => '1column'
                    ]);
                    $updated = true;
                }

                if ($updated) {
                    foreach ($pageData as $key => $value) {
                        $page->setData($key, $value);
                    }

                    $output->writeln('Updated/Created CMS page: ' . $cmsName);
                    $this->pageRepository->save($page);
                }
            }
        }
    }

    /**
     * @param $content
     * @return array
     */
    protected function parsePageInfo($content)
    {
        preg_match_all('/\<\?php(.*?)\?\>/ms', $content, $matches, PREG_SET_ORDER, 0);
        eval($matches[0][1]);
        $variables = [];

        if (isset($title)) {
            $variables['title'] = $title;
        }

        if (isset($isActive)) {
            $variables['is_active'] = $isActive;
        }

        if (isset($pageLayout)) {
            $variables['page_layout'] = $pageLayout;
        }

        if (isset($metaKeywords)) {
            $variables['meta_keywords'] = $metaKeywords;
        }

        if (isset($metaDescription)) {
            $variables['meta_description'] = $metaDescription;
        }

        if (isset($contentHeading)) {
            $variables['content_heading'] = $contentHeading;
        }

        if (isset($layoutXml)) {
            $variables['layout_update_xml'] = $layoutXml;
        }

        if (isset($themeId)) {
            $variables['custom_theme'] = $themeId;
        }

        if (isset($stores)) {
            $variables['stores'] = $stores;
        } else {
            $variables['stores'] = ['0'];
        }

        preg_match_all('/\?\>\n(.+)/ms', $content, $matches, PREG_SET_ORDER, 0);
        if (count($matches)) {
            $variables['content'] = $matches[0][1];
        } else {
            $variables['content'] = '';
        }

        return $variables;
    }
}
