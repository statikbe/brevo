<?php

namespace statikbe\brevo\models;

use Craft;
use craft\base\Model;

/**
 * statikbe/craft-brevo settings
 */
class Settings extends Model
{
    public string $apiKey;
    public int $listId;
    public int $templateId;
}
