<?php

namespace skh6075\ServerTransfer\lang;

use pocketmine\utils\SingletonTrait;

final class PluginLang{
    use SingletonTrait;

    private string $lang;

    private array $translates = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function setProperties(string $lang, array $translates = []): self{
        $this->lang = $lang;
        $this->translates = $translates;
        return $this;
    }

    public function format(string $key, array $replaces = [], bool $pushPrefix = true): string{
        $format = $pushPrefix ? $this->translates["prefix"] ?? "" : "";
        $format .= $this->translates[$key] ?? "";
        foreach ($replaces as $old => $new) {
            $format = str_replace($old, $new, $format);
        }

        return $format;
    }
}
