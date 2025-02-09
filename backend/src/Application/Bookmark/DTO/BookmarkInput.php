<?php

namespace App\Application\Bookmark\DTO;

use App\Domain\Bookmark\Validator\Constraints\AllowedDomain;
use Symfony\Component\Validator\Constraints as Assert;

class BookmarkInput
{
    #[Assert\NotBlank(message: 'The URL must not be blank.')]
    #[Assert\Url(message: "The URL '{{ value }}' is not a valid URL.")]
    #[AllowedDomain]
    public ?string $url = null;
}
