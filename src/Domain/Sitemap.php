<?php

namespace App\Domain;

class Sitemap implements \Stringable
{
    private function __construct(
        private string $content
    ) {
    }

    public function updateLastMod(\DateTimeImmutable $modificationDate): self
    {
        $this->content = preg_replace(
            '/<lastmod>[\s\S]+<\/lastmod>/',
            sprintf('<lastmod>%s</lastmod>', $modificationDate->format('Y-m-d\TH:i:s')),
            $this->content
        );

        return $this;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public static function fromPath(string $path): self
    {
        return new self(\Safe\file_get_contents($path));
    }
}
