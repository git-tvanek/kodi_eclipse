<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class AddonTag
{
    use SmartObject;

    public int $addon_id;
    public int $tag_id;

    /**
     * Create an AddonTag instance from array data
     */
    public static function fromArray(array $data): self
    {
        $addonTag = new self();
        
        $addonTag->addon_id = (int) $data['addon_id'];
        $addonTag->tag_id = (int) $data['tag_id'];
        
        return $addonTag;
    }

    /**
     * Convert the AddonTag instance to an array
     */
    public function toArray(): array
    {
        return [
            'addon_id' => $this->addon_id,
            'tag_id' => $this->tag_id,
        ];
    }
}