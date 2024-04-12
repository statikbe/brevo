# statikbe/craft-brevo

Brevo integration for Craft CMS. Subscribe to a mailing list through a form or directly by calling our BrevoService function.

## Requirements

- Craft CMS 4.0.0 or later.
- a <a href="https://www.brevo.com/features/email-api/" target="_blank" rel="noopener">Brevo API key</a>

> **Note**
> Using the plugin is only possible with a Brevo API key, which will require your payment details.
> The plugin maintainers are not responsible for any possible change in pricing model Brevo would make.


## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require statikbe/craft-brevo

# tell Craft to install the plugin
./craft plugin/install brevo
```

2. In the Control Panel, go to **Settings** -> **Plugins** -> Brevo plugin **Settings**

    Here you can add your API key, list id and template id. Or use a config file.

## Configuration

In the settings you can work with environment variables. 
If you do wish to override them with a config file. Create a `brevo.php` file in the `config folder of your project and add the following credentials:

```php
<?php

return [
    'apiKey' => 'your-api-key',
    'listId' => 'your-list-id',
    'templateId' => 'your-template-id',
];
```

## Usage
### Basic Form
```twig
<form method="post">
    {{ csrfInput() }}
    {{ actionInput('brevo/api/subscribe') }}
    {{ redirectInput('your-redirect-after-submit') }}
    
    // this url is the one you are redirected to after having confirmed your subscription in your mail
    {{ hiddenInput('url', 'your-redirect-after-opt-in') }}
    
    {% set subscribeResponse = newsletterSubscribe is defined ? newsletterSubscribe : null %}
    {% if subscribeResponse %}
        {% if not subscribeResponse.success %}
            <p>Whoops, something went wrong! Please try again.</p>
        {% endif %}
    {% endif %}

    <div >
        <label for="emailInput">Emailaddress</label>
        <input id="emailInput" name="email" type="email" required {% if subscribeResponse != null and subscribeResponse.mail is defined and subscribeResponse.mail|length %}value="{{ subscribeResponse.mail }}"{% endif %} />
    </div>
    <div >
        <input id="terms" name="terms" type="checkbox" required />
        <label for="terms">I have read and agreed with the privacy policy</label>
    </div>
    <button type="submit">Subscribe</button>
</form>
```

### Extended Form with Brevo custom fields
```twig
<form method="post">
    {{ csrfInput() }}
    {{ actionInput('brevo/api/subscribe') }}
    {{ redirectInput('your-redirect-after-submit') }}
    
    // this url is the one you are redirected to after having confirmed your subscription in your mail
    {{ hiddenInput('url', 'your-redirect-after-opt-in') }}
    
    //only add the list id hidden input if you have multiple list you want to subscribe a contact to depending on the form they fill in.
    {{ hiddenInput('attributes[listId]', getenv('BREVO_LIST_ID')) }}

    {% set subscribeResponse = newsletterSubscribe is defined ? newsletterSubscribe : null %}
    {% if subscribeResponse %}
        {% if not subscribeResponse.success %}
            <p>Whoops, something went wrong! Please try again.</p>
        {% endif %}
    {% endif %}

    <div >
        <label for="emailInput">Email</label>
        <input name="email" type="email" required {% if subscribeResponse != null and subscribeResponse.mail is defined and subscribeResponse.mail|length %}value="{{ subscribeResponse.mail }}"{% endif %}/>
    </div>
   
   // If you want to send extra fields to your subscription service, you need to add the 'attributes' tag in the name.
    <div>
        <label for="zipCode">Zipcode</label>
        <input name="attributes[zipCode]" type="text" {% if subscribeResponse != null and subscribeResponse.zipCode is defined and subscribeResponse.zipCode|length %}value="{{ subscribeResponse.zipCode }}"{% endif %}/>
    </div>
    <div >
        <input name="terms" type="checkbox" required/>
        <label for="terms">I have read and agreed with the privacy policy</label>
    </div>
    <button type="submit">Subscribe</button>
</form>
```

### Directly using the Brevo Service
Do you want customise how you subscribe a contact to your mailing list, you can also call the add function in the SubscribeService. 

Just keep in mind you always add a mailaddress, a list id and an url to which the double opt-in may redirect.

```php
SubscribeService::instance()->subscribe($email, $listId, $redirectUrl);
```
---
Brought to you by [Statik.be](https://www.statik.be)