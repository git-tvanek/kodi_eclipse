<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette\SmartObject;

class AddonReview
{
    use SmartObject;

    public int $id;
    public int $addon_id;
    public ?int $user_id;
    public ?string $name;
    public ?string $email;
    public int $rating;
    public ?string $comment;
    public DateTime $created_at;

    /**
     * Create an AddonReview instance from array data
     */
    public static function fromArray(array $data): self
    {
        $review = new self();
        
        if (isset($data['id'])) {
            $review->id = (int) $data['id'];
        }
        
        $review->addon_id = (int) $data['addon_id'];
        $review->user_id = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $review->name = $data['name'] ?? null;
        $review->email = $data['email'] ?? null;
        $review->rating = (int) $data['rating'];
        $review->comment = $data['comment'] ?? null;
        
        if (isset($data['created_at'])) {
            $review->created_at = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
        } else {
            $review->created_at = new DateTime();
        }
        
        return $review;
    }

    /**
     * Convert the AddonReview instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'addon_id' => $this->addon_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
        ];
    }
}