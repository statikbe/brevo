<?php

namespace statikbe\brevo;

use Craft;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use statikbe\brevo\models\Settings;
use statikbe\brevo\services\SubscribeService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * statikbe/craft-brevo plugin
 *
 * @method static Brevo getInstance()
 * @method Settings getSettings()
 * @author Statik
 * @copyright Statik
 * @license MIT
 */
class Brevo extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSection = false;
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'subscribeService' => SubscribeService::class,
        ]);
    }

    /**
     * @throws InvalidConfigException
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws Exception
     * @throws LoaderError
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('brevo/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
}
