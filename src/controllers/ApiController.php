<?php

namespace statikbe\brevo\controllers;

use Craft;
use craft\web\Controller;
use statikbe\brevo\services\SubscribeService;
use yii\web\BadRequestHttpException;

class ApiController extends Controller
{
    /**
     * @throws BadRequestHttpException
     */

    protected array|int|bool $allowAnonymous = ['subscribe'];

    /**
     * @throws BadRequestHttpException
     */
    public function actionSubscribe(): ?\yii\web\Response
    {
        $this->requirePostRequest();

        $req = $this->request;
        $email = $req->post('email');
        $terms = $req->post('terms');

//        get attributes variable with all possible extra fields
        $attributes = $req->post('attributes') ?? [];
        $redirectUrl = $req->getBodyParam('url');

        if($terms) {
            $subscribe = SubscribeService::instance()->add($email, $attributes, $redirectUrl);

            if (!$subscribe) {
                $data = [
                    'mail' => $email,
                    'attributes' => $attributes,
                    'terms' => $terms,
                    'success' => false,
                ];

                Craft::$app->getUrlManager()->setRouteParams([
                    'variables' => ['newsletterSubscribe' => $data]
                ]);

                return null;
            }

            return $this->redirectToPostedUrl();
        }
        return null;
    }
}