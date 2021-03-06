CRUDlex User
============

CRUDlexUser is a library offering an user provider for symfony/security.

[![Total Downloads](https://poser.pugx.org/philiplb/crudlexuser/downloads.svg)](https://packagist.org/packages/philiplb/crudlexuser)
[![Latest Stable Version](https://poser.pugx.org/philiplb/crudlexuser/v/stable.svg)](https://packagist.org/packages/philiplb/crudlexuser)
[![Latest Unstable Version](https://poser.pugx.org/philiplb/crudlexuser/v/unstable.svg)](https://packagist.org/packages/philiplb/crudlexuser) [![License](https://poser.pugx.org/philiplb/crudlexuser/license.svg)](https://packagist.org/packages/philiplb/crudlexuser)

## API Documentation

The CRUDlexUser API itself is documented here:

* [0.14.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.14.0/) (upcoming)
* [0.13.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.13.0/)
* [0.12.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.12.0/)
* [0.11.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.11.0/)
* [0.10.0](http://philiplb.github.io/CRUDlexUser/docs/api/0.10.0/)
* [0.9.10](http://philiplb.github.io/CRUDlexUser/docs/api/0.9.10/)
* [0.9.9](http://philiplb.github.io/CRUDlexUser/docs/api/0.9.9/)
* [0.9.8](http://philiplb.github.io/CRUDlexUser/docs/api/0.9.8/)

Upcoming bleeding edge:

* [Upcoming](http://philiplb.github.io/CRUDlexUser/docs/api/upcoming/)

Generated via (assuming APIGen is globally installed like `composer global require ...` and the PHP version is at maximum 7.1):

```bash
~/.composer/vendor/bin/apigen generate
```


## Package

CRUDlexUser uses [SemVer](http://semver.org/) for versioning. Currently, the API changes quickly due to be < 1.0.0, so take
care about notes in the changelog when upgrading.

### Stable

```json
"require": {
    "philiplb/crudlexuser": "0.14.0"
}
```

### Bleeding Edge

```json
"require": {
    "philiplb/crudlexuser": "0.15.x-dev"
}
```

## Usage

This library offers two parts. First, a management interface for your admin panel to
perform CRUD operations on your userbase and second, an symfony/security UserProvider
offering in order to connect the users with the application.

### The Admin Panel

The admin panel for your users is based on [CRUDlex](https://github.com/philiplb/CRUDlex).
So all you have to do is to add the needed entities to your crud.yml from the
following sub chapters.

In order to get the salt generated and the password hashed, you have to let the
library add some CRUDlex events in your initialization:

```PHP
$crudUserSetup = new CRUDlex\UserSetup();
$crudUserSetup->addEvents($app['crud']->getData('user'));
```

#### Users

```yml
user:
    label: User
    table: user
    fields:
        username:
            type: text
            label: Username
            required: true
            unique: true
        password:
            type: text
            label: Password Hash
            description: 'Set this to your desired password. Will be automatically converted to an hash value not meant to be readable.'
            required: true
        salt:
            type: text
            label: Password Salt
            description: 'Auto populated field on user creation. Used internally.'
            required: false
        userRoles:
            type: many
            label: Roles
            many:
                entity: role
                nameField: role
                thisField: user
                thatField: role
```

Plus any more fields you need.
Recommended for the password reset features:

```yml
email:
    type: text
    label: E-Mail
    required: true
    unique: true
```

#### Roles

```yml
role:
    label: Roles
    table: role
    fields:
        role:
            type: text
            label: Role
            required: true
```

#### Password Reset

In case you want to use the password reset features:

```yml
passwordReset:
    label: Password Resets
    table: password_reset
    fields:
        user:
            type: reference
            label: User
            reference:
                nameField: username
                entity: user
            required: true
        token:
            type: text
            label: Token
            required: true
        reset:
            type: datetime
            label: Reset
```

### The UserProvider

Simply instantiate and add it to your symfony/security configuration:

```PHP
$userProvider = new CRUDlex\UserProvider($app['crud']->getData('user'), $app['crud']->getData('userRole'));
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            //...
            'users' => $userProvider
        ),
    ),
));
```

### Accessing Data of the Logged in User

In order to get the user data from the logged in user in your controller, you
might grab him like this:

```PHP
$user = $app['security.token_storage']->getToken()
```

You get back a CRUDlex\\User instance having some getters, see the API docs.

## Build Status

[![Build Status](https://travis-ci.org/philiplb/CRUDlexUser.svg?branch=master)](https://travis-ci.org/philiplb/CRUDlexUser)
[![Coverage Status](https://coveralls.io/repos/philiplb/CRUDlexUser/badge.png?branch=master)](https://coveralls.io/r/philiplb/CRUDlexUser?branch=master)

## Code Quality

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dd63ce7f-349f-42dd-8e71-076950b726e5/mini.png)](https://insight.sensiolabs.com/projects/dd63ce7f-349f-42dd-8e71-076950b726e5)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/philiplb/CRUDlexUser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/philiplb/CRUDlexUser/?branch=master)
