<?php

namespace LucaCermenati\NovaGlobalFilters\Cards;

use Illuminate\Support\Str;
use Laravel\Nova\Card;

/**
 * A full-width title row to group the cards below it.
 *
 * @method static static make(string $title, string|null $subtitle = null)
 */
class SectionHeader extends Card
{
    public $width = 'full';

    public $height = 'dynamic';

    public function __construct(protected string $title, protected ?string $subtitle = null)
    {
        parent::__construct();
    }

    public function component(): string
    {
        return 'nova-section-header';
    }

    public function subtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function uriKey(): string
    {
        return 'section-header-'.Str::slug($this->title);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'uriKey' => $this->uriKey(),
            'title' => $this->title,
            'subtitle' => $this->subtitle,
        ]);
    }
}
