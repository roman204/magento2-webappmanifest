<?php

namespace Ampersand\WebAppManifest\Model;

use Ampersand\WebAppManifest\Api\Data\ManifestInterface;
use Ampersand\WebAppManifest\ValueObject\ManifestContents;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Manifest implements ManifestInterface
{
    const XML_PATH_STORE_INFO_SHORT_NAME = 'web/webappmanifest/short_store_name';
    const XML_PATH_STORE_INFO_NAME = 'web/webappmanifest/store_name';
    const XML_PATH_STORE_INFO_DESCRIPTION = 'web/webappmanifest/description';
    const XML_PATH_STORE_INFO_START_URL = 'web/webappmanifest/start_url';
    const XML_PATH_DISPLAY_THEME_COLOR = 'web/webappmanifest/theme_color';
    const XML_PATH_DISPLAY_BACKGROUND_COLOR = 'web/webappmanifest/background_color';
    const XML_PATH_DISPLAY_DISPLAY_TYPE = 'web/webappmanifest/display_type';
    const XML_PATH_DISPLAY_ORIENTATION = 'web/webappmanifest/orientation';
    const XML_PATH_ICONS_ICON = 'web/webappmanifest/icon';
    const XML_PATH_ICONS_SIZES = 'web/webappmanifest/icon_sizes';
    const XML_PATH_SCOPE = 'web/webappmanifest/scope';
    const ICON_STORAGE_PATH = "webappmanifest/icons/";

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var UrlInterface */
    private $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $manifestData = ManifestContents::fromConfigData(
            $this->populateFromConfig(self::XML_PATH_STORE_INFO_SHORT_NAME),
            $this->populateFromConfig(self::XML_PATH_STORE_INFO_NAME),
            $this->populateFromConfig(self::XML_PATH_STORE_INFO_DESCRIPTION),
            $this->populateStartUrl(),
            $this->populateFromConfig(self::XML_PATH_DISPLAY_THEME_COLOR),
            $this->populateFromConfig(self::XML_PATH_DISPLAY_BACKGROUND_COLOR),
            $this->populateFromConfig(self::XML_PATH_DISPLAY_DISPLAY_TYPE),
            $this->populateFromConfig(self::XML_PATH_DISPLAY_ORIENTATION),
            $this->populateIcons(),
            $this->populateFromConfig(self::XML_PATH_SCOPE)
        );

        return $manifestData->toArray();
    }

    /**
     * @return array
     */
    protected function populateIcons(): array
    {
        if ($icon = $this->populateFromConfig(self::XML_PATH_ICONS_ICON)) {
            $url = implode('', [
                $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]),
                'webappmanifest/icons/',
                $icon,
            ]);
            $imageSizes = [];

            $sizes = explode(" ", $this->populateFromConfig(self::XML_PATH_ICONS_SIZES));
            foreach ($sizes as $size) {
                $imageSrcResized = $this->createIcon($icon, $size);
                $imageSizes[] = [
                    "src"   => $imageSrcResized["url"],
                    "sizes" => $size,
                    "type"  => $imageSrcResized["type"]
                ];
            }

            return [$imageSizes];
        }

        return [];
    }

    private function createIcon($image, $imageDimensions)
    {
        //parse width and height of 72x72
        preg_match_all('/(.*)x(.*)/m', $imageDimensions, $matches, PREG_SET_ORDER, 0);
        $width = $matches[0][1];
        $height = $matches[0][2];

        $absolutePath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                            ->getAbsolutePath(self::ICON_STORAGE_PATH) . $image;
        if (!file_exists($absolutePath)) {
            return false;
        }
        $imageResized = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                            ->getAbsolutePath(self::ICON_STORAGE_PATH . 'resized/' . $imageDimensions . '/') . $image;

        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize($width, $height);
            //destination folder
            $destination = $imageResized;
            //save image
            $imageResize->save($destination);
        }

        $mime = getimagesize($imageResized)["mime"];
        $resizedURL = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                      self::ICON_STORAGE_PATH . 'resized/' . $imageDimensions . '/' . $image;
        return [
            "url"  => $resizedURL,
            "type" => $mime,
        ];
    }

    /**
     * @return string
     */
    private function populateStartUrl(): string
    {
        if ($path = $this->populateFromConfig(self::XML_PATH_STORE_INFO_START_URL)) {
            return $this->urlBuilder->getDirectUrl($path);
        }

        return $this->urlBuilder->getBaseUrl();
    }

    /**
     * @param string $config_path
     * @return mixed
     */
    private function populateFromConfig(string $config_path)
    {
        return $this->scopeConfig->getValue($config_path, ScopeInterface::SCOPE_STORE);
    }
}
