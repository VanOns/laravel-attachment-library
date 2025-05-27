<?php

namespace VanOns\LaravelAttachmentLibrary\Enums;

enum Fit: string
{
    case CONTAIN = 'contain';
    case CROP = 'crop';
    case FILL = 'fill';
    case FILL_MAX = 'fill-max';
    case MAX = 'max';
    case STRETCH = 'stretch';
}
