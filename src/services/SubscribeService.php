<?php

namespace statikbe\brevo\services;

use Brevo\Client\ApiException;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\CreateDoiContact;
use Brevo\Client\Model\UpdateContact;
use craft\base\Component;
use Brevo\Client\Api\ContactsApi;
use GuzzleHttp\Client;
use statikbe\brevo\Brevo;
use yii\helpers\VarDumper;


class SubscribeService extends Component
{
    private $_errorMessage;

    public function add(string $email, array $attributes, $redirectUrl): bool
    {
        $clientContactApi = $this->getClientContactApi();
//        GET settings list id
        $listId = $attributes['listId'] ?? Brevo::getInstance()->getSettings()->listId;

        if (!$this->_contactExist($email, $clientContactApi)) {
            if ($listId !== 0) {
                return $this->_registerContactToList($email, $attributes, $listId, $clientContactApi, $redirectUrl);
            }

            return $this->_registerContact($email, $attributes, $clientContactApi);
        }

        if ($listId !== 0) {
            return $this->_addContactToList($email, $attributes, $listId, $clientContactApi);
        }

        return true;
    }

    public function getClientContactApi(): ContactsApi|bool
    {
        $apiKey = Brevo::getInstance()->getSettings()->apiKey;
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);

        return new ContactsApi(
            new Client(),
            $config,
        );
    }

    private function _contactExist(string $email, ContactsApi $client): bool
    {
        try {
            $client->getContactInfo($email);
            return true;
        } catch (ApiException $apiException) {
            $this->_errorMessage = $this->_getErrorMessage($apiException);
            return false;
        }
    }

    private function _registerContactToList(string $email, array $attributes, int $listId, ContactsApi $clientContactApi, $redirectUrl): bool
    {

        $templateId = Brevo::getInstance()->getSettings()->templateId;
        try {
            $contact = new CreateDoiContact([
                'email' => $email,
                'attributes' => $attributes,
                'includeListIds' => [$listId],
                'templateId' => $templateId,
                'redirectionUrl' => $redirectUrl
            ]);
            $clientContactApi->createDoiContact($contact);
            return true;
        } catch (ApiException $apiException) {
            $this->_errorMessage = $this->_getErrorMessage($apiException);
            return false;
        }
    }

    private function _registerContact(string $email, array $attributes, ContactsApi $clientContactApi): bool
    {
        try {
            $contact = new CreateContact(['email' => $email, 'attributes' => $attributes]);
            $clientContactApi->createContact($contact);
            return true;
        } catch (ApiException $apiException) {
            $this->_errorMessage = $this->_getErrorMessage($apiException);
            return false;
        }
    }

    private function _addContactToList(string $email, array $attributes, int $listId, ContactsApi $clientContactApi): bool
    {
        try {
            $contact = new UpdateContact(['listIds' => [$listId], 'attributes' => $attributes]);
            $clientContactApi->updateContact($email, $contact);
            return true;
        } catch (ApiException $apiException) {
            $this->_errorMessage = $this->_getErrorMessage($apiException);
            return false;
        }
    }

    private function _getErrorMessage(ApiException $apiException): string
    {
        $errorLogMessages = [
            400 => 'Brevo request is invalid. Check the error code in JSON (400).',
            401 => 'Brevo authentication error (401). Make sure the provided api-key is correct.',
            403 => 'Brevo resource access error (403).',
            404 => 'Brevo resource was not found (404).',
            405 => 'Brevo verb is not allowed for this endpoint (405).',
            406 => 'Brevo empty or invalid json value (406).',
            429 => 'Brevo rate limit is exceeded. (429).',
            500 => 'Brevo internal server error (500).',
        ];
        $errorMessage = \Craft::t('app', 'The newsletter service is not available at that time. Please, try again later.');
        if (array_key_exists($apiException->getCode(), $errorLogMessages)) {
            \Craft::error($errorLogMessages[$apiException->getCode()] . " " . VarDumper::dumpAsString($apiException), __METHOD__);
        } else {
            \Craft::error("Brevo unknown error ({$apiException->getCode()}). " . VarDumper::dumpAsString($apiException->getResponseBody()), __METHOD__);
        }
        return $errorMessage;
    }
}