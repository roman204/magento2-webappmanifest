<?php

namespace Ampersand\WebAppManifest\ValueObject;

class ManifestContents
{
    /** @var string */
    private $shortName;
    /** @var string */
    private $name;
    /** @var string */
    private $description;
    /** @var string */
    private $startUrl;
    /** @var string */
    private $themeColor;
    /** @var string */
    private $backgroundColor;
    /** @var string */
    private $display;
    /** @var string */
    private $orientation;
    /** @var array */
    private $icons;
    /** @var string */
    private $scope;

    /**
     * @param string|null $shortName
     * @param string|null $name
     * @param string|null $description
     * @param string|null $startUrl
     * @param string|null $themeColor
     * @param string|null $backgroundColor
     * @param string|null $display
     * @param string|null $orientation
     * @param array|null $icons
     * @param string|null $scope
     */
    public function __construct(
        ?string $shortName = null,
        ?string $name = null,
        ?string $description = null,
        ?string $startUrl = null,
        ?string $themeColor = null,
        ?string $backgroundColor = null,
        ?string $display = null,
        ?string $orientation = null,
        ?array $icons = [],
        ?string $scope = null
    ) {
        $this->shortName = $shortName;
        $this->name = $name;
        $this->description = $description;
        $this->startUrl = $startUrl;
        $this->themeColor = $themeColor;
        $this->backgroundColor = $backgroundColor;
        $this->display = $display;
        $this->orientation = $orientation;
        $this->icons = $icons;
        $this->scope = $scope;
    }

    /**
     * @param string|null $shortName
     * @param string|null $name
     * @param string|null $description
     * @param string|null $startUrl
     * @param string|null $themeColor
     * @param string|null $backgroundColor
     * @param string|null $display
     * @param string|null $orientation
     * @param array|null $icons
     * @param string|null $scope
     * @return ManifestContents
     */
    public static function fromConfigData(
        ?string $shortName = null,
        ?string $name = null,
        ?string $description = null,
        ?string $startUrl = null,
        ?string $themeColor = null,
        ?string $backgroundColor = null,
        ?string $display = null,
        ?string $orientation = null,
        ?array $icons = [],
        ?string $scope = null
    ): self {
        return new self(
            $shortName,
            $name,
            $description,
            $startUrl,
            $themeColor,
            $backgroundColor,
            $display,
            $orientation,
            $icons,
            $scope
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $beforeFilter = [
            'short_name' => $this->shortName,
            'name' => $this->name,
            'description' => $this->description,
            'start_url' => $this->startUrl,
            'theme_color' => $this->themeColor,
            'background_color' => $this->backgroundColor,
            'display' => $this->display,
            'orientation' => $this->orientation,
            'icons' => $this->icons,
            'scope' => $this->scope,
        ];

        return array_filter($beforeFilter);
    }
}
