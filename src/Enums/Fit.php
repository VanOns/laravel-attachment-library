<?php

namespace VanOns\LaravelAttachmentLibrary\Enums;

enum Fit: string
{
    case CONTAIN = 'contain';
    case MAX = 'max';
    case FILL = 'fill';
    case FILLMAX = 'fill-max';
    case STRETCH = 'stretch';
    case CROP = 'crop';
}
