<?php

namespace Pinpoint\CmsImporter\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Processor
 * @package Pinpoint\CmsImporter\Setup
 */
abstract class Processor
{
    /**
     *
     */
    const IMPORT_FOLDER = '/design/%s/cms_import/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var null|int
     */
    protected $theme = null;

    /**
     * Processor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->themeProvider = $themeProvider;
        $this->directoryList = $directoryList;
        $this->io = $io;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getImportPath()
    {
        if (!$this->theme) {
            $themeId = $this->scopeConfig->getValue(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                1
            );

            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->themeProvider->getThemeById($themeId);
        } else {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->themeProvider->getThemeById($this->theme);
        }

        return $this->directoryList->getPath(DirectoryList::APP)
            . sprintf(self::IMPORT_FOLDER, $theme->getFullPath());
    }

    /**
     * @param $themeCode
     */
    protected function setTheme($themeCode)
    {
        $this->theme = $themeCode;
    }

    /**
     * @param $filePath
     * @return bool|string
     */
    protected function getCmsContent($filePath)
    {
        return $this->io->read($filePath);
    }

    /**
     * @param $filePath
     * @return mixed
     */
    protected function getCmsNameFromFileName($filePath)
    {
        return $this->io->getPathInfo($filePath)['filename'];
    }

    /**
     * @param $filePath
     * @return bool|int
     */
    protected function getFileUpdatedAt($filePath)
    {
        return \filemtime($filePath);
    }

    /**
     * @param $cmsType
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function folderHasFiles($cmsType)
    {
        return count($this->getFolderFiles($cmsType));
    }

    /**
     * @param $cmsType
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getFolderFiles($cmsType)
    {
        return $this->io->getDirectoriesList($this->getImportPath() . $cmsType . 's/', null);
    }
}